<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FacturaJsonModel;
use App\Models\ClienteModel;
use App\Models\FacturaHeadModel;
use App\Models\PagosDetailsModel;
use App\Models\PagosHeadModel;
use App\Models\TransactionModel;
use App\Models\AccountModel;
use App\Models\ProductoModel;
use App\Models\ProductoMovimientoModel;

class Facturas extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_facturas');
        if ($chk !== true) return $chk;

        $model = new FacturaHeadModel();

        // SELECT PRINCIPAL + JOINS
        $model->select(
            'facturas_head.*, 
            clientes.nombre AS cliente_nombre, 
            sellers.seller AS vendedor,
            tipo_venta.nombre_tipo_venta AS tipo_venta_nombre'
        )
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta', 'tipo_venta.id = facturas_head.tipo_venta', 'left');

        // ================= FILTROS =================

        $clienteId = $this->request->getGet('cliente_id');
        $sellerId  = $this->request->getGet('seller_id');
        $estado    = $this->request->getGet('estado');
        $tipoDte   = $this->request->getGet('tipo_dte');
        $fecha     = $this->request->getGet('fecha');
        $tipoVenta = $this->request->getGet('tipo_venta');
        $numeroFactura = trim($this->request->getGet('numero_factura'));

        if (is_numeric($clienteId)) {
            $model->where('facturas_head.receptor_id', $clienteId);
        }

        if (is_numeric($sellerId)) {
            $model->where('facturas_head.vendedor_id', $sellerId);
        }

        if ($estado === 'activa') {
            $model->where('facturas_head.anulada', 0);
        }

        if ($estado === 'anulada') {
            $model->where('facturas_head.anulada', 1);
        }

        if ($estado === 'pagada') {
            $model->where('facturas_head.anulada', 0)
                ->where('facturas_head.saldo', 0);
        }

        if (is_numeric($tipoDte)) {
            $model->where('facturas_head.tipo_dte', $tipoDte);
        }

        if (!empty($fecha)) {
            $model->where('facturas_head.fecha_emision', $fecha);
        }

        if (is_numeric($tipoVenta)) {
            $model->where('facturas_head.tipo_venta', $tipoVenta);
        }

        if (!empty($numeroFactura)) {
            $model->like('facturas_head.numero_control', $numeroFactura);
        }

        // ==========================================

        $model->orderBy('fecha_emision', 'DESC')
            ->orderBy("CAST(SUBSTRING(numero_control, -6) AS UNSIGNED)", 'DESC', false);

        $facturas = $model->paginate(10);
        $pager = $model->pager;

        // CATÁLOGO TIPO VENTA PARA EL SELECT
        $tipoVentaModel = new \App\Models\TipoVentaModel();
        $tiposVenta = $tipoVentaModel
            ->orderBy('nombre_tipo_venta')
            ->findAll();

        // RESPUESTA AJAX
        if ($this->request->isAJAX()) {

            $tbody = view('facturas/tbody_row', compact('facturas'));
            $pagerHtml = $pager->links('default', 'bootstrap_full');

            return $this->response->setJSON([
                'tbody' => $tbody,
                'pager' => $pagerHtml
            ]);
        }

        // VISTA NORMAL
        return view('facturas/index', compact('facturas', 'pager', 'tiposVenta'));
    }

    public function carga()
    {
        $chk = requerirPermiso('cargar_facturas');
        if ($chk !== true) return $chk;

        $emisorModel = new \App\Models\EmisorModel();
        $emisor = $emisorModel->first(); // solo debería existir uno

        return view('facturas/carga_procesado', [
            'emisor' => $emisor
        ]);
    }

    public function procesarCarga()
    {
        $user_id = session()->get('user_id');
        session_write_close();

        $files = $this->request->getFiles();
        $tipoVentaIds = $this->request->getPost('tipo_venta_ids');
        $sellerIds = $this->request->getPost('seller_ids');
        $plazos = $this->request->getPost('plazos_credito');
        $condiciones = $this->request->getPost('condiciones');

        if (!isset($files['archivos'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron archivos'
            ]);
        }

        $facturaHeadModel = new FacturaHeadModel();
        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();
        $facturaJsonModel    = new FacturaJsonModel();

        $productoModel = new ProductoModel();
        $movimientoModel = new ProductoMovimientoModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $facturasInsertadas = 0;
        $controles = [];
        $totalOperacion = 0;
        $seProcesoNC = false;

        foreach ($files['archivos'] as $index => $file) {

            if (!$file->isValid()) {
                continue;
            }

            $contenido = file_get_contents($file->getTempName());
            $json = json_decode($contenido, true);

            if (!$json || json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }
            $tipoDte = $json['identificacion']['tipoDte'] ?? null;

            $totalDte =
                $json['resumen']['totalPagar']
                ?? $json['resumen']['montoTotalOperacion']
                ?? 0;

            $totalGravada = 0;
            $totalIva = 0;

            if ($tipoDte === '01') {

                $retencion = (float) ($json['resumen']['ivaRete1'] ?? 0);

                if ($retencion > 0) {

                    // Caso con retención
                    $base = $totalDte + $retencion;

                    $totalGravada = round($base / 1.13, 2);
                    $totalIva     = round($totalGravada * 0.13, 2);
                } else {

                    // Caso sin retención
                    $totalGravada = round($totalDte / 1.13, 2);
                    $totalIva     = round($totalDte - $totalGravada, 2);
                }
            } else {

                // Otros DTE
                $totalGravada = (float) ($json['resumen']['totalGravada'] ?? 0);

                if (!empty($json['resumen']['tributos'])) {

                    foreach ($json['resumen']['tributos'] as $t) {

                        if (($t['codigo'] ?? null) == '20') {
                            $totalIva = (float) $t['valor'];
                        }
                    }
                }
            }

            $codigoRelacionado = null;

            if ($tipoDte === '05' && !empty($json['documentoRelacionado'][0]['numeroDocumento'])) {
                $codigoRelacionado = $json['documentoRelacionado'][0]['numeroDocumento'];
            }

            $clienteModel = new ClienteModel();
            $vendedorId = $sellerIds[$index] ?? null;
            $tipoVentaId = $tipoVentaIds[$index] ?? 1; // fallback Privados
            $plazo = $plazos[$index] ?? null;

            if (!$json) {
                continue;
            }
            // =============================
            // PROCESAR CLIENTE (RECEPTOR)
            // =============================
            $clienteId = null;

            if (!empty($json['receptor'])) {

                $receptor = $json['receptor'];

                $nrc = $receptor['nrc'] ?? null;

                // Detectar tipo y número automáticamente
                if (!empty($receptor['tipoDocumento']) && !empty($receptor['numDocumento'])) {

                    // Persona natural
                    $tipoDocumento   = $receptor['tipoDocumento'];
                    $numeroDocumento = $receptor['numDocumento'];
                } elseif (!empty($receptor['nit'])) {

                    // Empresa
                    $tipoDocumento   = '36'; // NIT
                    $numeroDocumento = $receptor['nit'];
                } else {

                    $tipoDocumento   = null;
                    $numeroDocumento = null;
                }

                if ($tipoDocumento && $numeroDocumento) {

                    // Buscar por tipo + número
                    $cliente = $clienteModel->buscarPorDocumento($tipoDocumento, $numeroDocumento);

                    // Si no existe y tiene NRC, buscar por NRC
                    if (!$cliente && $nrc) {
                        $cliente = $clienteModel->buscarPorNRC($nrc);
                    }

                    // Si no existe → crearlo
                    if (!$cliente) {

                        $clienteId = $clienteModel->insert([
                            'tipo_documento'   => $tipoDocumento,
                            'numero_documento' => $numeroDocumento,
                            'nrc'              => $nrc,
                            'nombre'           => $receptor['nombre'] ?? null,
                            'telefono'         => $receptor['telefono'] ?? null,
                            'correo'           => $receptor['correo'] ?? null,
                            'departamento'     => $receptor['direccion']['departamento'] ?? null,
                            'municipio'        => $receptor['direccion']['municipio'] ?? null,
                            'direccion'        => $receptor['direccion']['complemento'] ?? null,
                        ]);
                    } else {

                        $clienteId = $cliente->id;

                        // actualizar datos si vienen nuevos
                        $clienteModel->update($clienteId, [
                            'telefono' => $receptor['telefono'] ?? $cliente->telefono,
                            'correo'   => $receptor['correo'] ?? $cliente->correo,
                        ]);
                    }

                    if (!$clienteId && $cliente) {
                        $clienteId = $cliente->id;
                    }
                }
            }

            $condicionDte = isset($condiciones[$index])
                ? (int)$condiciones[$index]
                : 1;

            $fechaEmision = $json['identificacion']['fecEmi'] ?? null;

            $plazoCredito = null;

            /*
            ================================================
            SALDO INICIAL SEGÚN TIPO DE DOCUMENTO
            ================================================
            */

            if ($tipoDte === '05') {

                // Nota de Crédito no genera saldo
                $saldoInicial = 0;
            } else {

                // Facturas nacen con saldo completo
                $saldoInicial = $totalDte;
            }

            if ($condicionDte === 2) {
                $plazoCredito = is_numeric($plazo) ? (int)$plazo : 30;
            }

            // INSERTAR HEAD
            $dataHead = [
                'ambiente'          => $json['identificacion']['ambiente'] ?? null,
                'tipo_dte'          => $json['identificacion']['tipoDte'] ?? null,
                'numero_control'    => $json['identificacion']['numeroControl'] ?? null,
                'codigo_generacion' => $json['identificacion']['codigoGeneracion'] ?? null,
                'sello_recibido'    => $json['identificacion']['selloRecibido'] ?? null,
                'fecha_emision'     => $json['identificacion']['fecEmi'] ?? null,
                'hora_emision'      => $json['identificacion']['horEmi'] ?? null,
                'tipo_moneda'       => $json['identificacion']['tipoMoneda'] ?? null,
                'receptor_id'       => $clienteId,
                'vendedor_id'       => $vendedorId,
                'saldo'             => $saldoInicial,
                'iva_rete1' => $json['resumen']['ivaRete1'] ?? 0,

                'total_gravada' => $totalGravada,
                'total_iva'     => $totalIva,
                'sub_total'             => $json['resumen']['subTotal'] ?? 0,
                'monto_total_operacion' => $json['resumen']['montoTotalOperacion'] ?? 0,
                'total_pagar'           => $totalDte,
                'tipo_venta'            => $tipoVentaId,
                'condicion_operacion'   => $condicionDte,
                'plazo_credito'         => $plazoCredito,
                'codigo_generacion_relacionado' => $codigoRelacionado,
            ];

            log_message('error', json_encode($dataHead));
            $existe = $facturaHeadModel
                ->where('numero_control', $dataHead['numero_control'])
                ->first();

            if ($existe) {
                continue; // evita duplicados silenciosamente
            }

            if (!$facturaHeadModel->insert($dataHead)) {

                $db->transRollback();

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error insertando factura',
                    'errors'  => $facturaHeadModel->errors(),
                    'data'    => $dataHead
                ]);
            }

            $facturaId = $facturaHeadModel->getInsertID();

            if ($tipoDte === '05' && $codigoRelacionado) {
                $seProcesoNC = true;
                $facturaRelacionada = $facturaHeadModel
                    ->where('codigo_generacion', $codigoRelacionado)
                    ->first();

                if (!$facturaRelacionada) {

                    $db->transRollback();

                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'La Nota de Crédito hace referencia a un documento inexistente.'
                    ]);
                }

                $montoNC = (float)$totalDte;

                $saldoActual = (float)$facturaRelacionada->saldo;

                $nuevoSaldo = round($saldoActual - $montoNC, 2);

                $diferencia = abs($nuevoSaldo);

                // tolerancia de redondeo (3 centavos)
                $tolerancia = 0.03;

                if ($nuevoSaldo < 0) {

                    if ($diferencia <= $tolerancia) {

                        // ajuste permitido
                        $nuevoSaldo = 0;
                    } else {

                        $db->transRollback();

                        return $this->response->setJSON([
                            'success' => false,
                            'message' =>
                            'La Nota de Crédito excede el saldo de la factura por $' .
                                number_format($diferencia, 2) .
                                '. Solo se permite una tolerancia de $0.03 por redondeo.'
                        ]);
                    }
                }

                $facturaHeadModel->update(
                    $facturaRelacionada->id,
                    ['saldo' => $nuevoSaldo]
                );
                // Registrar en bitácora la Nota de Crédito aplicada
                registrar_bitacora(
                    'Aplicación de Nota de Crédito',
                    'Facturas',
                    'Se registró Nota de Crédito Nº ' . substr($dataHead['numero_control'], -6) .
                        ' por $' . number_format($montoNC, 2) .
                        ' aplicada a Factura Nº ' . substr($facturaRelacionada->numero_control, -6) .
                        '. Nuevo saldo: $' . number_format($nuevoSaldo, 2),
                    $user_id
                );
            }

            $facturasInsertadas++;
            $controles[] = substr($dataHead['numero_control'], -6);
            $totalOperacion += $dataHead['total_pagar'];

            if (!$facturaId) {
                continue;
            }

            // GUARDAR JSON ORIGINAL
            $facturaJsonModel->guardarJson(
                $facturaId,
                $contenido // guardamos el string original completo
            );

            // INSERTAR DETALLES
            if (!empty($json['cuerpoDocumento'])) {
                foreach ($json['cuerpoDocumento'] as $item) {

                    $tipoItem = $item['tipoItem'] ?? null;

                    $codigo = trim($item['codigo'] ?? '');

                    if ($tipoItem == 2) {
                        $codigo = 'SERV';
                    }

                    $cantidad = (float)($item['cantidad'] ?? 0);

                    // limpiar descripción (solo primera línea)
                    $descripcion = trim($item['descripcion'] ?? '');
                    $descripcion = strtok($descripcion, "\n");

                    /*
    ==============================
    BUSCAR PRODUCTO POR CODIGO
    ==============================
    */

                    $producto = null;
                    $productoId = null;

                    $producto = $productoModel
                        ->where('codigo', $codigo)
                        ->first();

                    if (!$producto) {

                        if (!$productoModel->insert([
                            'codigo' => $codigo,
                            'descripcion' => $tipoItem == 2 ? 'Servicio' : $descripcion
                        ])) {

                            return $this->response->setJSON([
                                'success' => false,
                                'message' => 'Error creando producto',
                                'errors' => $productoModel->errors(),
                                'codigo' => $codigo
                            ]);
                        }

                        $productoId = $productoModel->getInsertID();
                    } else {

                        $productoId = $producto->id;
                    }

                    if (!$productoId) {

                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'No se pudo determinar el producto_id',
                            'codigo' => $codigo,
                            'descripcion' => $descripcion
                        ]);
                    }

                    /*
    ==============================
    INSERTAR DETALLE
    ==============================
    */

                    $ventaGravada = (float) ($item['ventaGravada'] ?? 0);
                    $ivaItem = (float) ($item['ivaItem'] ?? 0);

                    /*
================================================
SEPARAR IVA PARA FACTURA CONSUMIDOR FINAL (01)
================================================
*/

                    if ($tipoDte === '01') {

                        // ventaGravada viene CON IVA
                        $base = round($ventaGravada / 1.13, 2);
                        $iva  = round($ventaGravada - $base, 2);

                        $ventaGravada = $base;
                        $ivaItem = $iva;
                    }

                    /*
================================================
CCF YA VIENE SIN IVA
================================================
*/

                    $detalleData = [
                        'factura_id'      => $facturaId,
                        'producto_id'     => $productoId,
                        'num_item'        => $item['numItem'] ?? null,
                        'tipo_item'       => $item['tipoItem'] ?? null,
                        'codigo'          => $codigo,
                        'descripcion'     => $descripcion,
                        'cantidad'        => $cantidad,
                        'unidad_medida'   => $item['uniMedida'] ?? null,
                        'precio_unitario' => $item['precioUni'] ?? 0,
                        'monto_descuento' => $item['montoDescu'] ?? 0,
                        'venta_no_sujeta' => $item['ventaNoSuj'] ?? 0,
                        'venta_exenta'    => $item['ventaExenta'] ?? 0,
                        'venta_gravada'   => $ventaGravada,
                        'iva_item'        => $ivaItem,
                    ];

                    if (!$facturaDetalleModel->insert($detalleData)) {

                        $dbError = $facturaDetalleModel->db->error();

                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Error insertando detalle',
                            'model_errors' => $facturaDetalleModel->errors(),
                            'db_error' => $dbError,
                            'data' => $detalleData
                        ]);
                    }

                    /*
    ==============================
    MOVIMIENTO INVENTARIO
    ==============================
    */

                    if ($cantidad > 0 && $tipoItem == 1) {

                        $movimientoModel->insert([
                            'producto_id' => $productoId,
                            'tipo_movimiento' => 'venta',
                            'cantidad' => -abs($cantidad),
                            'referencia_tipo' => 'factura',
                            'referencia_id' => $facturaId
                        ]);
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {

            $error = $db->error();

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en base de datos',
                'code' => $error['code'] ?? null,
                'error' => $error['message'] ?? 'Error desconocido'
            ]);
        }

        // Registrar bitácora
        if (!$seProcesoNC) {
            registrar_bitacora(
                'Carga masiva de facturas',
                'Facturas',
                sprintf(
                    'Cargó %d factura(s) desde archivos JSON. Total procesado: $%s. Controles: %s',
                    $facturasInsertadas,
                    number_format($totalOperacion, 2),
                    implode(', ', $controles)
                ),
                $user_id
            );
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Facturas procesadas correctamente'
        ]);
    }

    public function detalle($id)
    {
        $facturaHeadModel    = new FacturaHeadModel();
        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();

        // Cabecera
        $factura = $facturaHeadModel
            ->select('facturas_head.*, clientes.nombre AS cliente, sellers.seller AS vendedor, tipo_venta.nombre_tipo_venta AS tipo_venta_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta', 'tipo_venta.id = facturas_head.tipo_venta', 'left')
            ->where('facturas_head.id', $id)
            ->first();

        if (!$factura) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Detalles
        $detalles = $facturaDetalleModel
            ->where('factura_id', $id)
            ->findAll();

        // Traer fecha del último pago aplicado a esta factura
        $db = \Config\Database::connect();

        $ultimoPago = $db->table('pagos_details pd')
            ->select('ph.fecha_pago')
            ->join('pagos_head ph', 'ph.id = pd.pago_id')
            ->where('pd.factura_id', $id)
            ->where('pd.anulado', 0)
            ->orderBy('ph.fecha_pago', 'DESC')
            ->get()
            ->getRow();

        $factura->fecha_ultimo_pago = $ultimoPago->fecha_pago ?? null;

        $facturaRelacionada = null;

        $notasCredito = $facturaHeadModel
            ->where('tipo_dte', '05')
            ->where('codigo_generacion_relacionado', $factura->codigo_generacion)
            ->orderBy("CAST(SUBSTRING(numero_control, -6) AS UNSIGNED)", 'ASC', false)
            ->findAll();

        if (!empty($factura->codigo_generacion_relacionado)) {

            $facturaRelacionada = $facturaHeadModel
                ->where('codigo_generacion', $factura->codigo_generacion_relacionado)
                ->first();
        }

        $pagoDetalleModel = new PagosDetailsModel();

        $pagos = $pagoDetalleModel
            ->select('
                pagos_details.monto,
                pagos_details.pago_id,
                pagos_details.anulado,
                pagos_head.fecha_pago,
                pagos_head.forma_pago,
                pagos_head.anulado AS pago_anulado
            ')
            ->join('pagos_head', 'pagos_head.id = pagos_details.pago_id')
            ->where('pagos_details.factura_id', $id)
            ->orderBy('pagos_head.fecha_pago', 'ASC')
            ->findAll();

        return view('facturas/detalle', [
            'factura' => $factura,
            'detalles' => $detalles,
            'facturaRelacionada' => $facturaRelacionada,
            'notasCredito' => $notasCredito,
            'pagos' => $pagos
        ]);
    }

    public function validarNumeroControl()
    {
        $numeroControl = $this->request->getPost('numero_control');

        if (!$numeroControl) {
            return $this->response->setJSON([
                'existe' => false
            ]);
        }

        $facturaHeadModel = new FacturaHeadModel();

        $existe = $facturaHeadModel
            ->where('numero_control', $numeroControl)
            ->first();

        return $this->response->setJSON([
            'existe' => $existe ? true : false
        ]);
    }
    public function anular($id)
    {
        if (!tienePermiso('anular_factura')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para anular facturas.'
            ]);
        }

        $user_id = session()->get('user_id');

        $facturaModel       = new FacturaHeadModel();
        $pagosDetailsModel  = new PagosDetailsModel();
        $pagosHeadModel     = new PagosHeadModel();
        $transactionModel   = new TransactionModel();
        $accountModel       = new AccountModel();

        $factura = $facturaModel->find($id);

        if (!$factura) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Factura no encontrada.'
            ]);
        }

        if ($factura->anulada == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La factura ya está anulada.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1️⃣ Anular factura
        $facturaModel->update($id, [
            'anulada' => 1,
            'saldo'   => 0
        ]);

        // 2️⃣ Obtener detalles de pagos activos
        $detalles = $pagosDetailsModel
            ->where('factura_id', $id)
            ->where('anulado', 0)
            ->findAll();

        foreach ($detalles as $detalle) {

            $montoDevuelto = $detalle->monto;
            $pagoId        = $detalle->pago_id;

            $pago = $pagosHeadModel->find($pagoId);
            if (!$pago) continue;

            $accountId = $pago->numero_cuenta_bancaria; // es el id real

            // 🔹 1. Obtener cuenta actual
            $cuenta = $accountModel->find($accountId);
            if (!$cuenta) continue;

            // 🔹 2. Restar el monto al balance actual
            $nuevoBalance = $cuenta->balance - $montoDevuelto;

            // 🔹 3. Actualizar balance en accounts
            $accountModel->update($accountId, [
                'balance' => $nuevoBalance
            ]);

            // 🔹 4. Registrar transacción
            $transactionModel->addSalida(
                $accountId,
                $montoDevuelto,
                'Reversión por anulación de factura',
                'Factura Nº ' . substr($factura->numero_control, -6),
                $pagoId
            );

            // 🔹 5. Anular detalle
            $pagosDetailsModel->update($detalle->id, [
                'anulado'    => 1,
                'anulado_at' => date('Y-m-d H:i:s'),
                'anulado_by' => $user_id
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al anular la factura.'
            ]);
        }

        // Bitácora
        registrar_bitacora(
            'Anulación de factura',
            'Facturas',
            'Anuló factura Nº ' . substr($factura->numero_control, -6) .
                ' por monto $' . number_format($factura->total_pagar, 2),
            $user_id
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Factura anulada correctamente y saldo reintegrado.'
        ]);
    }

    public function preview($id)
    {
        $facturaHeadModel    = new FacturaHeadModel();
        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();
        $pagosDetalleModel   = new PagosDetailsModel();

        $factura = $facturaHeadModel
            ->select('facturas_head.*,
              clientes.nombre AS cliente,
              sellers.seller AS vendedor,
              tipo_venta.nombre_tipo_venta AS tipo_venta_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta', 'tipo_venta.id = facturas_head.tipo_venta', 'left')
            ->where('facturas_head.id', $id)
            ->first();

        if (!$factura) return 'Factura no encontrada';

        $detalles = $facturaDetalleModel
            ->where('factura_id', $id)
            ->findAll();

        // TRAER PAGOS APLICADOS A ESTA FACTURA
        $pagos = $pagosDetalleModel
            ->distinct()
            ->select('pd.monto,
                pd.observaciones,
                ph.fecha_pago,
                ph.forma_pago,
                ph.id as pago_id')
            ->from('pagos_details as pd')
            ->join('pagos_head as ph', 'ph.id = pd.pago_id')
            ->where('pd.factura_id', $id)
            ->orderBy('ph.fecha_pago', 'ASC')
            ->findAll();

        return view('facturas/_preview_modal', [
            'factura'  => $factura,
            'detalles' => $detalles,
            'pagos'    => $pagos
        ]);
    }
    public function checkPagos($facturaId)
    {
        $detalleModel = new PagosDetailsModel();
        $pagoHeadModel = new PagosHeadModel();

        $pagos = $detalleModel
            ->select('
            pagos_details.monto,
            pagos_head.id as pago_id,
            pagos_head.fecha_pago,
            pagos_head.forma_pago
        ')
            ->join('pagos_head', 'pagos_head.id = pagos_details.pago_id')
            ->where('pagos_details.factura_id', $facturaId)
            ->where('pagos_details.anulado', 0)
            ->where('pagos_head.anulado', 0)
            ->findAll();

        if (empty($pagos)) {
            return $this->response->setJSON([
                'tiene_pagos' => false
            ]);
        }

        $totalPagado = 0;
        foreach ($pagos as $p) {
            $totalPagado += $p->monto;
        }

        return $this->response->setJSON([
            'tiene_pagos' => true,
            'total_pagado' => number_format($totalPagado, 2),
            'pagos' => $pagos
        ]);
    }
    public function validarDocumentoRelacionado()
    {
        $codigo = $this->request->getPost('codigo_generacion');

        if (!$codigo) {
            return $this->response->setJSON([
                'existe' => false
            ]);
        }

        $model = new FacturaHeadModel();

        $factura = $model
            ->select('id, numero_control, saldo, total_pagar')
            ->where('codigo_generacion', $codigo)
            ->where('anulada', 0)
            ->first();

        if (!$factura) {
            return $this->response->setJSON([
                'existe' => false
            ]);
        }

        return $this->response->setJSON([
            'existe' => true,
            'id' => $factura->id,
            'numero_control' => $factura->numero_control,
            'saldo' => (float)$factura->saldo,
            'total' => (float)$factura->total_pagar
        ]);
    }
    public function cambiarVendedor()
    {
        if (!tienePermiso('editar_vendedor_en_detalle')) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tiene permisos.'
            ]);
        }

        $data = $this->request->getJSON(true);

        $facturaId  = $data['factura_id'] ?? null;
        $vendedorId = $data['vendedor_id'] ?? null;

        if (!$facturaId || !$vendedorId) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos.'
            ]);
        }

        $facturaModel = new FacturaHeadModel();

        $factura = $facturaModel->find($facturaId);

        if (!$factura) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Factura no encontrada.'
            ]);
        }

        $vendedorModel = new \App\Models\SellerModel();
        $vendedor = $vendedorModel->find($vendedorId);

        if (!$vendedor) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vendedor inválido.'
            ]);
        }

        $facturaModel->update($facturaId, [
            'vendedor_id' => $vendedorId
        ]);

        registrar_bitacora(
            'Cambio de vendedor en factura',
            'Facturas',
            'Se cambió el vendedor de la factura Nº ' .
                substr($factura->numero_control, -6) .
                ' a ' . $vendedor->seller,
            session()->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Vendedor actualizado correctamente.'
        ]);
    }
}
