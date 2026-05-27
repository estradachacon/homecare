<?php

namespace App\Controllers;

use App\Models\FacturaHeadModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaJsonModel;
use App\Models\EmisorModel;
use App\Models\ClienteModel;
use App\Models\ProductoModel;
use App\Models\ProductoMovimientoModel;
use App\Services\DteBuilderService;
use App\Services\DteSignerService;
use App\Services\HaciendaApiService;

class DteController extends BaseController
{
    protected FacturaHeadModel    $headModel;
    protected FacturaDetalleModel $detalleModel;
    protected FacturaJsonModel    $jsonModel;

    public function __construct()
    {
        $this->headModel    = new FacturaHeadModel();
        $this->detalleModel = new FacturaDetalleModel();
        $this->jsonModel    = new FacturaJsonModel();
    }

    private function clienteTieneNrc(?int $clienteId): bool
    {
        if (!$clienteId) {
            return false;
        }

        $cliente = (new ClienteModel())
            ->select('nrc')
            ->find($clienteId);

        return !empty(trim((string) ($cliente->nrc ?? '')));
    }

    private function resolverProductoId(array $item): ?int
    {
        $tipoItem = (int)($item['tipoItem'] ?? 1);
        $codigo = trim((string)($item['codigo'] ?? ''));
        $descripcion = trim((string)($item['descripcion'] ?? ''));

        if ($tipoItem === 2) {
            $codigo = 'SERV';
        }

        if ($codigo === '') {
            $codigo = $tipoItem === 2 ? 'SERV' : null;
        }

        $productoModel = new ProductoModel();
        $producto = $codigo ? $productoModel->where('codigo', $codigo)->first() : null;

        if ($producto) {
            return (int)$producto->id;
        }

        $productoId = $productoModel->insert([
            'codigo'      => $codigo,
            'descripcion' => $tipoItem === 2 ? 'Servicio' : ($descripcion ?: 'Producto'),
            'tipo'        => $tipoItem,
        ]);

        return $productoId ? (int)$productoId : null;
    }

    private function registrarMovimientoInventario(array $item, int $facturaId, int $productoId): void
    {
        $tipoItem = (int)($item['tipoItem'] ?? 0);
        $cantidad = (float)($item['cantidad'] ?? 0);

        if ($tipoItem !== 1 || $cantidad <= 0) {
            return;
        }

        (new ProductoMovimientoModel())->insert([
            'producto_id'     => $productoId,
            'tipo_movimiento' => 'venta',
            'cantidad'        => -abs($cantidad),
            'referencia_tipo' => 'factura',
            'referencia_id'   => $facturaId,
        ]);
    }

