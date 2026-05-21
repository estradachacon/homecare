<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\PagosDetailsModel;
use App\Models\TipoVentaModel;
use App\Models\FacturaHeadModel;
use App\Models\PagosHeadModel;
use App\Models\AccountModel;

class PaymentController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_pagos');
        if ($chk !== true) return $chk;

        $model = new PagosHeadModel();

        $model->select('
        pagos_head.*,
        clientes.nombre AS cliente_nombre,

        COALESCE(SUM(CASE WHEN pagos_details.anulado = 0 THEN pagos_details.monto ELSE 0 END),0) AS total_aplicado,
        COALESCE(SUM(CASE WHEN pagos_details.anulado = 1 THEN pagos_details.monto ELSE 0 END),0) AS total_anulado,
        COALESCE(SUM(CASE WHEN pagos_details.anulado = 0 THEN pagos_details.retencion_monto ELSE 0 END),0) AS total_retencion
    ')
            ->join('clientes', 'clientes.id = pagos_head.cliente_id', 'left')
            ->join('pagos_details', 'pagos_details.pago_id = pagos_head.id', 'left')
            ->join('facturas_head', 'facturas_head.id = pagos_details.factura_id', 'left')
            ->groupBy('pagos_head.id');

        // ===== FILTROS =====

        $clienteId = $this->request->getGet('cliente_id');
        $estado    = $this->request->getGet('estado');
        $fecha     = $this->request->getGet('fecha');
        $tipoAplicacion = $this->request->getGet('tipo_aplicacion');
        $factura = trim($this->request->getGet('factura'));

        if (!empty($factura)) {

            $model->groupStart()

                ->like('facturas_head.numero_control', $factura)

                ->orLike('facturas_head.id', $factura)

                ->groupEnd();
        }

        if (is_numeric($clienteId)) {
            $model->where('pagos_head.cliente_id', $clienteId);
        }

        if ($estado === 'activa') {
            $model->where('pagos_head.anulado', 0);
        }

        if ($estado === 'anulada') {
            $model->where('pagos_head.anulado', 1);
        }

        if (!empty($fecha)) {
            $model->where('pagos_head.fecha_pago', $fecha);
        }

        if ($tipoAplicacion === 'con_anulaciones') {
            $model->having('total_anulado >', 0);
        }

        if ($tipoAplicacion === 'sin_efecto') {
            $model->having('total_aplicado', 0);
        }

        if ($tipoAplicacion === 'normal') {
            $model->having('total_anulado', 0);
        }



        $model->orderBy('pagos_head.fecha_pago', 'DESC');

        $pagos = $model->paginate(10);
        $pager = $model->pager;

        if ($this->request->isAJAX()) {

            $tbody = view('pagos/tbody_row', compact('pagos'));
            $pagerHtml = $pager->links('default', 'bootstrap_full');

            return $this->response->setJSON([
                'tbody' => $tbody,
                'pager' => $pagerHtml
            ]);
        }

        return view('pagos/index', compact('pagos', 'pager'));
    }
    public function new()
    {
        $chk = requerirPermiso('crear_pagos');
        if ($chk !== true) return $chk;

        // Traer clientes (si luego quieres Select2 remoto, esto puede quitarse)
        $clienteModel = new \App\Models\ClienteModel();
        $clientes = $clienteModel->orderBy('nombre')->findAll();

        // Traer vendedores
        $sellerModel = new \App\Models\SellerModel();
        $sellers = $sellerModel->orderBy('seller')->findAll();

        return view('pagos/new', [
            'clientes' => $clientes,
            'sellers'  => $sellers
        ]);
    }
    public function show($id)
    {
        $pagoModel = new PagosHeadModel();
        $detalleModel = new PagosDetailsModel();

        $pago = $pagoModel
            ->select('
            pagos_head.*,
            clientes.nombre AS cliente_nombre,
            accounts.name AS cuenta_nombre
        ')
            ->join('clientes', 'clientes.id = pagos_head.cliente_id', 'left')
            ->join('accounts', 'accounts.id = pagos_head.numero_cuenta_bancaria', 'left')
            ->where('pagos_head.id', $id)
            ->first();

        if (!$pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $facturas = $detalleModel
            ->select('pagos_details.*, facturas_head.numero_control, cpc.codigo AS ret_cuenta_codigo, cpc.nombre AS ret_cuenta_nombre')
            ->join('facturas_head', 'facturas_head.id = pagos_details.factura_id')
            ->join('cont_plan_cuentas cpc', 'cpc.id = pagos_details.retencion_cuenta_id', 'left')
            ->where('pagos_details.pago_id', $id)
            ->findAll();

        // ==============================
        // 🔎 Detectar anulaciones parciales
        // ==============================

        $totalDetalles   = count($facturas);
        $totalAnulados   = 0;
        $totalActivo     = 0;
        $totalRetencion  = 0;

        foreach ($facturas as $f) {
            if ($f->anulado) {
                $totalAnulados++;
            } else {
                $totalActivo    += $f->monto;
                $totalRetencion += (float)($f->retencion_monto ?? 0);
            }
        }

        $anulacionParcial = (
            $totalDetalles > 0 &&
            $totalAnulados > 0 &&
            $totalAnulados < $totalDetalles
        );

        return view('pagos/show', compact(
            'pago',
            'facturas',
            'anulacionParcial',
            'totalActivo',
            'totalRetencion'
        ));
    }
    public function facturas($pagoId)
    {
        $model = new PagosDetailsModel();

        $facturas = $model
            ->select('facturas_head.numero_control, pagos_details.monto')
            ->join('facturas_head', 'facturas_head.id = pagos_details.factura_id')
            ->where('pagos_details.pago_id', $pagoId)
            ->findAll();

        return $this->response->setJSON($facturas);
    }

    public function facturasPendientes($clienteId)
    {
        $model = new FacturaHeadModel();

        $facturas = $model
            ->select('facturas_head.*, sellers.seller AS vendedor, tipo_venta.nombre_tipo_venta AS tipo_venta_nombre')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta', 'tipo_venta.id = facturas_head.tipo_venta', 'left')
            ->where('facturas_head.receptor_id', $clienteId)
            ->where('facturas_head.saldo >', 0);

        if (!puedeVerDocumentosTodosVendedores()) {
            $facturas->where('facturas_head.vendedor_id', vendedorUsuarioActual() ?? -1);
        }

        $facturas = $facturas
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->orderBy('numero_control', 'ASC')
            ->findAll();

        return $this->response->setJSON($facturas);
    }
    public function store()
    {
        $db = \Config\Database::connect();
        $db->transBegin();
        $session = session();

        try {

            $data = $this->request->getJSON(true);
            if (empty($data['facturas'])) {
                throw new \Exception('No hay facturas');
            }

            $pagosHead = new PagosHeadModel();
            $pagosDet  = new PagosDetailsModel();
            $facturas  = new FacturaHeadModel();

            // ================= CALCULAR NETO (descontar retenciones) =================

            $totalBruto     = 0;
            $totalRetencion = 0;
            foreach ($data['facturas'] as $f) {
                $totalBruto     += (float)$f['monto'];
                $totalRetencion += (float)($f['retencion_monto'] ?? 0);
            }
            $totalNeto = round($totalBruto - $totalRetencion, 2);

            // ================= HEAD =================

            $pagosHead->insert([
                'cliente_id'             => $data['cliente_id'],
                'fecha_pago'             => $data['fecha_pago'],
                'forma_pago'             => $data['tipo_pago'],
                'numero_recupero'        => $data['recupero'],
                'numero_cuenta_bancaria' => $data['cuenta_bancaria'],
                'total'                  => $totalNeto,
                'observaciones'          => $data['observaciones'],
                'anulado'                => 0,
            ], true);

            $pagoId = $pagosHead->getInsertID();
            // ================= DETAILS + FACTURAS =================

            foreach ($data['facturas'] as $f) {

                $retMonto   = (float)($f['retencion_monto']    ?? 0);
                $retCuenta  = !empty($f['retencion_cuenta_id']) ? (int)$f['retencion_cuenta_id'] : null;
                $retAplicada = $retMonto > 0 ? 1 : 0;

                if ($retAplicada && !$retCuenta) {
                    throw new \Exception('Seleccione una cuenta contable para la retención de la factura ' . $f['factura_id']);
                }

                // detalle
                $pagosDet->insert([
                    'pago_id'             => $pagoId,
                    'factura_id'          => $f['factura_id'],
                    'monto'               => $f['monto'],
                    'observaciones'       => $f['comentario'] ?? null,
                    'retencion_aplicada'  => $retAplicada,
                    'retencion_monto'     => $retMonto,
                    'retencion_cuenta_id' => $retCuenta,
                ]);

                // obtener saldo actual
                $factura = $facturas->find($f['factura_id']);

                if (!$factura) {
                    throw new \Exception('Factura no encontrada');
                }
                if (!puedeVerDocumentosTodosVendedores()) {
                    $sellerScope = vendedorUsuarioActual();
                    if (!$sellerScope || (int)$factura->vendedor_id !== (int)$sellerScope) {
                        throw new \Exception('No puedes aplicar pagos a facturas de otro vendedor');
                    }
                }

                $nuevoSaldo = $factura->saldo - $f['monto'];

                if ($nuevoSaldo < 0) {
                    throw new \Exception('Monto mayor al saldo');
                }

                $facturas->update($f['factura_id'], [
                    'saldo' => $nuevoSaldo
                ]);
            }

            // ================= CUENTAS =================

            helper('cuentas');

            $accountId = null;

            // Si es recupero → efectivo (ID 1)
            if ($data['tipo_pago'] === 'recupero') {
                $accountId = 1;
            }

            // Si es transferencia → cuenta seleccionada
            if ($data['tipo_pago'] === 'transferencia') {
                $accountId = $data['cuenta_bancaria'];
            }

            if ($accountId) {

                registrarEntrada(
                    $accountId,
                    $totalNeto,
                    'Pago de facturas',
                    'Pago ID ' . $pagoId,
                    $pagoId
                );

                // ACTUALIZAR BALANCE CACHE (opcional pero recomendado)
                $accountModel = new AccountModel();
                $nuevoBalance = $accountModel->getBalance($accountId);

                $accountModel->update($accountId, [
                    'balance' => $nuevoBalance
                ]);
            }

            // ================= VINCULAR RECUPERO (opcional) =================

            if (!empty($data['recupero_id'])) {
                $recModel = new \App\Models\RecuperosModel();
                $rec = $recModel->where('id', (int)$data['recupero_id'])
                                ->where('cliente_id', (int)$data['cliente_id'])
                                ->where('estado', 'ACTIVO')
                                ->first();
                if (!$rec) {
                    throw new \Exception('El recupero seleccionado no está disponible o no pertenece a este cliente');
                }
                $recModel->update($rec->id, [
                    'estado'  => 'APLICADO',
                    'pago_id' => $pagoId,
                ]);
            }

            $db->transCommit();

            registrar_bitacora(
                'Pago de facturas ID ' . esc($pagoId),
                'Pagos',
                'Se pagó un total de $' . number_format($data['total'], 2) . ' al cliente con ID ' . esc($data['cliente_id']) . '.' . 'Forma de pago: ' . esc($data['tipo_pago']),
                $session->get('user_id')
            );

            // ======== ASIENTOS CONTABLES (uno por factura, independiente del pago) ========
            $asientosCreados = [];
            $asientoError    = null;
            try {
                $asientosCreados = $this->_crearAsientosPago(
                    (int)$data['cliente_id'],
                    $data['facturas'],
                    $data['fecha_pago'],
                    $pagoId
                );
            } catch (\Throwable $e) {
                $asientoError = $e->getMessage();
            }

            return $this->response->setJSON([
                'status'        => 'ok',
                'pago_id'       => $pagoId,
                'asientos'      => $asientosCreados,
                'asiento_error' => $asientoError,
            ]);

        } catch (\Throwable $e) {

            $db->transRollback();

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }

    private function _crearAsientosPago(int $clienteId, array $facturas, string $fecha, int $pagoId): array
    {
        $planModel     = new \App\Models\ContPlanCuentasModel();
        $clienteModel  = new \App\Models\ClienteModel();
        $periodosModel = new \App\Models\ContPeriodosModel();
        $headModel     = new \App\Models\ContAsientosHeadModel();
        $detModel      = new \App\Models\ContAsientosDetalleModel();
        $facturaModel  = new \App\Models\FacturaHeadModel();

        // 1. Cuenta DEBE: 11010101 CAJA GRANDE
        $cuentaCaja = $planModel->where('codigo', '11010101')->first();
        if (!$cuentaCaja) {
            throw new \Exception('No existe la cuenta 11010101 CAJA GRANDE en el plan de cuentas');
        }

        // 2. Cuenta HABER: CxC del cliente (crear subcuenta si no tiene)
        $cliente = $clienteModel->find($clienteId);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado');
        }

        $cxcId = (int)($cliente->cuenta_contable_id ?? 0) ?: null;

        if (!$cxcId) {
            $existente = $planModel
                ->like('codigo', '110201', 'after')
                ->where('nombre', mb_strtoupper($cliente->nombre))
                ->first();

            if ($existente) {
                $clienteModel->update($clienteId, ['cuenta_contable_id' => $existente->id]);
                $cxcId = (int)$existente->id;
            } else {
                $padre = $planModel->where('codigo', '110201')->first();
                if (!$padre) {
                    throw new \Exception('No existe la cuenta padre 110201 CLIENTES LOCALES');
                }

                $db        = \Config\Database::connect();
                $siguiente = (int)$db->query(
                    "SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 7) AS UNSIGNED)), 0) + 1
                     AS sig FROM cont_plan_cuentas
                     WHERE codigo LIKE '110201%' AND LENGTH(codigo) > 6"
                )->getRow()->sig;

                $nuevoCodigo = '110201' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);

                $cxcId = (int)$planModel->insert([
                    'codigo'             => $nuevoCodigo,
                    'nombre'             => mb_strtoupper($cliente->nombre),
                    'tipo'               => $padre->tipo,
                    'naturaleza'         => $padre->naturaleza,
                    'nivel'              => $padre->nivel + 1,
                    'cuenta_padre_id'    => $padre->id,
                    'acepta_movimientos' => 1,
                    'activo'             => 1,
                ]);

                $clienteModel->update($clienteId, ['cuenta_contable_id' => $cxcId]);
            }
        }

        // 3. Período contable — auto-create if it doesn't exist yet for this date
        $anioFechaP = (int)substr($fecha, 0, 4);
        $mesFechaP  = (int)substr($fecha, 5, 2);
        $periodo    = $periodosModel->abrirObtenerPeriodo($anioFechaP, $mesFechaP);
        if (!$periodo) {
            throw new \Exception("El período {$mesFechaP}/{$anioFechaP} está cerrado y no puede reabrirse automáticamente");
        }

        // Leer tipo_partida de pagos desde configuración
        $cfg           = (new \App\Models\ContConfiguracionModel())->getConfig();
        $tipoPartidaId = !empty($cfg->tipo_partida_pagos_id) ? (int)$cfg->tipo_partida_pagos_id : null;

        // 4. Un asiento consolidado por día (o uno por factura si no hay tipo_partida)
        $creados = [];
        $userId  = session()->get('id');

        foreach ($facturas as $f) {
            $monto     = (float)$f['monto'];
            $facturaId = (int)$f['factura_id'];

            $factura     = $facturaModel->find($facturaId);
            $correlativo = $factura
                ? substr($factura->numero_control, -6)
                : 'FAC-' . $facturaId;

            $descLinea = 'Pago ' . $correlativo . ' — ' . $cliente->nombre;

            $retMonto   = round((float)($f['retencion_monto'] ?? 0), 2);
            $retCuenta  = !empty($f['retencion_cuenta_id']) ? (int)$f['retencion_cuenta_id'] : null;
            $montoNeto  = round($monto - $retMonto, 2);

            if ($retMonto > 0 && $retCuenta) {
                $lineasAsiento = [
                    ['cuenta_id' => $cuentaCaja->id, 'descripcion' => $descLinea, 'debe' => $montoNeto, 'haber' => 0],
                    ['cuenta_id' => $retCuenta,      'descripcion' => 'Ret. 10% ' . $correlativo,       'debe' => $retMonto, 'haber' => 0],
                    ['cuenta_id' => $cxcId,          'descripcion' => $descLinea, 'debe' => 0,           'haber' => $monto],
                ];
            } else {
                $lineasAsiento = [
                    ['cuenta_id' => $cuentaCaja->id, 'descripcion' => $descLinea, 'debe' => $monto, 'haber' => 0],
                    ['cuenta_id' => $cxcId,          'descripcion' => $descLinea, 'debe' => 0,      'haber' => $monto],
                ];
            }

            // Consolidar: buscar partida del mismo día y tipo
            $existing = $tipoPartidaId
                ? $headModel->buscarPartidaDia($tipoPartidaId, $fecha)
                : null;

            if ($existing) {
                $dbRaw    = \Config\Database::connect();
                $maxOrden = (int)($dbRaw->query(
                    'SELECT COALESCE(MAX(orden), 0) AS m FROM cont_asientos_detalle WHERE asiento_id = ?',
                    [$existing->id]
                )->getRow()->m ?? 0);

                foreach ($lineasAsiento as $i => $linea) {
                    $detModel->insert([
                        'asiento_id'  => $existing->id,
                        'cuenta_id'   => $linea['cuenta_id'],
                        'descripcion' => $linea['descripcion'],
                        'debe'        => $linea['debe'],
                        'haber'       => $linea['haber'],
                        'orden'       => $maxOrden + $i + 1,
                    ]);
                }

                $headModel->update($existing->id, [
                    'total_debe'  => round($existing->total_debe  + $monto, 2),
                    'total_haber' => round($existing->total_haber + $monto, 2),
                ]);

                $headModel->aprobarConSaldos($existing->id, $lineasAsiento, $periodo->id, $fecha, $descLinea, 'DIARIO', $periodo);

                $creados[] = [
                    'factura' => $correlativo,
                    'asiento' => 'AST-' . str_pad($existing->numero_asiento, 5, '0', STR_PAD_LEFT),
                    'monto'   => $monto,
                ];
            } else {
                // Nueva partida del día
                $anioFecha     = (int)substr($fecha, 0, 4);
                $numPartida    = $tipoPartidaId ? $headModel->getSiguienteNumeroPartida($tipoPartidaId, $anioFecha) : null;
                $numeroAsiento = $headModel->getSiguienteNumero();

                $asientoId = $headModel->insert([
                    'numero_asiento'     => $numeroAsiento,
                    'numero_partida'     => $numPartida,
                    'fecha'              => $fecha,
                    'descripcion'        => 'Pagos ' . $fecha,
                    'tipo'               => 'DIARIO',
                    'tipo_partida_id'    => $tipoPartidaId,
                    'estado'             => 'APROBADO',
                    'periodo_id'         => $periodo->id,
                    'total_debe'         => $monto,
                    'total_haber'        => $monto,
                    'referencia'         => 'PAGO-' . $pagoId,
                    'documento_tipo'     => 'pago',
                    'documento_id'       => $pagoId,
                    'usuario_id'         => $userId,
                    'usuario_aprueba_id' => $userId,
                    'fecha_aprobacion'   => date('Y-m-d H:i:s'),
                ]);

                if (!$asientoId) {
                    $creados[] = ['factura' => $correlativo, 'error' => 'No se pudo insertar el asiento'];
                    continue;
                }

                foreach ($lineasAsiento as $i => $linea) {
                    $detModel->insert([
                        'asiento_id'  => $asientoId,
                        'cuenta_id'   => $linea['cuenta_id'],
                        'descripcion' => $linea['descripcion'],
                        'debe'        => $linea['debe'],
                        'haber'       => $linea['haber'],
                        'orden'       => $i + 1,
                    ]);
                }

                $headModel->aprobarConSaldos($asientoId, $lineasAsiento, $periodo->id, $fecha, $descLinea, 'DIARIO', $periodo);

                $creados[] = [
                    'factura' => $correlativo,
                    'asiento' => 'AST-' . str_pad($numeroAsiento, 5, '0', STR_PAD_LEFT),
                    'monto'   => $monto,
                ];
            }
        }

        return $creados;
    }
    public function anular($id)
    {
        $pagoHeadModel    = new PagosHeadModel();
        $pagoDetailsModel = new PagosDetailsModel();
        $facturaHeadModel = new FacturaHeadModel();
        $accountModel     = new AccountModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $pago = $pagoHeadModel->find($id);

        if (!$pago) {
            return redirect()->back()->with('error', 'Pago no encontrado');
        }

        if ($pago->anulado) {
            return redirect()->back()->with('error', 'El pago ya está anulado');
        }

        // Obtener solo detalles activos

        $detallesActivos = $pagoDetailsModel
            ->where('pago_id', $id)
            ->where('anulado', 0)
            ->findAll();

        $totalARevertir = 0;
        foreach ($detallesActivos as $detalle) {

            $db->table('facturas_head')
                ->where('id', $detalle->factura_id)
                ->set('saldo', 'saldo + ' . $detalle->monto, false)
                ->update();

            $totalARevertir += $detalle->monto;

            $pagoDetailsModel->update($detalle->id, [
                'anulado'    => 1,
                'anulado_at' => date('Y-m-d H:i:s'),
                'anulado_by' => session()->get('user_id')
            ]);
        }
        // Reversión bancaria SOLO si hay monto activo

        if (!empty($pago->numero_cuenta_bancaria) && $totalARevertir > 0) {

            $accountId = $pago->numero_cuenta_bancaria;

            $db->table('transactions')->insert([
                'account_id'  => $accountId,
                'tracking_id' => $pago->id,
                'tipo'        => 'salida',
                'monto'       => $totalARevertir,
                'origen'      => 'anulacion_pago',
                'referencia'  => 'Anulación de pago #' . $pago->id,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            // Recalcular balance real desde transactions
            $nuevoBalance = $accountModel->getBalance($accountId);

            $accountModel->update($accountId, [
                'balance' => $nuevoBalance
            ]);
        }

        // Marcar pago como anulado

        $pagoHeadModel->update($id, [
            'anulado' => 1
        ]);

        // Registrar bitácora

        $session = session();

        registrar_bitacora(
            'Anulación de pago #' . esc($pago->id),
            'Pagos',
            'Se anuló el pago #' . esc($pago->id) .
                ' por un monto total de $' . number_format($totalARevertir, 2) .
                '. Se revirtieron los saldos aplicados a las facturas correspondientes.',
            $session->get('user_id')
        );
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()
                ->with('error', 'Error al anular el pago');
        }

        // ======== REVERSIÓN DE ASIENTOS CONTABLES ========
        $asientoMsg = '';
        try {
            $this->_revertirAsientosPago((int)$id, $detallesActivos, (int)$pago->cliente_id);
        } catch (\Throwable $e) {
            $asientoMsg = ' ⚠ Asientos no revertidos: ' . $e->getMessage();
        }

        return redirect()->to(base_url('payments/' . $id))
            ->with('success', 'Pago anulado correctamente y movimiento compensado.' . $asientoMsg);
    }

    private function _revertirAsientosPago(int $pagoId, array $detalles, int $clienteId): void
    {
        $planModel     = new \App\Models\ContPlanCuentasModel();
        $clienteModel  = new \App\Models\ClienteModel();
        $periodosModel = new \App\Models\ContPeriodosModel();
        $headModel     = new \App\Models\ContAsientosHeadModel();
        $detModel      = new \App\Models\ContAsientosDetalleModel();
        $facturaModel  = new \App\Models\FacturaHeadModel();

        $cuentaCaja = $planModel->where('codigo', '11010101')->first();
        if (!$cuentaCaja) {
            throw new \Exception('No existe la cuenta 11010101 CAJA GRANDE');
        }

        $cliente = $clienteModel->find($clienteId);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado');
        }

        $cxcId = (int)($cliente->cuenta_contable_id ?? 0) ?: null;
        if (!$cxcId) {
            throw new \Exception('El cliente no tiene cuenta CxC asignada');
        }

        $periodo = $periodosModel->getPeriodoActual();
        if (!$periodo) {
            throw new \Exception('No hay período contable abierto para la reversión');
        }

        $cfg           = (new \App\Models\ContConfiguracionModel())->getConfig();
        $tipoPartidaId = !empty($cfg->tipo_partida_pagos_id) ? (int)$cfg->tipo_partida_pagos_id : null;

        $fechaReversa  = date('Y-m-d');
        $userId        = session()->get('id');
        $totalBruto    = 0;
        $lineasReversa = [];

        foreach ($detalles as $det) {
            $monto     = (float)$det->monto;
            $retMonto  = round((float)($det->retencion_monto ?? 0), 2);
            $retCuenta = !empty($det->retencion_cuenta_id) ? (int)$det->retencion_cuenta_id : null;
            $montoNeto = round($monto - $retMonto, 2);

            $factura     = $facturaModel->find($det->factura_id);
            $correlativo = $factura ? substr($factura->numero_control, -6) : 'FAC-' . $det->factura_id;
            $descLinea   = 'REV.Pago ' . $correlativo . ' — ' . $cliente->nombre;

            // DEBE CxC: restituye la deuda del cliente (bruto)
            $lineasReversa[] = ['cuenta_id' => $cxcId,          'descripcion' => $descLinea,                       'debe' => $monto,     'haber' => 0         ];
            // HABER Caja: sale el efectivo recibido (neto)
            $lineasReversa[] = ['cuenta_id' => $cuentaCaja->id, 'descripcion' => $descLinea,                       'debe' => 0,          'haber' => $montoNeto];
            // HABER CuentaRetención si aplica
            if ($retMonto > 0 && $retCuenta) {
                $lineasReversa[] = ['cuenta_id' => $retCuenta, 'descripcion' => 'REV.Ret. 10% ' . $correlativo, 'debe' => 0, 'haber' => $retMonto];
            }

            $totalBruto += $monto;
        }

        $numPartida    = $tipoPartidaId ? $headModel->getSiguienteNumeroPartida($tipoPartidaId, (int)substr($fechaReversa, 0, 4)) : null;
        $numeroAsiento = $headModel->getSiguienteNumero();

        $asientoId = $headModel->insert([
            'numero_asiento'     => $numeroAsiento,
            'numero_partida'     => $numPartida,
            'fecha'              => $fechaReversa,
            'descripcion'        => 'Reversa pago #' . $pagoId . ' — ' . $cliente->nombre,
            'tipo'               => 'DIARIO',
            'tipo_partida_id'    => $tipoPartidaId,
            'estado'             => 'APROBADO',
            'periodo_id'         => $periodo->id,
            'total_debe'         => round($totalBruto, 2),
            'total_haber'        => round($totalBruto, 2),
            'referencia'         => 'REVERSA-PAGO-' . $pagoId,
            'usuario_id'         => $userId,
            'usuario_aprueba_id' => $userId,
            'fecha_aprobacion'   => date('Y-m-d H:i:s'),
        ]);

        if (!$asientoId) {
            throw new \Exception('No se pudo insertar el asiento de reversión');
        }

        foreach ($lineasReversa as $i => $linea) {
            $detModel->insert([
                'asiento_id'  => $asientoId,
                'cuenta_id'   => $linea['cuenta_id'],
                'descripcion' => $linea['descripcion'],
                'debe'        => $linea['debe'],
                'haber'       => $linea['haber'],
                'orden'       => $i + 1,
            ]);
        }

        $headModel->aprobarConSaldos($asientoId, $lineasReversa, $periodo->id, $fechaReversa, 'Reversa pago #' . $pagoId, 'DIARIO', $periodo);
    }
}