    private function cuentaCxcCliente(?int $clienteId): ?int
    {
        if (!$clienteId) {
            return null;
        }

        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->select('id, nombre, cuenta_contable_id')->find($clienteId);

        if (!$cliente) {
            return null;
        }

        if (!empty($cliente->cuenta_contable_id)) {
            return (int)$cliente->cuenta_contable_id;
        }

        $planModel = new \App\Models\ContPlanCuentasModel();
        $padre = $planModel->where('codigo', '110201')->first();

        if (!$padre) {
            return null;
        }

        $nombreCuenta = mb_strtoupper($cliente->nombre);
        $existente = $planModel
            ->like('codigo', '110201', 'after')
            ->where('nombre', $nombreCuenta)
            ->first();

        if ($existente) {
            $clienteModel->update($cliente->id, ['cuenta_contable_id' => $existente->id]);
            return (int)$existente->id;
        }

        $db = \Config\Database::connect();
        $siguiente = (int)($db->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 7) AS UNSIGNED)), 0) + 1 AS sig
             FROM cont_plan_cuentas
             WHERE codigo LIKE '110201%' AND LENGTH(codigo) > 6"
        )->getRow()->sig ?? 1);

        $cuentaId = $planModel->insert([
            'codigo'             => '110201' . str_pad($siguiente, 4, '0', STR_PAD_LEFT),
            'nombre'             => $nombreCuenta,
            'tipo'               => $padre->tipo,
            'naturaleza'         => $padre->naturaleza,
            'nivel'              => $padre->nivel + 1,
            'cuenta_padre_id'    => $padre->id,
            'acepta_movimientos' => 1,
            'activo'             => 1,
        ]);

        if ($cuentaId) {
            $clienteModel->update($cliente->id, ['cuenta_contable_id' => $cuentaId]);
            return (int)$cuentaId;
        }

        return null;
    }

    private function crearAsientoVenta(array $dte, int $facturaId, ?int $clienteId): array
    {
        $tipoDte = $dte['identificacion']['tipoDte'] ?? '';

        if (!in_array($tipoDte, ['01', '03'], true)) {
            return ['creado' => false, 'omitido' => null];
        }

        helper('cont_ventas');

        $fechaEmision = $dte['identificacion']['fecEmi'] ?? date('Y-m-d');
        $periodosModel = new \App\Models\ContPeriodosModel();
        $periodo = $periodosModel->abrirObtenerPeriodo((int)substr($fechaEmision, 0, 4), (int)substr($fechaEmision, 5, 2));
        $ref = substr((string)($dte['identificacion']['numeroControl'] ?? ''), -6);

        if (!$periodo) {
            return ['creado' => false, 'omitido' => "{$ref}: periodo contable cerrado"];
        }

        $tipoDteHelper = $tipoDte === '03' ? 'CCF' : 'FAC';
        $monto = $tipoDte === '03'
            ? (float)($dte['resumen']['totalGravada'] ?? 0)
            : (float)($dte['resumen']['montoTotalOperacion'] ?? $dte['resumen']['totalPagar'] ?? 0);

        if ($monto <= 0) {
            return ['creado' => false, 'omitido' => "{$ref}: monto invalido"];
        }

        $resultado = cont_asiento_venta_json(
            $tipoDteHelper,
            $monto,
            (float)($dte['resumen']['ivaRete1'] ?? 0),
            $ref,
            $periodo->id,
            $fechaEmision,
            "Venta {$tipoDteHelper} {$ref}",
            null,
            $this->cuentaCxcCliente($clienteId)
        );

        if (!$resultado['ok']) {
            return ['creado' => false, 'omitido' => "{$ref}: " . implode(', ', $resultado['errores'])];
        }

        $payload = $resultado['payload'];
        $contHeadModel = new \App\Models\ContAsientosHeadModel();
        $contDetalleModel = new \App\Models\ContAsientosDetalleModel();
        $tipoPartidaId = $payload['tipo_partida_id'] ?? null;
        $fechaAsiento = $payload['fecha'];
        $totalDebe = round(array_sum(array_column($payload['lineas'], 'debe')), 2);
        $totalHaber = round(array_sum(array_column($payload['lineas'], 'haber')), 2);

        $existing = $tipoPartidaId
            ? $contHeadModel->buscarPartidaDia($tipoPartidaId, $fechaAsiento)
            : null;

        if ($existing) {
            $db = \Config\Database::connect();
            $maxOrden = (int)($db->query(
                'SELECT COALESCE(MAX(orden), 0) AS m FROM cont_asientos_detalle WHERE asiento_id = ?',
                [$existing->id]
            )->getRow()->m ?? 0);

            foreach ($payload['lineas'] as $i => $linea) {
                $contDetalleModel->insert([
                    'asiento_id'  => $existing->id,
                    'cuenta_id'   => $linea['cuenta_id'],
                    'descripcion' => $linea['descripcion'],
                    'debe'        => $linea['debe'],
                    'haber'       => $linea['haber'],
                    'orden'       => $maxOrden + $i + 1,
                ]);
            }

            $contHeadModel->update($existing->id, [
                'total_debe'  => round($existing->total_debe + $totalDebe, 2),
                'total_haber' => round($existing->total_haber + $totalHaber, 2),
            ]);

            $asientoId = (int)$existing->id;
        } else {
            $numPartida = $tipoPartidaId
                ? $contHeadModel->getSiguienteNumeroPartida($tipoPartidaId, (int)substr($fechaAsiento, 0, 4))
                : null;

            $asientoId = (int)$contHeadModel->insert([
                'numero_asiento'     => $contHeadModel->getSiguienteNumero(),
                'numero_partida'     => $numPartida,
                'fecha'              => $fechaAsiento,
                'descripcion'        => $payload['descripcion'],
                'tipo'               => $payload['tipo'],
                'tipo_partida_id'    => $tipoPartidaId,
                'estado'             => 'APROBADO',
                'periodo_id'         => $payload['periodo_id'],
                'total_debe'         => $totalDebe,
                'total_haber'        => $totalHaber,
                'referencia'         => $payload['referencia'],
                'documento_tipo'     => 'factura',
                'documento_id'       => $facturaId,
                'usuario_id'         => session()->get('user_id') ?? session()->get('id'),
                'usuario_aprueba_id' => session()->get('user_id') ?? session()->get('id'),
                'fecha_aprobacion'   => date('Y-m-d H:i:s'),
            ]);

            foreach ($payload['lineas'] as $i => $linea) {
                $contDetalleModel->insert([
                    'asiento_id'  => $asientoId,
                    'cuenta_id'   => $linea['cuenta_id'],
                    'descripcion' => $linea['descripcion'],
                    'debe'        => $linea['debe'],
                    'haber'       => $linea['haber'],
                    'orden'       => $i + 1,
                ]);
            }
        }

        $contHeadModel->aprobarConSaldos(
            $asientoId,
            $payload['lineas'],
            (int)$payload['periodo_id'],
            $fechaAsiento,
            $payload['descripcion'],
            $payload['tipo'],
            $periodo
        );

        return ['creado' => true, 'omitido' => null];
    }

    // ─────────────────────────────────────────────
    //  LISTADO DE DTEs EMITIDOS
    // ─────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $perPage = (int)($this->request->getGet('per_page') ?? 25);
        if (!in_array($perPage, [10, 15, 25, 50, 100, 99999])) $perPage = 25;

        $q = $this->headModel
            ->select('facturas_head.*, clientes.nombre AS cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.emitido', 1);

        if ($tipoDte = $this->request->getGet('tipo_dte')) {
            $q->where('facturas_head.tipo_dte', $tipoDte);
        }
        if ($estado = $this->request->getGet('estado')) {
            if ($estado === 'anulada') $q->where('facturas_head.anulada', 1);
            else $q->where('facturas_head.anulada', 0);
        }
        if ($fecha = $this->request->getGet('fecha')) {
            $q->where('facturas_head.fecha_emision', $fecha);
        }
        if ($numero = $this->request->getGet('numero')) {
            $q->like('facturas_head.numero_control', $numero, 'before');
        }

        $q->orderBy('facturas_head.fecha_emision', 'DESC')
          ->orderBy('facturas_head.id', 'DESC');

        $dtEs  = $q->paginate($perPage);
        $pager = $this->headModel->pager;

        return view('emisiondte/index', compact('dtEs', 'pager', 'perPage'));
    }

    // ─────────────────────────────────────────────
    //  FORMULARIO EMISIÓN
    // ─────────────────────────────────────────────

    public function new()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $emisor = new EmisorModel();
        $emisor = $emisor->first();
        $fechaHoraSv = new \DateTimeImmutable('now', new \DateTimeZone('America/El_Salvador'));

        return view('emisiondte/create', [
            'emisor' => $emisor,
            'fecha_sv' => $fechaHoraSv->format('Y-m-d'),
            'hora_sv' => $fechaHoraSv->format('H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GUARDAR Y ENVIAR DTE (JSON POST)
    // ─────────────────────────────────────────────

    public function store()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $data = (array) $this->request->getJSON(true);
        $fechaHoraSv = new \DateTimeImmutable('now', new \DateTimeZone('America/El_Salvador'));
        $data['fecha_emision'] = $fechaHoraSv->format('Y-m-d');
        $data['hora_emision']  = $fechaHoraSv->format('H:i:s');

        // Validación mínima
        if (empty($data['tipo_dte']) || empty($data['fecha_emision']) || empty($data['items'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos. Se requiere tipo_dte, fecha_emision e items.',
            ]);
        }

        if (!is_array($data['items']) || count($data['items']) === 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe incluir al menos un producto.',
            ]);
        }

        if (($data['tipo_dte'] ?? '') === '03' && !$this->clienteTieneNrc((int) ($data['cliente_id'] ?? 0))) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El cliente seleccionado no cumple para emitir Credito Fiscal porque no tiene NRC registrado.',
            ]);
        }

        try {
            // 1. Construir JSON del DTE
            $builder = new DteBuilderService();

            $dte = match ($data['tipo_dte']) {
                '01'    => $builder->buildFactura($data),
                '03'    => $builder->buildCCF($data),
                '04'    => $builder->buildNR($data),
                default => throw new \InvalidArgumentException("Tipo DTE '{$data['tipo_dte']}' no soportado."),
            };

            $ambienteHacienda = env('hacienda.env', '00');
            $modoLocal = $ambienteHacienda === '03';
            $selloRecibido = null;
            $mensajeMh     = null;

            if ($modoLocal) {
                $dteFirmado = $dte;
                $dteFirmado['firma'] = 'MODO_LOCAL_SIN_FIRMA';
                $estadoMh = 'local';
                $response = [
                    'http_code' => 0,
                    'body' => [
                        'estado' => 'LOCAL',
                        'descripcionMsg' => 'Modo local desconectado: no se firmo ni se transmitio a Hacienda.',
                    ],
                    'error' => null,
                ];
                $mensajeMh = $response['body']['descripcionMsg'];
            } else {
                // 2. Firmar
                $signer    = new DteSignerService();
                $dteFirmado = $signer->firmar($dte);
                $payload   = $signer->buildPayload($dteFirmado);

                // 3. Transmitir a MH
                $api      = new HaciendaApiService();
                $response = $api->post('fesv/recepciondte', $payload);
                $estadoMh = 'error';

                if ($response['http_code'] === 200 && isset($response['body']['selloRecibido'])) {
                    $selloRecibido = $response['body']['selloRecibido'];
                    $estadoMh      = 'procesado';
                    $dteFirmado['identificacion']['selloRecibido'] = $selloRecibido;
                } elseif ($response['http_code'] === 200 && isset($response['body']['estado'])) {
                    $estadoMh  = strtolower($response['body']['estado']);
                    $mensajeMh = $response['body']['descripcionMsg'] ?? null;
                } else {
                    $mensajeMh = $response['error'] ?? json_encode($response['body'] ?? []);
                }
            }

            // 4. Persistir en DB
            $ident   = $dte['identificacion'];
            $resumen = $dte['resumen'];
            $totalPagar = (float)($resumen['totalPagar'] ?? $resumen['montoTotalOperacion'] ?? 0);
            $totalIvaHead = (float)($resumen['totalIva'] ?? 0);

            if (empty($totalIvaHead) && !empty($resumen['tributos'])) {
                foreach ($resumen['tributos'] as $tributo) {
                    if (($tributo['codigo'] ?? null) === '20') {
                        $totalIvaHead = (float)$tributo['valor'];
                    }
                }
            }

            if ($ident['tipoDte'] === '01') {
                $totalGravadaHead = round($totalPagar / 1.13, 2);
                $totalIvaHead = round($totalPagar - $totalGravadaHead, 2);
            } else {
                $totalGravadaHead = (float)($resumen['totalGravada'] ?? 0);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            $existe = $this->headModel
                ->where('numero_control', $ident['numeroControl'])
                ->first();

            if ($existe) {
                throw new \RuntimeException('Ya existe una factura con este numero de control.');
            }

            $facturaId = $this->headModel->insert([
                'ambiente'           => $ident['ambiente'],
                'tipo_dte'           => $ident['tipoDte'],
                'emitido'            => 1,
                'estado_mh'          => $estadoMh,
                'respuesta_mh'       => json_encode($response['body'] ?? [], JSON_UNESCAPED_UNICODE),
                'numero_control'     => $ident['numeroControl'],
                'codigo_generacion'  => $ident['codigoGeneracion'],
                'fecha_emision'      => $ident['fecEmi'],
                'hora_emision'       => $ident['horEmi'],
                'tipo_moneda'        => 'USD',
                'sello_recibido'     => $selloRecibido,
                'receptor_id'        => $data['cliente_id'] ?? null,
                'total_gravada'      => $totalGravadaHead,
                'sub_total'          => $resumen['subTotal'],
                'total_iva'          => $totalIvaHead,
                'monto_total_operacion' => $resumen['montoTotalOperacion'],
                'total_pagar'        => $totalPagar,
                'saldo'              => $totalPagar,
                'condicion_operacion'=> $resumen['condicionOperacion'],
                'plazo_credito'      => $data['plazo_credito'] ?? null,
                'vendedor_id'        => session()->get('user_id') ?? session()->get('id'),
                'firma_electronica'  => $dteFirmado['firma'],
                'anulada'            => 0,
                'tipo_venta'         => $data['tipo_venta'] ?? 1,
                'iva_rete1'          => $resumen['ivaRete1'] ?? 0,
            ]);

            foreach ($dte['cuerpoDocumento'] as $item) {
                $productoId = $this->resolverProductoId($item);

                if (!$productoId) {
                    throw new \RuntimeException('No se pudo determinar el producto para la linea ' . ($item['numItem'] ?? ''));
                }

                $ventaGravada = (float)($item['ventaGravada'] ?? 0);
                $ivaItem = (float)($item['ivaItem'] ?? 0);

                if ($ident['tipoDte'] === '01') {
                    $base = round($ventaGravada / 1.13, 2);
                    $ivaItem = round($ventaGravada - $base, 2);
                    $ventaGravada = $base;
                }

                $this->detalleModel->insert([
                    'factura_id'      => $facturaId,
                    'producto_id'     => $productoId,
                    'num_item'        => $item['numItem'],
                    'tipo_item'       => $item['tipoItem'],
                    'cantidad'        => $item['cantidad'],
                    'descripcion'     => $item['descripcion'],
                    'precio_unitario' => $item['precioUni'],
                    'monto_descuento' => $item['montoDescu'],
                    'venta_no_sujeta' => $item['ventaNoSuj'],
                    'venta_exenta'    => $item['ventaExenta'],
                    'venta_gravada'   => $ventaGravada,
                    'iva_item'        => $ivaItem,
                    'codigo'          => $item['codigo'],
                    'unidad_medida'   => $item['uniMedida'],
                ]);

                $this->registrarMovimientoInventario($item, (int)$facturaId, $productoId);
            }

            $this->jsonModel->guardarJson($facturaId, json_encode($dteFirmado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $db->transComplete();

            if (!$db->transStatus()) {
                throw new \RuntimeException('Error al guardar el DTE en la base de datos.');
            }

            unset($db);

            $asiento = $this->crearAsientoVenta($dte, (int)$facturaId, (int)($data['cliente_id'] ?? 0));

            registrar_bitacora(
                'Emitir DTE',
                'Facturación Electrónica',
                "DTE {$ident['tipoDte']} {$ident['numeroControl']} emitido por $" .
                number_format($resumen['montoTotalOperacion'], 2),
                session()->get('id')
            );

            return $this->response->setJSON([
                'success'      => true,
                'estado_mh'    => $estadoMh,
                'sello'        => $selloRecibido,
                'numero'       => $ident['numeroControl'],
                'total'        => $totalPagar,
                'factura_id'   => $facturaId,
                'mensaje'      => $mensajeMh,
                'asiento_creado' => $asiento['creado'],
                'asiento_omitido'=> $asiento['omitido'],
            ]);

        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->transRollback();
            }

            log_message('error', '[DteController::store] ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────
    //  DETALLE / VISTA DTE EMITIDO
    // ─────────────────────────────────────────────

    public function previewJson()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $data    = (array) $this->request->getJSON(true);
        $tipoDte = $data['tipo_dte'] ?? '';

        if (!in_array($tipoDte, ['01', '03', '04'], true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Tipo de DTE no soportado para previsualización.',
            ]);
        }

        if (empty($data['cliente_id']) || empty($data['items']) || !is_array($data['items'])) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Seleccione cliente y agregue al menos un producto.',
            ]);
        }

        if ($tipoDte === '03' && !$this->clienteTieneNrc((int) ($data['cliente_id'] ?? 0))) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El cliente seleccionado no cumple para emitir Credito Fiscal porque no tiene NRC registrado.',
            ]);
        }

        try {
            $fechaHoraSv = new \DateTimeImmutable('now', new \DateTimeZone('America/El_Salvador'));
            $data['fecha_emision'] = $fechaHoraSv->format('Y-m-d');
            $data['hora_emision']  = $fechaHoraSv->format('H:i:s');
            $data['codigo_generacion'] = null;

            $builder = new DteBuilderService();
            $dte = match ($tipoDte) {
                '03'    => $builder->buildCCF($data),
                '04'    => $builder->buildNR($data),
                default => $builder->buildFactura($data),
            };

            return $this->response->setJSON([
                'success' => true,
                'dte'     => $dte,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[DteController::previewJson] ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function show(int $id)
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $dte = $this->headModel
            ->select('facturas_head.*, clientes.nombre AS cliente_nombre, clientes.numero_documento, clientes.nrc')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.id', $id)
            ->where('facturas_head.emitido', 1)
            ->first();

        if (!$dte) {
            return redirect()->to('/emision-dte')->with('error', 'DTE no encontrado.');
        }

        $detalles = $this->detalleModel->where('factura_id', $id)->findAll();
        $jsonDte  = $this->jsonModel->getByFactura($id);

        return view('emisiondte/show', compact('dte', 'detalles', 'jsonDte'));
    }

    // ─────────────────────────────────────────────
    //  CONSULTAR ESTADO EN MH
    // ─────────────────────────────────────────────

    public function consultarEstado(int $id)
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $dte = $this->headModel->find($id);
        if (!$dte || !$dte->emitido) {
            return $this->response->setJSON(['success' => false, 'message' => 'DTE no encontrado.']);
        }

        if (($dte->ambiente ?? env('hacienda.env', '00')) === '03') {
            return $this->response->setJSON([
                'success' => true,
                'estado'  => 'local',
                'sello'   => null,
                'body'    => [
                    'estado' => 'LOCAL',
                    'descripcionMsg' => 'Modo local desconectado: no hay consulta a Hacienda.',
                ],
            ]);
        }

        try {
            $api      = new HaciendaApiService();
            $ambiente = env('hacienda.env', '00');
            $endpoint = "fesv/consultadte?ambiente={$ambiente}&codigoGeneracion={$dte->codigo_generacion}&fechaEmi={$dte->fecha_emision}";
            $response = $api->get($endpoint);

            $estado = $response['body']['estado'] ?? null;
            $sello  = $response['body']['selloRecibido'] ?? $dte->sello_recibido;

            if ($estado) {
                $this->headModel->update($id, [
                    'estado_mh'      => strtolower($estado),
                    'sello_recibido' => $sello ?: $dte->sello_recibido,
                    'respuesta_mh'   => json_encode($response['body'] ?? []),
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'estado'  => $estado,
                'sello'   => $sello,
                'body'    => $response['body'],
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────
    //  PRÓXIMO NÚMERO DE CONTROL (AJAX)
    // ─────────────────────────────────────────────

    public function proximoNumero(string $tipoDte)
    {
        try {
            $builder = new DteBuilderService();
            return $this->response->setJSON([
                'numero' => $builder->siguienteNumeroControl($tipoDte),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['numero' => '—', 'error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────
    //  NOTA DE CRÉDITO — FORMULARIO
    // ─────────────────────────────────────────────

    public function nuevaNc(int $id)
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $original = $this->headModel
            ->select('facturas_head.*, clientes.nombre AS cliente_nombre, clientes.numero_documento, clientes.nrc')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.id', $id)
            ->where('facturas_head.emitido', 1)
            ->first();

        if (!$original) {
            return redirect()->to('/emision-dte')->with('error', 'Documento original no encontrado.');
        }

        if ($original->anulada) {
            return redirect()->to('/emision-dte/' . $id)->with('error', 'No se puede generar NC de un documento anulado.');
        }

        if (!in_array($original->tipo_dte, ['01', '03'])) {
            return redirect()->to('/emision-dte/' . $id)->with('error', 'Solo se puede generar NC desde Facturas y CCF.');
        }

        $detalles = $this->detalleModel
            ->where('factura_id', $id)
            ->orderBy('num_item', 'ASC')
            ->findAll();

        return view('emisiondte/nc', compact('original', 'detalles'));
    }

    // ─────────────────────────────────────────────
    //  NOTA DE CRÉDITO — GUARDAR Y ENVIAR
    // ─────────────────────────────────────────────

    public function storeNc()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $data = $this->request->getJSON(true);

        if (empty($data['original_id']) || empty($data['items'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos.',
            ]);
        }

        $original = $this->headModel->find($data['original_id']);
        if (!$original || !$original->emitido) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento original no encontrado.']);
        }

        try {
            $builder    = new DteBuilderService();
            $dte        = $builder->buildNC($data, $original);

            $signer     = new DteSignerService();
            $dteFirmado = $signer->firmar($dte);
            $payload    = $signer->buildPayload($dteFirmado);

            $api      = new HaciendaApiService();
            $response = $api->post('fesv/recepciondte', $payload);

            $selloRecibido = $response['body']['selloRecibido'] ?? null;
            $estadoMh      = $selloRecibido ? 'procesado'
                           : strtolower($response['body']['estado'] ?? 'error');
            $mensajeMh     = $response['body']['descripcionMsg'] ?? $response['error'] ?? null;

            $ident   = $dte['identificacion'];
            $resumen = $dte['resumen'];

            $db = \Config\Database::connect();
            $db->transStart();

            $ncId = $this->headModel->insert([
                'ambiente'              => $ident['ambiente'],
                'tipo_dte'              => '05',
                'emitido'               => 1,
                'estado_mh'             => $estadoMh,
                'respuesta_mh'          => json_encode($response['body'] ?? []),
                'numero_control'        => $ident['numeroControl'],
                'codigo_generacion'     => $ident['codigoGeneracion'],
                'codigo_generacion_relacionado' => $original->codigo_generacion,
                'fecha_emision'         => $ident['fecEmi'],
                'hora_emision'          => $ident['horEmi'],
                'tipo_moneda'           => 'USD',
                'sello_recibido'        => $selloRecibido,
                'receptor_id'           => $original->receptor_id,
                'total_gravada'         => $resumen['totalGravada'],
                'sub_total'             => $resumen['subTotal'],
                'total_iva'             => $resumen['totalIva'],
                'monto_total_operacion' => $resumen['montoTotalOperacion'],
                'total_pagar'           => $resumen['montoTotalOperacion'],
                'saldo'                 => 0,
                'condicion_operacion'   => $resumen['condicionOperacion'],
                'vendedor_id'           => session()->get('id'),
                'firma_electronica'     => $dteFirmado['firma'],
                'anulada'               => 0,
            ]);

            foreach ($dte['cuerpoDocumento'] as $item) {
                $this->detalleModel->insert([
                    'factura_id'      => $ncId,
                    'num_item'        => $item['numItem'],
                    'tipo_item'       => $item['tipoItem'],
                    'cantidad'        => $item['cantidad'],
                    'descripcion'     => $item['descripcion'],
                    'precio_unitario' => $item['precioUni'],
                    'monto_descuento' => $item['montoDescu'],
                    'venta_no_sujeta' => $item['ventaNoSuj'],
                    'venta_exenta'    => $item['ventaExenta'],
                    'venta_gravada'   => $item['ventaGravada'],
                    'iva_item'        => $item['ivaItem'],
                    'codigo'          => $item['codigo'],
                    'unidad_medida'   => $item['uniMedida'],
                ]);
            }

            $this->jsonModel->guardarJson($ncId, $dteFirmado);

            // Reducir saldo del documento original
            $nuevoSaldo = max(0, $original->saldo - $resumen['montoTotalOperacion']);
            $this->headModel->update($original->id, ['saldo' => $nuevoSaldo]);

            $db->transComplete();

            if (!$db->transStatus()) {
                throw new \RuntimeException('Error al guardar la Nota de Crédito.');
            }

            registrar_bitacora(
                'Emitir Nota de Crédito',
                'Facturación Electrónica',
                "NC {$ident['numeroControl']} referencia {$original->numero_control} por $" .
                number_format($resumen['montoTotalOperacion'], 2),
                session()->get('id')
            );

            return $this->response->setJSON([
                'success'    => true,
                'estado_mh'  => $estadoMh,
                'sello'      => $selloRecibido,
                'numero'     => $ident['numeroControl'],
                'total'      => $resumen['montoTotalOperacion'],
                'factura_id' => $ncId,
                'mensaje'    => $mensajeMh,
            ]);

        } catch (\Throwable $e) {
            log_message('error', '[DteController::storeNc] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
