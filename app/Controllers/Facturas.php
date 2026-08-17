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
use App\Models\EmisorModel;
use App\Models\SettingModel;
use App\Services\DteSignerService;
use App\Services\HaciendaApiService;

class Facturas extends BaseController
{
    private function consultaHaciendaUrl(object $factura): string
    {
        if (empty($factura->codigo_generacion) || empty($factura->fecha_emision)) {
            return '';
        }

        return 'https://admin.factura.gob.sv/consultaPublica?' . http_build_query([
            'ambiente' => $factura->ambiente ?: '01',
            'codGen'   => $factura->codigo_generacion,
            'fechaEmi' => $factura->fecha_emision,
        ]);
    }

    private function qrDataUri(string $url, int $scale = 5): ?string
    {
        if ($url === '' || !class_exists('\TCPDF2DBarcode')) {
            return null;
        }

        try {
            $qr = new \TCPDF2DBarcode($url, 'QRCODE,H');
            return 'data:image/png;base64,' . base64_encode($qr->getBarcodePngData($scale, $scale));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function logoDataUri(): string
    {
        $setting = (new SettingModel())->find(1);
        $logo = $setting->logo ?? null;
        $path = $logo ? FCPATH . 'upload/settings/' . $logo : FCPATH . 'img/logo.jpg';

        if (!$path || !is_file($path)) {
            $path = FCPATH . 'img/logo.jpg';
        }

        if (!is_file($path)) {
            return '';
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    private function facturaConReceptor(int $id): ?object
    {
        return (new FacturaHeadModel())
            ->select('facturas_head.*,
                clientes.nombre AS cliente,
                clientes.tipo_documento AS cliente_tipo_documento,
                clientes.numero_documento AS cliente_documento,
                clientes.nrc AS cliente_nrc,
                clientes.telefono AS cliente_telefono,
                clientes.correo AS cliente_correo,
                clientes.direccion AS cliente_direccion,
                sellers.seller AS vendedor,
                tipo_venta.nombre_tipo_venta AS tipo_venta_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta', 'tipo_venta.id = facturas_head.tipo_venta', 'left')
            ->where('facturas_head.id', $id)
            ->first();
    }

    private function puedeAccederFactura(object $factura): bool
    {
        if (puedeVerDocumentosTodosVendedores()) {
            return true;
        }

        $sellerScope = vendedorUsuarioActual();
        return $sellerScope && (int)$factura->vendedor_id === (int)$sellerScope;
    }

    private function prefijoArchivoDte(?string $tipoDte): string
    {
        $prefijosArchivo = [
            '01' => 'factura',
            '03' => 'credito_fiscal',
            '04' => 'nota_remision',
            '05' => 'nota_credito',
            '06' => 'nota_debito',
            '07' => 'comprobante_retencion',
            '08' => 'comprobante_liquidacion',
            '09' => 'documento_liquidacion',
            '11' => 'factura_exportacion',
            '14' => 'sujeto_excluido',
            '15' => 'comprobante_donacion',
        ];

        return $prefijosArchivo[$tipoDte] ?? 'documento';
    }

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

        if (!puedeVerDocumentosTodosVendedores()) {
            $model->where('facturas_head.vendedor_id', vendedorUsuarioActual() ?? -1);
        } elseif (is_numeric($sellerId)) {
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
        $emisor = $emisorModel->first();

        $configModel = new \App\Models\ContConfiguracionModel();
        $contConfig  = (array)$configModel->getConfig();

        return view('facturas/carga_procesado', [
            'emisor'     => $emisor,
            'contConfig' => $contConfig,
        ]);
    }

    public function procesarCarga()
    {
        $user_id = session()->get('user_id');
        session_write_close();

        $files = $this->request->getFiles();
        $tipoVentaIds         = $this->request->getPost('tipo_venta_ids');
        $sellerIds            = $this->request->getPost('seller_ids');
        $plazos               = $this->request->getPost('plazos_credito');
        $condiciones          = $this->request->getPost('condiciones');
        $tipoLineas           = $this->request->getPost('tipo_lineas')            ?? [];
        $cuentaVentasOverrides = $this->request->getPost('cuenta_ventas_override_ids') ?? [];
        $clienteIdsManual      = $this->request->getPost('cliente_ids')               ?? [];

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
        $asientosQueue = [];

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
            } elseif ($tipoDte === '14') {

                // Factura de sujeto excluido usa campos de compra, no venta.
                $totalGravada = (float) (
                    $json['resumen']['totalCompra']
                    ?? $json['resumen']['subTotal']
                    ?? $totalDte
                );
                $totalIva = 0;
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
            // PROCESAR CLIENTE (RECEPTOR / SUJETO EXCLUIDO)
            // =============================
            $clienteId       = null;
            $receptor        = $json['receptor'] ?? $json['sujetoExcluido'] ?? null;
            $clienteIdManual = !empty($clienteIdsManual[$index]) ? (int)$clienteIdsManual[$index] : null;

            if (!empty($receptor)) {

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

                // El documento/NRC coincidía con más de un cliente ya registrado
                // (p.ej. sucursales de la misma empresa) y el usuario ya eligió
                // manualmente cuál usar en la previsualización: se respeta esa
                // elección tal cual, sin volver a correr la búsqueda ambigua.
                if ($clienteIdManual) {

                    $cliente = $clienteModel->find($clienteIdManual);

                    if ($cliente) {
                        $clienteId = $cliente->id;

                        $clienteModel->update($clienteId, [
                            'telefono'       => $receptor['telefono'] ?? $cliente->telefono,
                            'correo'         => $receptor['correo'] ?? $cliente->correo,
                            'cod_actividad'  => $receptor['codActividad'] ?? $cliente->cod_actividad ?? null,
                            'desc_actividad' => $receptor['descActividad'] ?? $cliente->desc_actividad ?? null,
                        ]);
                    }
                } elseif ($tipoDocumento && $numeroDocumento) {

                    // Buscar por tipo + número
                    $cliente = $clienteModel->buscarPorDocumento($tipoDocumento, $numeroDocumento);

                    // Si no existe y tiene NRC, buscar por NRC
                    if (!$cliente && $nrc) {
                        $cliente = $clienteModel->buscarPorNRC($nrc);
                    }

                    // Si no existe → crearlo
                    if (!$cliente) {

                        $clienteId = $clienteModel->insert([
                            'tipo_documento'   => ClienteModel::normalizarTipoDocumento($tipoDocumento),
                            'numero_documento' => $numeroDocumento,
                            'nrc'              => $nrc,
                            'cod_actividad'    => $receptor['codActividad'] ?? null,
                            'desc_actividad'   => $receptor['descActividad'] ?? null,
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
                            'telefono'       => $receptor['telefono'] ?? $cliente->telefono,
                            'correo'         => $receptor['correo'] ?? $cliente->correo,
                            'cod_actividad'  => $receptor['codActividad'] ?? $cliente->cod_actividad ?? null,
                            'desc_actividad' => $receptor['descActividad'] ?? $cliente->desc_actividad ?? null,
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
                'monto_total_operacion' => $json['resumen']['montoTotalOperacion'] ?? $totalDte,
                'total_pagar'           => $totalDte,
                'tipo_venta'            => $tipoVentaId,
                'condicion_operacion'   => $condicionDte,
                'plazo_credito'         => $plazoCredito,
                'codigo_generacion_relacionado' => $codigoRelacionado,
                'observaciones'         => $json['observaciones'] ?? null,
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

                        $tipoProducto = (int) $tipoItem;
                        if (!$productoModel->insert([
                            'codigo' => $codigo,
                            'descripcion' => $tipoItem == 2 ? 'Servicio' : $descripcion,
                            'tipo' => $tipoProducto
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

                    $ventaGravada = (float) ($item['ventaGravada'] ?? $item['compra'] ?? 0);
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

                        $esNC = ($tipoDte === '05');

                        $movimientoModel->insert([
                            'producto_id'     => $productoId,
                            'tipo_movimiento' => $esNC ? 'devolucion' : 'venta',
                            'cantidad'        => $esNC ? abs($cantidad) : -abs($cantidad),
                            'referencia_tipo' => 'factura',
                            'referencia_id'   => $facturaId
                        ]);
                    }
                }
            }

            // Encolar asiento contable para CCF y FAC (se procesa después de commit)
            if (in_array($tipoDte, ['01', '03'])) {
                $asientosQueue[] = [
                    'tipoDte'          => $tipoDte,
                    'totalGravada'     => $totalGravada,
                    'montoTotalOp'     => (float)($json['resumen']['montoTotalOperacion'] ?? $totalDte),
                    'retencion'        => (float)($json['resumen']['ivaRete1'] ?? 0),
                    'fechaEmision'     => $json['identificacion']['fecEmi'] ?? date('Y-m-d'),
                    'numeroControl'    => $dataHead['numero_control'],
                    'facturaId'        => $facturaId,
                    'clienteId'        => $clienteId ?? null,
                    'tipoLinea'        => $tipoLineas[$index] ?? 'producto',
                    'cuentaOverrideId' => (($tipoLineas[$index] ?? '') === 'servicio' && !empty($cuentaVentasOverrides[$index]))
                        ? (int)$cuentaVentasOverrides[$index] : null,
                ];
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

        // Crear asientos contables automáticos (CCF y FAC únicamente)
        $asientosCreados  = 0;
        $asientosOmitidos = [];

        if (!empty($asientosQueue)) {
            helper('cont_ventas');
            $periodosModel    = new \App\Models\ContPeriodosModel();
            $contHeadModel    = new \App\Models\ContAsientosHeadModel();
            $contDetalleModel = new \App\Models\ContAsientosDetalleModel();

            foreach ($asientosQueue as $item) {
                $ref = substr($item['numeroControl'], -6);

                // Obtain (or auto-create) the period matching the invoice date
                $anioItem      = (int)substr($item['fechaEmision'], 0, 4);
                $mesItem       = (int)substr($item['fechaEmision'], 5, 2);
                $periodoActual = $periodosModel->abrirObtenerPeriodo($anioItem, $mesItem);

                if (!$periodoActual) {
                    $asientosOmitidos[] = "{$ref}: período {$mesItem}/{$anioItem} está cerrado, no se puede reabrir automáticamente";
                    continue;
                }

                $tipoDteHelper = $item['tipoDte'] === '03' ? 'CCF' : 'FAC';
                $monto = $item['tipoDte'] === '03' ? (float)$item['totalGravada'] : (float)$item['montoTotalOp'];

                if ($monto <= 0) {
                    $asientosOmitidos[] = "{$ref}: monto inválido ({$monto})";
                    continue;
                }

                // Usar la subcuenta propia del cliente; si no tiene, crearla automáticamente
                $cxcOverrideId = null;
                if (!empty($item['clienteId'])) {
                    $clienteModel = new \App\Models\ClienteModel();
                    $cli = $clienteModel->select('id, nombre, cuenta_contable_id')->find((int)$item['clienteId']);

                    if ($cli && !empty($cli->cuenta_contable_id)) {
                        $cxcOverrideId = (int)$cli->cuenta_contable_id;
                    } elseif ($cli) {
                        // Auto-crear subcuenta bajo 110201 CLIENTES LOCALES
                        $planModel = new \App\Models\ContPlanCuentasModel();
                        $padre     = $planModel->where('codigo', '110201')->first();

                        if ($padre) {
                            // 1. Buscar subcuenta existente por nombre bajo 110201xxxx
                            $subcuentaExistente = $planModel
                                ->like('codigo', '110201', 'after')
                                ->where('nombre', mb_strtoupper($cli->nombre))
                                ->first();

                            if ($subcuentaExistente) {
                                $clienteModel->update($cli->id, ['cuenta_contable_id' => $subcuentaExistente->id]);
                                $cxcOverrideId = (int)$subcuentaExistente->id;
                            } else {
                                // 2. Código seguro: MAX sobre TODOS los códigos 110201xxxx
                                //    (independiente de cuenta_padre_id para evitar duplicados)
                                $dbRaw     = \Config\Database::connect();
                                $siguiente = (int)($dbRaw->query(
                                    "SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 7) AS UNSIGNED)), 0) + 1
                                     AS sig FROM cont_plan_cuentas
                                     WHERE codigo LIKE '110201%' AND LENGTH(codigo) > 6"
                                )->getRow()->sig ?? 1);

                                $nuevoCodigo = '110201' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);

                                $nuevaCuentaId = $planModel->insert([
                                    'codigo'             => $nuevoCodigo,
                                    'nombre'             => mb_strtoupper($cli->nombre),
                                    'tipo'               => $padre->tipo,
                                    'naturaleza'         => $padre->naturaleza,
                                    'nivel'              => $padre->nivel + 1,
                                    'cuenta_padre_id'    => $padre->id,
                                    'acepta_movimientos' => 1,
                                    'activo'             => 1,
                                ]);

                                if ($nuevaCuentaId) {
                                    $clienteModel->update($cli->id, ['cuenta_contable_id' => $nuevaCuentaId]);
                                    $cxcOverrideId = (int)$nuevaCuentaId;
                                }
                            }
                        }
                    }
                }

                try {
                    $resultado = cont_asiento_venta_json(
                        $tipoDteHelper,
                        $monto,
                        (float)$item['retencion'],
                        $ref,
                        $periodoActual->id,
                        $item['fechaEmision'],
                        "Venta {$tipoDteHelper} {$ref}",
                        $item['cuentaOverrideId'],
                        $cxcOverrideId
                    );
                } catch (\InvalidArgumentException $e) {
                    $asientosOmitidos[] = "{$ref}: " . $e->getMessage();
                    continue;
                }

                if (!$resultado['ok']) {
                    $asientosOmitidos[] = "{$ref}: " . implode(', ', $resultado['errores']);
                    continue;
                }

                $payload       = $resultado['payload'];
                $fechaAsiento  = $payload['fecha'];
                $tipoPartidaId = $payload['tipo_partida_id'] ?? null;
                $totalDebe     = round(array_sum(array_column($payload['lineas'], 'debe')),  2);
                $totalHaber    = round(array_sum(array_column($payload['lineas'], 'haber')), 2);

                // Consolidar: buscar partida del mismo día y tipo
                $existing = $tipoPartidaId
                    ? $contHeadModel->buscarPartidaDia($tipoPartidaId, $fechaAsiento)
                    : null;

                if ($existing) {
                    // Añadir líneas a la partida existente del día
                    $dbRaw    = \Config\Database::connect();
                    $maxOrden = (int)($dbRaw->query(
                        'SELECT COALESCE(MAX(orden), 0) AS m FROM cont_asientos_detalle WHERE asiento_id = ?',
                        [$existing->id]
                    )->getRow()->m ?? 0);

                    foreach ($payload['lineas'] as $i => $l) {
                        $contDetalleModel->insert([
                            'asiento_id'  => $existing->id,
                            'cuenta_id'   => $l['cuenta_id'],
                            'descripcion' => $l['descripcion'],
                            'debe'        => $l['debe'],
                            'haber'       => $l['haber'],
                            'orden'       => $maxOrden + $i + 1,
                        ]);
                    }

                    $contHeadModel->update($existing->id, [
                        'total_debe'  => round($existing->total_debe  + $totalDebe,  2),
                        'total_haber' => round($existing->total_haber + $totalHaber, 2),
                    ]);

                    $asientoId = $existing->id;
                } else {
                    // Nueva partida para este día
                    $anioFecha  = (int)substr($fechaAsiento, 0, 4);
                    $numPartida = $tipoPartidaId
                        ? $contHeadModel->getSiguienteNumeroPartida($tipoPartidaId, $anioFecha)
                        : null;

                    $asientoId = $contHeadModel->insert([
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
                        'documento_id'       => $item['facturaId'] ?? null,
                        'usuario_id'         => $user_id,
                        'usuario_aprueba_id' => $user_id,
                        'fecha_aprobacion'   => date('Y-m-d H:i:s'),
                    ]);

                    if (!$asientoId) {
                        $asientosOmitidos[] = "{$ref}: error al insertar encabezado de asiento";
                        continue;
                    }

                    foreach ($payload['lineas'] as $i => $l) {
                        $contDetalleModel->insert([
                            'asiento_id'  => $asientoId,
                            'cuenta_id'   => $l['cuenta_id'],
                            'descripcion' => $l['descripcion'],
                            'debe'        => $l['debe'],
                            'haber'       => $l['haber'],
                            'orden'       => $i + 1,
                        ]);
                    }

                    $asientosCreados++;
                }

                $contHeadModel->aprobarConSaldos(
                    $asientoId,
                    $payload['lineas'],
                    (int)$payload['periodo_id'],
                    $fechaAsiento,
                    $payload['descripcion'],
                    $payload['tipo'],
                    $periodoActual
                );
            }
        }

        $mensaje = 'Facturas procesadas correctamente';
        if ($asientosCreados > 0) {
            $mensaje .= ". {$asientosCreados} asiento(s) contable(s) generado(s).";
        }
        if (!empty($asientosOmitidos)) {
            $mensaje .= ' Omitidos: ' . implode(' | ', $asientosOmitidos);
        }

        return $this->response->setJSON([
            'success'          => true,
            'message'          => $mensaje,
            'asientos_creados' => $asientosCreados,
            'asientos_omitidos'=> $asientosOmitidos,
        ]);
    }

    // =========================================================
    // CARGA MANUAL (Facturas tradicionales pre-electrónicas)
    // =========================================================

    public function cargaManual()
    {
        $chk = requerirPermiso('cargar_facturas_manual');
        if ($chk !== true) return $chk;

        return view('facturas/carga_manual');
    }

    // ── Inyecta datos en la hoja Datos del XLSM usando ZipArchive ──────────
    // No usa PhpSpreadsheet para escribir — manipula el ZIP directamente
    // para preservar el vbaProject.bin intacto.
    private function _servirXlsmConDatos(string $path, array $clientes, array $productos): void
    {
        // Copiar a temporal para no modificar el original
        $temp = tempnam(sys_get_temp_dir(), 'xlsm_') . '.xlsm';
        copy($path, $temp);

        $zip = new \ZipArchive();
        if ($zip->open($temp) !== true) {
            // Si no se puede abrir, servir el original directamente
            $this->_servirArchivoDirecto($path);
            @unlink($temp);
            return;
        }

        // Encontrar la hoja "Datos" en el workbook
        $datosSheetFile = $this->_encontrarHojaEnZip($zip, 'Datos');

        if ($datosSheetFile) {
            $xml = $this->_construirXmlDatos($clientes, $productos);
            $zip->addFromString($datosSheetFile, $xml);
        }

        $zip->close();

        $filename = 'plantilla_facturas_' . date('Y-m-d') . '.xlsm';

        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/vnd.ms-excel.sheet.macroEnabled.12');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($temp));
        readfile($temp);
        @unlink($temp);
        exit;
    }

    // ── Localiza el archivo XML de una hoja por su nombre ────────────────────
    private function _encontrarHojaEnZip(\ZipArchive $zip, string $sheetName): ?string
    {
        $wbXmlRaw = $zip->getFromName('xl/workbook.xml');
        if (!$wbXmlRaw) return null;

        $wbXml = simplexml_load_string($wbXmlRaw);
        if (!$wbXml) return null;

        $ns  = $wbXml->getNamespaces(true);
        $rNs = $ns['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        $rId = null;
        foreach ($wbXml->sheets->sheet ?? [] as $sheet) {
            $attrs = $sheet->attributes();
            if (strcasecmp((string)($attrs['name'] ?? ''), $sheetName) === 0) {
                $rAttrs = $sheet->attributes($rNs);
                $rId    = (string)($rAttrs['id'] ?? '');
                break;
            }
        }
        if (!$rId) return null;

        $relsRaw = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if (!$relsRaw) return null;

        $rels = simplexml_load_string($relsRaw);
        if (!$rels) return null;

        foreach ($rels->Relationship ?? [] as $rel) {
            if ((string)($rel['Id'] ?? '') === $rId) {
                $target = (string)($rel['Target'] ?? '');
                return strpos($target, '/') === 0 ? ltrim($target, '/') : 'xl/' . $target;
            }
        }
        return null;
    }

    // ── Construye el XML de la hoja Datos con datos frescos ──────────────────
    private function _construirXmlDatos(array $clientes, array $productos): string
    {
        $x = fn(string $s): string => htmlspecialchars(
            mb_convert_encoding($s, 'UTF-8', 'UTF-8'),
            ENT_XML1 | ENT_QUOTES,
            'UTF-8'
        );

        $cell = function(string $ref, string $val, bool $numeric = false) use ($x): string {
            if ($val === '' || $val === null) return '';
            if ($numeric) return '<c r="'.$ref.'"><v>'.((float)$val).'</v></c>';
            return '<c r="'.$ref.'" t="inlineStr"><is><t>'.$x($val).'</t></is></c>';
        };

        $rows = '';

        // Fila 1: aviso
        $rows .= '<row r="1">'
            . $cell('A1', 'NO EDITAR — Generado automaticamente desde la base de datos')
            . '</row>';

        // Fila 2: sección headers
        $rows .= '<row r="2">'
            . $cell('A2', 'CLIENTES')
            . $cell('D2', 'PRODUCTOS')
            . '</row>';

        // Fila 3: columnas headers
        // Clientes: A=Nombre, B=NIT/DUI, C=Telefono, D=Flag(NUEVO/vacio)
        // Productos: F=Codigo, G=Descripcion, H=Precio  (E es separador)
        $rows .= '<row r="3">'
            . $cell('A3', 'Nombre')
            . $cell('B3', 'NIT/DUI')
            . $cell('C3', 'Telefono')
            . $cell('D3', 'Estado')
            . $cell('F3', 'Codigo')
            . $cell('G3', 'Descripcion')
            . $cell('H3', 'Precio Unit.')
            . '</row>';

        $maxRows = max(count($clientes), count($productos));
        for ($i = 0; $i < $maxRows; $i++) {
            $r   = $i + 4;
            $row = '<row r="' . $r . '">';

            if (isset($clientes[$i])) {
                $cli  = $clientes[$i];
                $row .= $cell('A'.$r, $cli->nombre ?? '');
                $row .= $cell('B'.$r, $cli->nrc ?? $cli->numero_documento ?? '');
                $row .= $cell('C'.$r, $cli->telefono ?? '');
                // D = vacío para clientes existentes; VBA pone "NUEVO" para los nuevos
            }

            if (isset($productos[$i])) {
                $prod = $productos[$i];
                $row .= $cell('F'.$r, $prod->codigo ?? '');
                $row .= $cell('G'.$r, $prod->descripcion ?? '');
                $row .= $cell('H'.$r, (string)((float)($prod->precio_minimo ?? 0)), true);
            }

            $rows .= $row . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetData>' . $rows . '</sheetData>'
            . '</worksheet>';
    }

    // ── Fallback: sirve el archivo base directamente sin modificar ────────────
    private function _servirArchivoDirecto(string $path): void
    {
        $filename = 'plantilla_facturas_' . date('Y-m-d') . '.xlsm';
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/vnd.ms-excel.sheet.macroEnabled.12');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    // ── Ejecuta el script PS que genera la plantilla macro ───────────────────
    public function crearPlantillaMacro()
    {
        $chk = requerirPermiso('cargar_facturas_manual');
        if ($chk !== true) return $chk;

        $ps  = WRITEPATH . 'templates/crear_plantilla_macro.ps1';
        $out = WRITEPATH . 'templates/plantilla_base.xlsm';

        if (!file_exists($ps)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Script no encontrado: ' . $ps]);
        }

        $cmd    = 'powershell.exe -NonInteractive -ExecutionPolicy Bypass -File "' . $ps . '" 2>&1';
        $result = shell_exec($cmd) ?? '';

        // PowerShell en Windows devuelve CP850/Windows-1252 — convertir a UTF-8
        if (!mb_check_encoding($result, 'UTF-8')) {
            $result = mb_convert_encoding($result, 'UTF-8', 'Windows-1252');
        }
        // Quitar cualquier carácter residual no imprimible
        $result = preg_replace('/[^\x09\x0A\x0D\x20-\x{FFFD}]/u', '', $result);

        if (file_exists($out)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Plantilla macro creada correctamente.',
                'output'  => trim($result),
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'El script finalizó pero no se generó el archivo. Verifica que Excel esté instalado en el servidor.',
            'output'  => trim($result),
        ]);
    }

    public function descargarPlantilla()
    {
        $chk = requerirPermiso('cargar_facturas_manual');
        if ($chk !== true) return $chk;

        $clienteModel  = new ClienteModel();
        $productoModel = new ProductoModel();

        $clientes  = $clienteModel->orderBy('nombre')->findAll();
        $productos = $productoModel->where('activo', 1)->orderBy('descripcion')->findAll();

        // ── Si existe la plantilla macro (.xlsm), úsala como base ─────────────
        $baseXlsm = WRITEPATH . 'templates/plantilla_base.xlsm';
        if (file_exists($baseXlsm)) {
            return $this->_servirXlsmConDatos($baseXlsm, $clientes, $productos);
        }

        // ── Fallback: genera .xlsx sin macro ──────────────────────────────────
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()->setTitle('Plantilla Facturas Tradicionales');

        // ── Sheet 1: Facturas ──────────────────────────────────
        $sh = $spreadsheet->getActiveSheet();
        $sh->setTitle('Facturas');

        /*
         * LAYOUT A–S (19 columnas)
         * CAB: A B C D E F  G  H        I       J      K        L       M
         *      # T F C E NIT T SUBTOTAL IVA(CCF) TOTAL  CONDIC  ∑LÍNEAS ESTADO
         * DET: A B         N      O      P           Q          R           S
         *      # T         CANT   CÓDIGO DESCRIP▸auto PRECIO▸auto VTA.GRAV▸auto DESC
         */
        $colWidths = [
            'A'=>6,  'B'=>8,  'C'=>13, 'D'=>16, 'E'=>36,
            'F'=>16, 'G'=>10, 'H'=>14, 'I'=>12, 'J'=>13,
            'K'=>12, 'L'=>12, 'M'=>14,
            'N'=>10, 'O'=>14, 'P'=>38, 'Q'=>13, 'R'=>15, 'S'=>11,
        ];
        foreach ($colWidths as $col => $w) {
            $sh->getColumnDimension($col)->setWidth($w);
        }

        $headers = [
            'A1'=>'#',       'B1'=>'TIPO',    'C1'=>'FECHA',
            'D1'=>'CORRELATIVO','E1'=>'CLIENTE','F1'=>'NIT/DUI ▸auto',
            'G1'=>'TIPO DOC','H1'=>'SUBTOTAL', 'I1'=>'IVA (CCF)',
            'J1'=>'TOTAL ▸auto','K1'=>'CONDICIÓN','L1'=>'∑ LÍNEAS','M1'=>'ESTADO',
            'N1'=>'CANTIDAD','O1'=>'CÓDIGO',   'P1'=>'DESCRIPCIÓN ▸auto',
            'Q1'=>'PRECIO UNIT ▸auto','R1'=>'VENTA GRAVADA ▸auto','S1'=>'DESCUENTO',
        ];
        foreach ($headers as $coord => $val) {
            $sh->setCellValue($coord, $val);
        }
        $sh->getStyle('A1:S1')->applyFromArray([
            'font'      => ['bold'=>true, 'color'=>['rgb'=>'FFFFFF'], 'size'=>10],
            'fill'      => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'1F3864']],
            'alignment' => ['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical'  =>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ]);
        $sh->getRowDimension(1)->setRowHeight(22);
        $sh->freezePane('A2');

        // ── Formato fecha DD/MM/AAAA en columna C ──
        $sh->getStyle('C2:C1001')->getNumberFormat()->setFormatCode('DD/MM/YYYY');

        $vFecha = $sh->getCell('C2')->getDataValidation();
        $vFecha->setSqref('C2:C1001');
        $vFecha->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DATE);
        $vFecha->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_GREATERTHANOREQUAL);
        $vFecha->setFormula1(\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(mktime(0,0,0,1,1,1990)));
        $vFecha->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $vFecha->setAllowBlank(true);
        $vFecha->setShowErrorMessage(true);
        $vFecha->setErrorTitle('Fecha inválida');
        $vFecha->setError('Ingrese una fecha válida. Use DD/MM/AAAA.');
        $vFecha->setShowInputMessage(true);
        $vFecha->setPromptTitle('Fecha');
        $vFecha->setPrompt('Formato: DD/MM/AAAA');

        // ── Comentarios de ayuda ──
        $sh->getComment('H1')->getText()->createTextRun("CCF: base antes de IVA. FAC: total con IVA incluido.");
        $sh->getComment('I1')->getText()->createTextRun("Solo CCF: IVA = 13% del Subtotal. Para FAC deje en 0.");
        $sh->getComment('J1')->getText()->createTextRun("Calculado: Subtotal + IVA (CCF) o solo Subtotal (FAC).");
        $sh->getComment('O1')->getText()->createTextRun("Código del producto. Al llenarlo, la descripción y precio se completan solos.");
        $sh->getComment('P1')->getText()->createTextRun("Se completa al ingresar el Código. También puede escribirla manualmente.");
        $sh->getComment('Q1')->getText()->createTextRun("Se completa al ingresar el Código o la Descripción. Editable.");
        $sh->getComment('R1')->getText()->createTextRun("Calculado: Cantidad × Precio. Editable si necesita ajuste.");
        $sh->getComment('F1')->getText()->createTextRun("Se completa automáticamente si el cliente existe en el sistema.");

        // ── Datos de ejemplo ──
        $today         = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(time());
        $firstCliente  = !empty($clientes)  ? $clientes[0]->nombre        : 'CLIENTE EJEMPLO';
        $secondCliente = count($clientes)>1 ? $clientes[1]->nombre        : $firstCliente;
        $firstCod      = !empty($productos) ? ($productos[0]->codigo??'') : 'COD-001';
        $firstProd     = !empty($productos) ? $productos[0]->descripcion   : 'PRODUCTO A';
        $firstPrecio   = !empty($productos) ? (float)($productos[0]->precio_minimo??100) : 100;
        $secondCod     = count($productos)>1? ($productos[1]->codigo??'') : 'COD-002';
        $secondProd    = count($productos)>1? $productos[1]->descripcion   : 'PRODUCTO B';
        $secondPrecio  = count($productos)>1? (float)($productos[1]->precio_minimo??50)  : 50;

        // Ejemplo 1 — CCF
        $sh->setCellValue('A2',1);  $sh->setCellValue('B2','CAB');
        $sh->setCellValue('C2',$today);
        $sh->setCellValue('D2','CCF-00001'); $sh->setCellValue('E2',$firstCliente);
        $sh->setCellValue('G2','CCF'); $sh->setCellValue('H2',1000.00);
        $sh->setCellValue('I2',130.00); $sh->setCellValue('K2','Contado');

        $sh->setCellValue('A3',1); $sh->setCellValue('B3','DET');
        $sh->setCellValue('N3',10); $sh->setCellValue('O3',$firstCod);

        $sh->setCellValue('A4',1); $sh->setCellValue('B4','DET');
        $sh->setCellValue('N4',3);  $sh->setCellValue('O4',$secondCod);

        // Ejemplo 2 — FAC (fila 6, fila 5 vacía como separador)
        $sh->setCellValue('A6',2);  $sh->setCellValue('B6','CAB');
        $sh->setCellValue('C6',$today);
        $sh->setCellValue('D6','FAC-00001'); $sh->setCellValue('E6',$secondCliente);
        $sh->setCellValue('G6','Factura'); $sh->setCellValue('H6',1130.00);
        $sh->setCellValue('I6',0.00); $sh->setCellValue('K6','Crédito');

        $sh->setCellValue('A7',2); $sh->setCellValue('B7','DET');
        $sh->setCellValue('N7',10); $sh->setCellValue('O7',$firstCod);

        // ── Contadores ──
        $cliCount  = min(count($clientes), 999);
        $prodCount = min(count($productos), 499);

        // ── Fórmulas automáticas filas 2-1001 ──
        $jData=[]; $lData=[]; $mData=[]; $pData=[]; $qData=[]; $rData=[];
        // Datos terminarán en fila 4 + count - 1, con margen
        $cliEnd  = max(4, $cliCount  + 3) + 50;
        $prodEnd = max(4, $prodCount + 3) + 50;

        for ($r = 2; $r <= 1001; $r++) {
            // J: TOTAL = Subtotal + IVA
            $jData[] = ['=IF($B'.$r.'="CAB",$H'.$r.'+$I'.$r.',"")'];
            // L: ∑ LÍNEAS — suma col R (Venta Gravada) para DET con mismo # fac
            $lData[] = ['=IF($B'.$r.'="CAB",SUMPRODUCT(($A$2:$A$1001=A'.$r.')*($B$2:$B$1001="DET")*($R$2:$R$1001)),"")'];
            // M: ESTADO — compara ∑ líneas (L) con Subtotal (H)
            $mData[] = ['=IF($H'.$r.'="","",IF(ABS($L'.$r.'-$H'.$r.')<=0.02,"✓ OK","⚠ NO CUADRA"))'];
            // P: DESCRIPCIÓN — VLOOKUP desde código (O); Datos col D=código, E=desc
            $pData[] = ['=IF($B'.$r.'="DET",IFERROR(VLOOKUP(O'.$r.',Datos!$D$4:$E$'.$prodEnd.',2,FALSE),""),"")'];
            // Q: PRECIO — desde código (O→col3) o desde descripción (P→col2)
            $qData[] = ['=IF($B'.$r.'="DET",IF(O'.$r.'<>"",IFERROR(VLOOKUP(O'.$r.',Datos!$D$4:$F$'.$prodEnd.',3,FALSE),""),IFERROR(VLOOKUP(P'.$r.',Datos!$E$4:$F$'.$prodEnd.',2,FALSE),"")),"")'];
            // R: VENTA GRAVADA — Cantidad × Precio (editable manualmente si se necesita)
            $rData[] = ['=IF($B'.$r.'="DET",IFERROR(N'.$r.'*Q'.$r.',0),"")'];
        }
        $sh->fromArray($jData, null, 'J2');
        $sh->fromArray($lData, null, 'L2');
        $sh->fromArray($mData, null, 'M2');
        $sh->fromArray($pData, null, 'P2');
        $sh->fromArray($qData, null, 'Q2');
        $sh->fromArray($rData, null, 'R2');

        // F: NIT/DUI desde nombre cliente
        $fData = [];
        for ($r = 2; $r <= 1001; $r++) {
            $fData[] = ['=IF($B'.$r.'="CAB",IFERROR(VLOOKUP(E'.$r.',Datos!$A$4:$B$'.$cliEnd.',2,FALSE),""),"")'];
        }
        $sh->fromArray($fData, null, 'F2');

        // ── Estilos auto-fill (gris/cursiva) para columnas calculadas ──
        $autoFillStyle = [
            'fill' => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'F2F2F2']],
            'font' => ['color'=>['rgb'=>'555555'], 'italic'=>true],
        ];
        foreach (['F2:F1001','J2:J1001','M2:M1001','P2:P1001','Q2:Q1001','R2:R1001'] as $rng) {
            $sh->getStyle($rng)->applyFromArray($autoFillStyle);
        }

        // ── Data Validation ──
        // E: Cliente
        $vCli = $sh->getCell('E2')->getDataValidation();
        $vCli->setSqref('E2:E1001'); $vCli->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $vCli->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $vCli->setAllowBlank(true); $vCli->setShowDropDown(false);
        $vCli->setShowErrorMessage(true); $vCli->setErrorTitle('Cliente nuevo');
        $vCli->setError('Si es nuevo, escríbalo y complete NIT/DUI manualmente.');
        $vCli->setFormula1('ListaClientes');

        // G: Tipo Doc
        $vTipo = $sh->getCell('G2')->getDataValidation();
        $vTipo->setSqref('G2:G1001'); $vTipo->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $vTipo->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $vTipo->setAllowBlank(false); $vTipo->setShowDropDown(false);
        $vTipo->setFormula1('"CCF,Factura"');

        // K: Condición (era J)
        $vCond = $sh->getCell('K2')->getDataValidation();
        $vCond->setSqref('K2:K1001'); $vCond->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $vCond->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $vCond->setAllowBlank(false); $vCond->setShowDropDown(false);
        $vCond->setFormula1('"Contado,Crédito"');

        // P: Descripción producto (dropdown, puede escribirse libremente)
        $vProd = $sh->getCell('P2')->getDataValidation();
        $vProd->setSqref('P2:P1001'); $vProd->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $vProd->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $vProd->setAllowBlank(true); $vProd->setShowDropDown(false);
        $vProd->setShowErrorMessage(true); $vProd->setErrorTitle('Producto nuevo');
        $vProd->setError('Se creará automáticamente si no existe en el sistema.');
        $vProd->setFormula1('ListaProductos');

        // ── Formato condicional ──
        // ∑ LÍNEAS ahora está en L; compara L vs H
        $cRed = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $cRed->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $cRed->addCondition('=AND($B2="CAB",$H2<>"",ABS($L2-$H2)>0.02)');
        $cRed->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC7CE');
        $cRed->getStyle()->getFont()->getColor()->setARGB('FF9C0006');

        $cGreen = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $cGreen->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $cGreen->addCondition('=AND($B2="CAB",$H2<>"",ABS($L2-$H2)<=0.02)');
        $cGreen->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFC6EFCE');
        $cGreen->getStyle()->getFont()->getColor()->setARGB('FF276221');

        $cBlue = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $cBlue->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $cBlue->addCondition('=$B2="DET"');
        $cBlue->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F0FE');

        $sh->getStyle('A2:S1001')->setConditionalStyles([$cRed, $cGreen, $cBlue]);

        // ── Sheet 2: Datos (visible — matrices de clientes y productos) ──
        $shD = $spreadsheet->createSheet();
        $shD->setTitle('Datos');

        foreach (['A'=>38,'B'=>18,'C'=>16,'D'=>16,'E'=>38,'F'=>14] as $col => $w) {
            $shD->getColumnDimension($col)->setWidth($w);
        }

        $shD->setCellValue('A1', '⚠ NO EDITAR — Generado automáticamente desde la base de datos');
        $shD->mergeCells('A1:F1');
        $shD->getStyle('A1')->applyFromArray([
            'font'      => ['bold'=>true, 'color'=>['rgb'=>'7B3F00']],
            'fill'      => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'FCE4D6']],
            'alignment' => ['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $shD->setCellValue('A2','CLIENTES');  $shD->mergeCells('A2:C2');
        $shD->setCellValue('D2','PRODUCTOS'); $shD->mergeCells('D2:F2');
        $shD->getStyle('A2:F2')->applyFromArray([
            'font'      => ['bold'=>true, 'color'=>['rgb'=>'FFFFFF']],
            'fill'      => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'1F3864']],
            'alignment' => ['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Cabeceras Datos
        // Productos: D=CÓDIGO (llave búsqueda), E=DESCRIPCIÓN, F=PRECIO
        $shD->setCellValue('A3','Nombre');  $shD->setCellValue('B3','NIT/DUI');
        $shD->setCellValue('C3','Teléfono');
        $shD->setCellValue('D3','Código');  $shD->setCellValue('E3','Descripción');
        $shD->setCellValue('F3','Precio Unit.');
        $shD->getStyle('A3:F3')->applyFromArray([
            'font' => ['bold'=>true],
            'fill' => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'D9E1F2']],
        ]);

        // Clientes (A-C desde fila 4)
        foreach ($clientes as $i => $cli) {
            $row = $i + 4;
            $shD->setCellValue('A'.$row, $cli->nombre);
            $shD->setCellValue('B'.$row, $cli->nrc ?? $cli->numero_documento ?? '');
            $shD->setCellValue('C'.$row, $cli->telefono ?? '');
        }

        // Productos (D-F desde fila 4): D=código, E=descripción, F=precio
        $pMax = min(count($productos), 499);
        for ($i = 0; $i < $pMax; $i++) {
            $row = $i + 4;
            $shD->setCellValue('D'.$row, $productos[$i]->codigo ?? '');
            $shD->setCellValue('E'.$row, $productos[$i]->descripcion);
            $shD->setCellValue('F'.$row, (float)($productos[$i]->precio_minimo ?? 0));
        }

        // ── Rangos con nombre (data validation cross-sheet) ──
        $cliLastRow  = max(4, $cliCount  + 3);
        $prodLastRow = max(4, $prodCount + 3);

        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
            'ListaClientes', $shD, '$A$4:$A$'.$cliLastRow
        ));
        // ListaProductos referencia columna E (descripciones) para el dropdown de P
        $spreadsheet->addNamedRange(new \PhpOffice\PhpSpreadsheet\NamedRange(
            'ListaProductos', $shD, '$E$4:$E$'.$prodLastRow
        ));

        $spreadsheet->setActiveSheetIndex(0);

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'plantilla_facturas_' . date('Y-m-d') . '.xlsx';

        if (ob_get_level()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function previewManual()
    {
        $chk = requerirPermiso('cargar_facturas_manual');
        if ($chk !== true) return $chk;

        $file = $this->request->getFile('excel');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Archivo no válido']);
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo leer el Excel: ' . $e->getMessage()]);
        }

        $sh = $spreadsheet->getSheetByName('Facturas');
        if (!$sh) {
            return $this->response->setJSON(['success' => false, 'message' => 'El archivo no contiene la hoja "Facturas"']);
        }

        $facturas   = [];
        $emptyCount = 0;

        for ($row = 2; $row <= 1001; $row++) {

            $tipo   = strtoupper(trim((string)$sh->getCell('B' . $row)->getValue()));
            $numFac = $sh->getCell('A' . $row)->getValue();

            if (!$tipo || $numFac === null || $numFac === '') {
                if (++$emptyCount >= 8) break;
                continue;
            }
            $emptyCount = 0;

            if ($tipo === 'CAB') {

                $rawDate = $sh->getCell('C' . $row)->getValue();
                if (is_numeric($rawDate) && $rawDate > 0) {
                    $fechaStr = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$rawDate)
                        ->format('Y-m-d');
                } else {
                    $ts = strtotime((string)$rawDate);
                    $fechaStr = $ts ? date('Y-m-d', $ts) : null;
                }

                $tipoDocRaw = strtoupper(trim((string)$sh->getCell('G' . $row)->getValue()));
                $tipoDte    = (str_contains($tipoDocRaw, 'CCF') || $tipoDocRaw === '03') ? '03' : '01';

                $condRaw   = strtolower(trim((string)$sh->getCell('K' . $row)->getValue()));
                $condicion = str_contains($condRaw, 'r') ? 2 : 1;

                // F puede ser fórmula VLOOKUP — leer valor calculado
                $nitCell = $sh->getCell('F' . $row);
                $nitVal  = $nitCell->getCalculatedValue();
                if ($nitVal === null || $nitVal === '' || str_contains((string)$nitVal, '#')) {
                    $nitVal = '';
                }

                $facturas[$numFac] = [
                    'num_fac'        => $numFac,
                    'correlativo'    => trim((string)$sh->getCell('D' . $row)->getValue()),
                    'fecha'          => $fechaStr,
                    'cliente_nombre' => trim((string)$sh->getCell('E' . $row)->getValue()),
                    'nit_dui'        => trim((string)$nitVal),
                    'tipo_dte'       => $tipoDte,
                    'subtotal'       => (float)$sh->getCell('H' . $row)->getValue(),
                    'iva_declarado'  => (float)$sh->getCell('I' . $row)->getValue(),
                    'condicion'      => $condicion,
                    'lineas'         => [],
                ];

            } elseif ($tipo === 'DET' && isset($facturas[$numFac])) {

                // P, Q, R son fórmulas — leer valor calculado
                $descVal = $sh->getCell('P' . $row)->getCalculatedValue();
                if (str_contains((string)$descVal, '#')) $descVal = '';

                $precioVal = $sh->getCell('Q' . $row)->getCalculatedValue();
                if (!is_numeric($precioVal)) $precioVal = 0;

                $ventaVal = $sh->getCell('R' . $row)->getCalculatedValue();
                if (!is_numeric($ventaVal)) $ventaVal = 0;

                $facturas[$numFac]['lineas'][] = [
                    'cantidad'        => (float)$sh->getCell('N' . $row)->getValue(),
                    'codigo'          => trim((string)$sh->getCell('O' . $row)->getValue()),
                    'descripcion'     => trim((string)$descVal),
                    'precio_unitario' => (float)$precioVal,
                    'venta_gravada'   => (float)$ventaVal,
                    'descuento'       => (float)$sh->getCell('S' . $row)->getValue(),
                ];
            }
        }

        if (empty($facturas)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se encontraron facturas en el archivo']);
        }

        $clienteModel         = new ClienteModel();
        $productoModel        = new ProductoModel();
        $facturaHeadModel     = new FacturaHeadModel();

        $result = [];

        foreach ($facturas as $fac) {

            $cli = $fac['cliente_nombre']
                ? $clienteModel->where('LOWER(TRIM(nombre))', strtolower(trim($fac['cliente_nombre'])))->first()
                : null;

            $fac['cliente_nuevo'] = !$cli;
            $fac['cliente_id']    = $cli ? $cli->id : null;

            // Duplicado = misma combinación tipo_dte + correlativo + fecha
            $existe = $fac['correlativo']
                ? $facturaHeadModel
                    ->where('numero_control', $fac['correlativo'])
                    ->where('tipo_dte',       $fac['tipo_dte'])
                    ->where('fecha_emision',  $fac['fecha'])
                    ->first()
                : null;
            $fac['duplicado'] = (bool)$existe;

            $sumaLineas         = array_sum(array_column($fac['lineas'], 'venta_gravada'));
            $fac['suma_lineas'] = $sumaLineas;
            $fac['cuadra']      = abs($sumaLineas - $fac['subtotal']) <= 0.02;

            $tieneNuevos = false;
            foreach ($fac['lineas'] as &$linea) {
                // Buscar primero por código (case-insensitive), luego por descripción
                $prod     = null;
                $codBusq  = strtoupper(trim($linea['codigo'] ?? ''));
                if ($codBusq) {
                    $prod = $productoModel
                        ->where('UPPER(TRIM(codigo))', $codBusq)
                        ->first();
                }
                if (!$prod && !empty($linea['descripcion'])) {
                    $prod = $productoModel
                        ->where('LOWER(TRIM(descripcion))', strtolower(trim($linea['descripcion'])))
                        ->first();
                }
                $linea['producto_nuevo'] = !$prod;
                $linea['producto_id']    = $prod ? $prod->id : null;
                if (!$prod) $tieneNuevos = true;
            }
            unset($linea);

            $fac['tiene_productos_nuevos'] = $tieneNuevos;
            $result[] = $fac;
        }

        // ── Leer hoja Datos: clientes marcados como NUEVO (col D = "NUEVO") ──
        $clientesNuevos = [];
        $shDatos = $spreadsheet->getSheetByName('Datos');
        if ($shDatos) {
            for ($row = 4; $row <= 1003; $row++) {
                $nombre = trim((string)$shDatos->getCell('A'.$row)->getValue());
                if (!$nombre) break;
                $flag = strtoupper(trim((string)$shDatos->getCell('D'.$row)->getValue()));
                if ($flag === 'NUEVO') {
                    $clientesNuevos[] = [
                        'nombre'   => $nombre,
                        'nit_dui'  => trim((string)$shDatos->getCell('B'.$row)->getValue()),
                        'telefono' => trim((string)$shDatos->getCell('C'.$row)->getValue()),
                    ];
                }
            }
        }

        return $this->response->setJSON([
            'success'         => true,
            'facturas'        => $result,
            'clientes_nuevos' => $clientesNuevos,
        ]);
    }

    public function procesarCargaManual()
    {
        $chk = requerirPermiso('cargar_facturas_manual');
        if ($chk !== true) return $chk;

        $user_id = session()->get('user_id');

        $data     = $this->request->getJSON(true);
        $facturas = $data['facturas'] ?? [];

        // Índice de clientes nuevos por nombre (para lookup rápido con datos completos)
        $clientesNuevosIdx = [];
        foreach ($data['clientes_nuevos'] ?? [] as $cn) {
            $key = strtolower(trim($cn['nombre'] ?? ''));
            if ($key) $clientesNuevosIdx[$key] = $cn;
        }

        if (empty($facturas)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin datos']);
        }

        $clienteModel        = new ClienteModel();
        $facturaHeadModel    = new FacturaHeadModel();
        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();
        $productoModel       = new ProductoModel();
        $movimientoModel     = new ProductoMovimientoModel();

        $db = \Config\Database::connect();

        $procesadas     = 0;
        $saltadas       = [];
        $errores        = [];
        $asientosQueue  = [];

        foreach ($facturas as $fac) {

            $correlativo = trim($fac['correlativo'] ?? '');

            if (!$correlativo) {
                $errores[] = 'Fila sin correlativo, omitida';
                continue;
            }

            if ($facturaHeadModel->where('numero_control', $correlativo)->first()) {
                $saltadas[] = $correlativo;
                continue;
            }

            $db->transStart();

            try {

                // CLIENTE
                $nombreCli = trim($fac['cliente_nombre'] ?? '');
                $nitDui    = trim($fac['nit_dui'] ?? '');
                $clienteId = null;

                if ($nombreCli) {
                    $cli = $clienteModel
                        ->where('LOWER(TRIM(nombre))', strtolower($nombreCli))
                        ->first();

                    if (!$cli && $nitDui) {
                        // buscarPorDocumento usa los valores reales: 'NIT' / 'DUI'
                        $cli = $clienteModel->buscarPorDocumento('NIT', $nitDui)
                            ?? $clienteModel->buscarPorDocumento('DUI', $nitDui);
                    }

                    if (!$cli) {
                        $datosNuevo = $clientesNuevosIdx[strtolower($nombreCli)] ?? null;
                        $nitFinal   = $datosNuevo['nit_dui']  ?? $nitDui;
                        $telFinal   = $datosNuevo['telefono'] ?? null;

                        // Determinar tipo a partir de la longitud numérica del documento
                        $tipoDocFinal = null;
                        if ($nitFinal) {
                            $soloDigitos  = preg_replace('/\D/', '', $nitFinal);
                            $tipoDocFinal = strlen($soloDigitos) <= 9 ? 'DUI' : 'NIT';
                        }

                        $insertData = [
                            'nombre'           => $nombreCli,
                            'tipo_documento'   => $tipoDocFinal,
                            'numero_documento' => $nitFinal ?: null,
                            'telefono'         => $telFinal ?: null,
                        ];

                        $clienteId = $clienteModel->insert($insertData);

                        if (!$clienteId) {
                            throw new \RuntimeException(
                                'No se pudo crear el cliente "' . $nombreCli . '": ' .
                                implode(', ', $clienteModel->errors())
                            );
                        }
                    } else {
                        $clienteId = $cli->id;
                    }
                }

                // MONTOS — lógica según tipo de documento
                $tipoDte     = $fac['tipo_dte'] ?? '01';
                $subtotal    = (float)($fac['subtotal']      ?? 0);
                $ivaDec      = (float)($fac['iva_declarado'] ?? 0);
                $sumaGravada = (float)($fac['suma_lineas']   ?? array_sum(array_column($fac['lineas'], 'venta_gravada')));

                if ($tipoDte === '01') {
                    // FAC: subtotal = total CON IVA (precios incluyen IVA)
                    $totalGravada = round($subtotal / 1.13, 2);
                    $totalIva     = round($subtotal - $totalGravada, 2);
                    $totalPagar   = $subtotal;
                } else {
                    // CCF: subtotal = base pre-IVA; IVA declarado por separado
                    $totalGravada = $subtotal;
                    $totalIva     = $ivaDec;
                    $totalPagar   = $subtotal + $ivaDec;
                }

                $condicion    = (int)($fac['condicion'] ?? 1);
                $plazoCredito = $condicion === 2 ? 30 : null;

                $dataHead = [
                    'clase'                         => 'TRADICIONAL',
                    'ambiente'                      => 'T',
                    'tipo_dte'                      => $tipoDte,
                    'numero_control'                => $correlativo,
                    'codigo_generacion'             => null,
                    'sello_recibido'                => null,
                    'fecha_emision'                 => $fac['fecha'] ?? null,
                    'hora_emision'                  => null,
                    'tipo_moneda'                   => 'USD',
                    'receptor_id'                   => $clienteId,
                    'vendedor_id'                   => null,
                    'total_gravada'                 => $totalGravada,
                    'sub_total'                     => $sumaGravada,
                    'total_iva'                     => $totalIva,
                    'monto_total_operacion'         => $totalPagar,
                    'total_pagar'                   => $totalPagar,
                    'condicion_operacion'           => $condicion,
                    'plazo_credito'                 => $plazoCredito,
                    'iva_rete1'                     => 0,
                    'saldo'                         => $totalPagar,
                    'tipo_venta'                    => 1,
                    'codigo_generacion_relacionado' => null,
                    'observaciones'                 => 'Factura tradicional pre-electrónica',
                ];

                if (!$facturaHeadModel->insert($dataHead)) {
                    throw new \RuntimeException('Error insertando cabecera: ' . implode(', ', $facturaHeadModel->errors()));
                }

                $facturaId = $facturaHeadModel->getInsertID();

                // DETALLES
                foreach ($fac['lineas'] as $idx => $linea) {

                    $descripcion  = trim($linea['descripcion'] ?? '');
                    $codigoLinea  = strtoupper(trim($linea['codigo'] ?? ''));
                    if (!$descripcion && !$codigoLinea) continue;

                    $ventaGravada  = (float)($linea['venta_gravada']  ?? 0);
                    $cantidadLinea = (float)($linea['cantidad']        ?? 1);
                    $precioUni     = (float)($linea['precio_unitario'] ?? 0);
                    $descuento     = (float)($linea['descuento']       ?? 0);

                    // Para FAC, separar IVA de venta_gravada si el usuario ingresó con IVA
                    $ivaItem = 0;
                    if ($tipoDte === '01') {
                        $base         = round($ventaGravada / 1.13, 4);
                        $ivaItem      = round($ventaGravada - $base, 4);
                        $ventaGravada = $base;
                    } else {
                        // CCF: IVA proporcional
                        if ($sumaGravada > 0) {
                            $ivaItem = round($totalIva * ($ventaGravada / $sumaGravada), 4);
                        }
                    }

                    // Buscar producto: primero por código, luego por descripción
                    $producto = null;
                    if ($codigoLinea) {
                        $producto = $productoModel
                            ->where('UPPER(TRIM(codigo))', $codigoLinea)
                            ->first();
                    }
                    if (!$producto && $descripcion) {
                        $producto = $productoModel
                            ->where('LOWER(TRIM(descripcion))', strtolower($descripcion))
                            ->first();
                    }
                    if (!$producto) {
                        $newId    = $productoModel->insert([
                            'descripcion' => $descripcion ?: ($codigoLinea ?: 'SIN DESCRIPCIÓN'),
                            'codigo'      => $codigoLinea ?: null,
                            'activo'      => 1,
                            'tipo'        => 1,
                        ]);
                        $producto = $productoModel->find($newId);
                    }

                    if (empty($producto->id)) {
                        throw new \RuntimeException('Producto inválido: ' . ($codigoLinea ?: $descripcion));
                    }

                    $detalleData = [
                        'factura_id'      => $facturaId,
                        'producto_id'     => $producto->id,
                        'num_item'        => $idx + 1,
                        'tipo_item'       => 1,
                        'codigo'          => $codigoLinea ?: ($producto->codigo ?? null),
                        'descripcion'     => $descripcion ?: ($producto->descripcion ?? ''),
                        'cantidad'        => $cantidadLinea,
                        'unidad_medida'   => null,
                        'precio_unitario' => $precioUni,
                        'monto_descuento' => $descuento,
                        'venta_no_sujeta' => 0,
                        'venta_exenta'    => 0,
                        'venta_gravada'   => $ventaGravada,
                        'iva_item'        => $ivaItem,
                    ];

                    if (!$facturaDetalleModel->insert($detalleData)) {
                        throw new \RuntimeException('Error en detalle: ' . implode(', ', $facturaDetalleModel->errors()));
                    }

                    // Movimiento inventario (SALIDA por venta)
                    if ($cantidadLinea > 0) {
                        $movimientoModel->insert([
                            'producto_id'     => $producto->id,
                            'tipo_movimiento' => 'venta',
                            'cantidad'        => -abs($cantidadLinea),
                            'referencia_tipo' => 'factura',
                            'referencia_id'   => $facturaId,
                        ]);
                    }
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    $err = $db->error();
                    $errores[] = $correlativo . ': error BD (' . ($err['message'] ?? 'desconocido') . ')';
                } else {
                    $procesadas++;

                    // Encolar asiento contable (CCF y FAC)
                    if (in_array($tipoDte, ['01', '03'])) {
                        $asientosQueue[] = [
                            'tipoDte'       => $tipoDte,
                            'totalGravada'  => $totalGravada,
                            'montoTotalOp'  => $totalPagar,
                            'retencion'     => 0,
                            'fechaEmision'  => $fac['fecha'] ?? date('Y-m-d'),
                            'numeroControl' => $correlativo,
                            'facturaId'     => $facturaId,
                            'clienteId'     => $clienteId,
                        ];
                    }
                }

            } catch (\Throwable $e) {
                $db->transRollback();
                $errores[] = $correlativo . ': ' . $e->getMessage();
                log_message('error', 'cargaManualFactura ' . $correlativo . ': ' . $e->getMessage());
            }
        }

        // Asientos contables (post-commit, igual que carga JSON)
        $asientosCreados  = 0;
        $asientosOmitidos = [];

        if (!empty($asientosQueue)) {
            try {
                helper('cont_ventas');
                $periodosModel    = new \App\Models\ContPeriodosModel();
                $contHeadModel    = new \App\Models\ContAsientosHeadModel();
                $contDetalleModel = new \App\Models\ContAsientosDetalleModel();

                foreach ($asientosQueue as $item) {

                    $ref       = substr($item['numeroControl'], -6);
                    $anioItem  = (int)substr($item['fechaEmision'], 0, 4);
                    $mesItem   = (int)substr($item['fechaEmision'], 5, 2);
                    $periodo   = $periodosModel->abrirObtenerPeriodo($anioItem, $mesItem);

                    if (!$periodo) {
                        $asientosOmitidos[] = "{$ref}: período {$mesItem}/{$anioItem} cerrado";
                        continue;
                    }

                    $tipoDteH = $item['tipoDte'] === '03' ? 'CCF' : 'FAC';
                    $monto    = $item['tipoDte'] === '03'
                        ? (float)$item['totalGravada']
                        : (float)$item['montoTotalOp'];

                    if ($monto <= 0) { $asientosOmitidos[] = "{$ref}: monto inválido"; continue; }

                    // Subcuenta cliente
                    $cxcOverrideId = null;
                    if (!empty($item['clienteId'])) {
                        $cli = (new ClienteModel())->select('id, nombre, cuenta_contable_id')->find((int)$item['clienteId']);
                        if ($cli && !empty($cli->cuenta_contable_id)) {
                            $cxcOverrideId = (int)$cli->cuenta_contable_id;
                        } elseif ($cli) {
                            $planModel = new \App\Models\ContPlanCuentasModel();
                            $padre     = $planModel->where('codigo', '110201')->first();
                            if ($padre) {
                                $sub = $planModel->like('codigo', '110201', 'after')->where('nombre', mb_strtoupper($cli->nombre))->first();
                                if ($sub) {
                                    (new ClienteModel())->update($cli->id, ['cuenta_contable_id' => $sub->id]);
                                    $cxcOverrideId = (int)$sub->id;
                                } else {
                                    $dbRaw = \Config\Database::connect();
                                    $sig   = (int)($dbRaw->query("SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 7) AS UNSIGNED)), 0) + 1 AS sig FROM cont_plan_cuentas WHERE codigo LIKE '110201%' AND LENGTH(codigo) > 6")->getRow()->sig ?? 1);
                                    $newId = $planModel->insert([
                                        'codigo' => '110201' . str_pad($sig, 4, '0', STR_PAD_LEFT),
                                        'nombre' => mb_strtoupper($cli->nombre),
                                        'tipo' => $padre->tipo, 'naturaleza' => $padre->naturaleza,
                                        'nivel' => $padre->nivel + 1, 'cuenta_padre_id' => $padre->id,
                                        'acepta_movimientos' => 1, 'activo' => 1,
                                    ]);
                                    if ($newId) { (new ClienteModel())->update($cli->id, ['cuenta_contable_id' => $newId]); $cxcOverrideId = (int)$newId; }
                                }
                            }
                        }
                    }

                    try {
                        $resultado = cont_asiento_venta_json(
                            $tipoDteH, $monto, 0, $ref,
                            $periodo->id, $item['fechaEmision'],
                            "Venta {$tipoDteH} {$ref}", null, $cxcOverrideId
                        );
                    } catch (\Throwable $e) {
                        $asientosOmitidos[] = "{$ref}: " . $e->getMessage();
                        continue;
                    }

                    if (!$resultado['ok']) {
                        $asientosOmitidos[] = "{$ref}: " . implode(', ', $resultado['errores'] ?? []);
                        continue;
                    }

                    $payload      = $resultado['payload'];
                    $fechaAsiento = $payload['fecha'];
                    $tPartidaId   = $payload['tipo_partida_id'] ?? null;
                    $totalDebe    = round(array_sum(array_column($payload['lineas'], 'debe')),  2);
                    $totalHaber   = round(array_sum(array_column($payload['lineas'], 'haber')), 2);

                    $existing = $tPartidaId ? $contHeadModel->buscarPartidaDia($tPartidaId, $fechaAsiento) : null;

                    if ($existing) {
                        $dbRaw    = \Config\Database::connect();
                        $maxOrden = (int)($dbRaw->query('SELECT COALESCE(MAX(orden), 0) AS m FROM cont_asientos_detalle WHERE asiento_id = ?', [$existing->id])->getRow()->m ?? 0);
                        foreach ($payload['lineas'] as $i => $l) {
                            $contDetalleModel->insert(['asiento_id' => $existing->id, 'cuenta_id' => $l['cuenta_id'], 'descripcion' => $l['descripcion'], 'debe' => $l['debe'], 'haber' => $l['haber'], 'orden' => $maxOrden + $i + 1]);
                        }
                        $contHeadModel->update($existing->id, ['total_debe' => round($existing->total_debe + $totalDebe, 2), 'total_haber' => round($existing->total_haber + $totalHaber, 2)]);
                        $asientoId = $existing->id;
                    } else {
                        $anioFecha  = (int)substr($fechaAsiento, 0, 4);
                        $numPartida = $tPartidaId ? $contHeadModel->getSiguienteNumeroPartida($tPartidaId, $anioFecha) : null;
                        $asientoId  = $contHeadModel->insert([
                            'numero_asiento' => $contHeadModel->getSiguienteNumero(),
                            'numero_partida' => $numPartida, 'fecha' => $fechaAsiento,
                            'descripcion' => $payload['descripcion'], 'tipo' => $payload['tipo'],
                            'tipo_partida_id' => $tPartidaId, 'estado' => 'APROBADO',
                            'periodo_id' => $payload['periodo_id'], 'total_debe' => $totalDebe,
                            'total_haber' => $totalHaber, 'referencia' => $payload['referencia'],
                            'documento_tipo' => 'factura', 'documento_id' => $item['facturaId'],
                            'usuario_id' => $user_id, 'usuario_aprueba_id' => $user_id,
                            'fecha_aprobacion' => date('Y-m-d H:i:s'),
                        ]);
                        if (!$asientoId) { $asientosOmitidos[] = "{$ref}: error insertando asiento"; continue; }
                        foreach ($payload['lineas'] as $i => $l) {
                            $contDetalleModel->insert(['asiento_id' => $asientoId, 'cuenta_id' => $l['cuenta_id'], 'descripcion' => $l['descripcion'], 'debe' => $l['debe'], 'haber' => $l['haber'], 'orden' => $i + 1]);
                        }
                        $asientosCreados++;
                    }

                    $contHeadModel->aprobarConSaldos($asientoId, $payload['lineas'], (int)$payload['periodo_id'], $fechaAsiento, $payload['descripcion'], $payload['tipo'], $periodo);
                }
            } catch (\Throwable $e) {
                log_message('error', 'cargaManualFactura asientos: ' . $e->getMessage());
                $asientosOmitidos[] = 'Error general en asientos: ' . $e->getMessage();
            }
        }

        $msg = "Facturas procesadas: {$procesadas}.";
        if ($asientosCreados) $msg .= " Asientos generados: {$asientosCreados}.";

        return $this->response->setJSON([
            'success'           => true,
            'total'             => $procesadas,
            'saltadas'          => $saltadas,
            'errores'           => $errores,
            'asientos_creados'  => $asientosCreados,
            'asientos_omitidos' => $asientosOmitidos,
            'message'           => $msg,
        ]);
    }

    public function detalle($id)
    {
        $facturaHeadModel    = new FacturaHeadModel();
        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();

        // Cabecera
        $factura = $this->facturaConReceptor((int)$id);

        if (!$factura) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (!$this->puedeAccederFactura($factura)) {
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

        // Verificar si la factura pertenece a un quedan activo
        $quedanInfo = $db->table('quedan_facturas qf')
            ->select('q.id, q.numero_quedan, q.fecha_pago, q.anulado')
            ->join('quedans q', 'q.id = qf.quedan_id')
            ->where('qf.factura_id', $id)
            ->orderBy('q.id', 'DESC')
            ->get()
            ->getRow();

        $factura->quedan = $quedanInfo;

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

        // Recuperos (remesas) vinculadas a esta factura
        $remesas = $db->query(
            "SELECT rd.monto_aplicado,
                    r.id            AS recupero_id,
                    r.numero_recupero,
                    r.fecha,
                    r.forma_cobro,
                    r.estado,
                    r.pago_id
             FROM recuperos_detalle rd
             JOIN recuperos r ON r.id = rd.recupero_id
             WHERE rd.factura_id = ?
             ORDER BY r.fecha ASC, r.id ASC",
            [$id]
        )->getResult();

        return view('facturas/detalle', [
            'factura'           => $factura,
            'detalles'          => $detalles,
            'facturaRelacionada' => $facturaRelacionada,
            'notasCredito'      => $notasCredito,
            'pagos'             => $pagos,
            'remesas'           => $remesas,
            'consultaHaciendaUrl' => $this->consultaHaciendaUrl($factura),
            'qrHaciendaDataUri'  => $this->qrDataUri($this->consultaHaciendaUrl($factura), 4),
        ]);
    }

    public function qr($id)
    {
        $factura = $this->facturaConReceptor((int)$id);

        if (!$factura || !$this->puedeAccederFactura($factura)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $url = $this->consultaHaciendaUrl($factura);

        if ($url === '' || !class_exists('\TCPDF2DBarcode')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $qr = new \TCPDF2DBarcode($url, 'QRCODE,H');

        return $this->response
            ->setContentType('image/png')
            ->setBody($qr->getBarcodePngData(6, 6));
    }

    public function pdf($id)
    {
        $factura = $this->facturaConReceptor((int)$id);

        if (!$factura || !$this->puedeAccederFactura($factura)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $detalles = (new \App\Models\FacturaDetalleModel())
            ->where('factura_id', $id)
            ->orderBy('num_item', 'ASC')
            ->findAll();

        $consultaUrl = $this->consultaHaciendaUrl($factura);
        $emisor = (new EmisorModel())->first();

        $html = view('facturas/imprimible_pdf', [
            'factura' => $factura,
            'detalles' => $detalles,
            'emisor' => $emisor,
            'consultaHaciendaUrl' => $consultaUrl,
            'qrHaciendaDataUri' => $this->qrDataUri($consultaUrl, 5),
            'logoDataUri' => $this->logoDataUri(),
        ]);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text(500, 815, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 8, [80, 91, 104]);

        $numero = !empty($factura->numero_control) ? substr($factura->numero_control, -6) : $factura->id;
        $prefix = $this->prefijoArchivoDte($factura->tipo_dte ?? null);
        $filename = $prefix . '_' . $numero . '.pdf';
        $disposition = $this->request->getGet('download') ? 'attachment' : 'inline';

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function json($id)
    {
        $factura = $this->facturaConReceptor((int)$id);

        if (!$factura || !$this->puedeAccederFactura($factura)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $jsonRow = (new FacturaJsonModel())->getByFactura((int)$id);

        if (!$jsonRow || empty($jsonRow->json_original)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('JSON de factura no encontrado.');
        }

        $contenido = $jsonRow->json_original;
        $decoded = json_decode($contenido, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $contenido = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $numero = !empty($factura->numero_control) ? substr($factura->numero_control, -6) : $factura->id;
        $filename = $this->prefijoArchivoDte($factura->tipo_dte ?? null) . '_' . $numero . '.json';

        return $this->response
            ->setContentType('application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($contenido);
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

    /**
     * AJAX: al previsualizar un JSON en la carga masiva, revisa si el
     * documento/NRC del receptor coincide con MÁS de un cliente ya registrado
     * (p.ej. varias sucursales de la misma empresa cargadas como fichas
     * distintas). Si hay ambigüedad, el front pide elegir manualmente cuál
     * usar — igual que ya se exige elegir vendedor para cada factura.
     */
    public function verificarClienteDuplicado()
    {
        $tipoDocumento   = trim((string)$this->request->getPost('tipo_documento'));
        $numeroDocumento = trim((string)$this->request->getPost('numero_documento'));
        $nrc             = trim((string)$this->request->getPost('nrc'));

        $model         = new ClienteModel();
        $candidatosMap = [];

        if ($numeroDocumento !== '') {
            foreach ($model->buscarTodosPorDocumento($numeroDocumento) as $c) {
                $candidatosMap[$c->id] = $c;
            }
        }

        if ($nrc !== '') {
            foreach ($model->buscarTodosPorNRC($nrc) as $c) {
                $candidatosMap[$c->id] = $c;
            }
        }

        $candidatos = array_values(array_map(static function ($c) {
            $partes = [$c->nombre];
            if (!empty($c->numero_documento)) $partes[] = 'Doc: ' . $c->numero_documento;
            if (!empty($c->nrc))              $partes[] = 'NRC: ' . $c->nrc;

            return [
                'id'   => (int)$c->id,
                'text' => implode(' — ', $partes),
            ];
        }, $candidatosMap));

        return $this->response->setJSON([
            'duplicado'  => count($candidatos) > 1,
            'candidatos' => $candidatos,
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

        $session = session();
        $user_id = $session->get('user_id');

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
        if (!puedeVerDocumentosTodosVendedores()) {
            $sellerScope = vendedorUsuarioActual();
            if (!$sellerScope || (int)$factura->vendedor_id !== (int)$sellerScope) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No tienes acceso a esta factura.'
                ]);
            }
        }

        if ($factura->anulada == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La factura ya está anulada.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Anular factura
        $facturaModel->update($id, [
            'anulada'         => 1,
            'saldo'           => 0,
            'anulada_por'     => $user_id,
            'fecha_anulacion' => date('Y-m-d H:i:s')
        ]);

        // Obtener detalles de pagos activos
        $detalles = $pagosDetailsModel
            ->where('factura_id', $id)
            ->where('anulado', 0)
            ->findAll();

        foreach ($detalles as $detalle) {

            $montoDevuelto = $detalle->monto;
            $pagoId        = $detalle->pago_id;

            $pago = $pagosHeadModel->find($pagoId);
            if (!$pago) continue;

            // 🔹 Si la cuenta viene null usar la cuenta 1
            $accountId = $pago->numero_cuenta_bancaria ?? 1;

            // 🔹 Obtener cuenta
            $cuenta = $accountModel->find($accountId);

            // 🔹 Si tampoco existe la cuenta, usar la 1 como fallback
            if (!$cuenta) {
                $accountId = 1;
                $cuenta = $accountModel->find($accountId);
                if (!$cuenta) continue;
            }

            $balanceActual = (float) $cuenta->balance;
            $nuevoBalance  = $balanceActual - $montoDevuelto;

            $accountModel->update($accountId, [
                'balance' => $nuevoBalance
            ]);

            // 🔹 Registrar transacción
            $transactionModel->addSalida(
                $accountId,
                $montoDevuelto,
                'Reversión por anulación de factura',
                'Factura Nº ' . substr($factura->numero_control, -6),
                $pagoId
            );

            // 🔹 Anular detalle
            $pagosDetailsModel->update($detalle->id, [
                'anulado'    => 1,
                'anulado_at' => date('Y-m-d H:i:s'),
                'anulado_by' => $user_id
            ]);
        }

        // Desasociar la factura de cualquier NP que la tenga vinculada
        $db->table('pedidos_head')
           ->where('factura_id', $id)
           ->where('anulada', 0)
           ->update(['factura_id' => null]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al anular la factura.'
            ]);
        }

        // Reversar asiento contable: se calcula desde los datos de la factura
        // para funcionar correctamente con asientos consolidados por día.
        $mensajeContable = '';
        if (in_array($factura->tipo_dte, ['01', '03'])) {
            try {
                helper('cont_ventas');
                $contHeadModel    = new \App\Models\ContAsientosHeadModel();
                $contDetalleModel = new \App\Models\ContAsientosDetalleModel();
                $periodosModel    = new \App\Models\ContPeriodosModel();

                $periodo = $periodosModel->abrirObtenerPeriodo((int)date('Y'), (int)date('n'));
                if (!$periodo) {
                    throw new \Exception('El período contable de ' . date('m/Y') . ' está cerrado; ábrelo antes de anular');
                }

                $ref           = substr($factura->numero_control, -6);
                $tipoDteHelper = $factura->tipo_dte === '03' ? 'CCF' : 'FAC';
                $monto         = $factura->tipo_dte === '03'
                    ? (float)$factura->total_gravada
                    : (float)$factura->monto_total_operacion;
                $retencion     = (float)($factura->iva_rete1 ?? 0);

                if ($monto <= 0) {
                    throw new \Exception("Monto de factura inválido ({$monto})");
                }

                // Subcuenta CxC específica del cliente si existe
                $cxcOverrideId = null;
                if (!empty($factura->receptor_id)) {
                    $cli = (new \App\Models\ClienteModel())
                        ->select('id, cuenta_contable_id')
                        ->find((int)$factura->receptor_id);
                    if ($cli && !empty($cli->cuenta_contable_id)) {
                        $cxcOverrideId = (int)$cli->cuenta_contable_id;
                    }
                }

                // Calcular líneas del asiento original (misma lógica que la carga)
                $resultado = cont_asiento_venta_json(
                    $tipoDteHelper,
                    $monto,
                    $retencion,
                    $ref,
                    $periodo->id,
                    date('Y-m-d'),
                    'REVERSA: Anulación ' . $tipoDteHelper . ' ' . $ref,
                    null,
                    $cxcOverrideId
                );

                if (!$resultado['ok']) {
                    throw new \Exception('Error al calcular líneas contables: ' . implode(', ', $resultado['errores']));
                }

                // Invertir DEBE ↔ HABER para el asiento de reversión
                $payload       = $resultado['payload'];
                $lineasReversa = [];
                foreach ($payload['lineas'] as $l) {
                    $lineasReversa[] = [
                        'cuenta_id'   => $l['cuenta_id'],
                        'descripcion' => 'REVERSA: ' . $l['descripcion'],
                        'debe'        => $l['haber'],
                        'haber'       => $l['debe'],
                    ];
                }

                $totalDebe  = round(array_sum(array_column($lineasReversa, 'debe')), 2);
                $totalHaber = round(array_sum(array_column($lineasReversa, 'haber')), 2);

                $tipoPartidaId = !empty($payload['tipo_partida_id']) ? (int)$payload['tipo_partida_id'] : null;
                $numPartida    = $tipoPartidaId
                    ? $contHeadModel->getSiguienteNumeroPartida($tipoPartidaId, (int)date('Y'))
                    : null;
                $numAsiento    = $contHeadModel->getSiguienteNumero();

                $reversaId = $contHeadModel->insert([
                    'numero_asiento'     => $numAsiento,
                    'numero_partida'     => $numPartida,
                    'fecha'              => date('Y-m-d'),
                    'descripcion'        => 'REVERSA: Anulación ' . $tipoDteHelper . ' ' . $ref,
                    'tipo'               => 'DIARIO',
                    'tipo_partida_id'    => $tipoPartidaId,
                    'estado'             => 'APROBADO',
                    'periodo_id'         => $periodo->id,
                    'total_debe'         => $totalDebe,
                    'total_haber'        => $totalHaber,
                    'referencia'         => 'Anulación ' . $ref,
                    'documento_tipo'     => 'factura',
                    'documento_id'       => (int)$id,
                    'usuario_id'         => $user_id,
                    'usuario_aprueba_id' => $user_id,
                    'fecha_aprobacion'   => date('Y-m-d H:i:s'),
                ]);

                if (!$reversaId) {
                    throw new \Exception('No se pudo insertar el encabezado del asiento de reversión');
                }

                foreach ($lineasReversa as $i => $l) {
                    $contDetalleModel->insert([
                        'asiento_id'  => $reversaId,
                        'cuenta_id'   => $l['cuenta_id'],
                        'descripcion' => $l['descripcion'],
                        'debe'        => $l['debe'],
                        'haber'       => $l['haber'],
                        'orden'       => $i + 1,
                    ]);
                }

                $contHeadModel->aprobarConSaldos(
                    $reversaId,
                    $lineasReversa,
                    $periodo->id,
                    date('Y-m-d'),
                    'REVERSA: Anulación ' . $tipoDteHelper . ' ' . $ref,
                    'DIARIO',
                    $periodo
                );

                $mensajeContable = ' Asiento de reversión AST-' . str_pad($numAsiento, 5, '0', STR_PAD_LEFT) . ' generado.';

            } catch (\Throwable $e) {
                $mensajeContable = ' (Nota: no se pudo crear reversión contable: ' . $e->getMessage() . ')';
            }
        }

        // Bitácora
        registrar_bitacora(
            'Anulación de factura',
            'Facturas',
            'Anuló factura Nº ' . substr($factura->numero_control, -6) .
                ' por monto $' . number_format($factura->total_pagar, 2) .
                ' el ' . date('d/m/Y H:i'),
            $user_id
        );

        crear_notificacion(
            'Factura anulada',
            'Se anuló la factura Nº ' . substr($factura->numero_control, -6),
            'ver_notificacion_factura_anulada',
            base_url('facturas/' . $id),
            'warning'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Factura anulada correctamente y saldo reintegrado.' . $mensajeContable,
        ]);
    }

    // ─────────────────────────────────────────────
    //  INVALIDACIÓN DTE (reporta a MH + anula local)
    // ─────────────────────────────────────────────

    public function invalidar(int $id)
    {
        if (!tienePermiso('anular_factura')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos para anular facturas.']);
        }

        $data    = (array) $this->request->getJSON(true);
        $session = session();
        $userId  = $session->get('user_id');

        $factura = $this->facturaConReceptor($id);

        if (!$factura) {
            return $this->response->setJSON(['success' => false, 'message' => 'Factura no encontrada.']);
        }

        if (!$this->puedeAccederFactura($factura)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes acceso a esta factura.']);
        }

        if ((int) $factura->anulada === 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'La factura ya está anulada.']);
        }

        $tipoAnulacion  = (int) ($data['tipo_anulacion']  ?? 1);
        $motivo         = trim((string) ($data['motivo']          ?? ''));
        $nombreSolicita = trim((string) ($data['nombre_solicita'] ?? ''));
        $tipDocSolicita = $data['tip_doc_solicita'] ?? '13';
        $numDocSolicita = trim((string) ($data['num_doc_solicita'] ?? ''));

        if ($tipoAnulacion === 3 && $motivo === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'El motivo es obligatorio para el tipo de anulación "Otro".']);
        }

        $ambiente      = env('hacienda.env', '00');
        $modoLocal     = ($ambiente === '03');
        $esTradicional = (($factura->clase ?? '') === 'TRADICIONAL');

        // ── 1. REPORTAR A HACIENDA ──────────────────────────────────────────
        // Las facturas tradicionales (pre-electrónicas) no tienen DTE en Hacienda.
        if (!$modoLocal && !$esTradicional) {
            try {
                $emisor = (new EmisorModel())->first();
                if (!$emisor) {
                    throw new \RuntimeException('No hay datos de emisor configurados.');
                }

                $fechaSv = new \DateTimeImmutable('now', new \DateTimeZone('America/El_Salvador'));
                $codigoGenAnul = strtoupper($this->_generarUuid());
                $codigoGenR    = ($tipoAnulacion !== 2) ? strtoupper($this->_generarUuid()) : null;

                $invalidacion = [
                    'identificacion' => [
                        'version'          => 2,
                        'ambiente'         => $ambiente,
                        'codigoGeneracion' => $codigoGenAnul,
                        'fecAnula'         => $fechaSv->format('Y-m-d'),
                        'horAnula'         => $fechaSv->format('H:i:s'),
                    ],
                    'emisor' => [
                        'nit'                 => preg_replace('/[^0-9]/', '', $emisor->nit),
                        'nombre'              => $emisor->nombre,
                        'tipoEstablecimiento' => '02',
                        'nomEstablecimiento'  => $emisor->nombre_comercial ?: null,
                        'codEstableMH'        => $emisor->cod_estable_mh  ?: null,
                        'codEstable'          => $emisor->cod_estable      ?: null,
                        'codPuntoVentaMH'     => $emisor->cod_punto_venta_mh ?: null,
                        'codPuntoVenta'       => $emisor->cod_punto_venta  ?: null,
                        'telefono'            => preg_replace('/[^0-9]/', '', $emisor->telefono),
                        'correo'              => $emisor->correo,
                    ],
                    'documento' => [
                        'tipoDte'           => $factura->tipo_dte,
                        'codigoGeneracion'  => strtoupper($factura->codigo_generacion),
                        'selloRecibido'     => $factura->sello_recibido,
                        'numeroControl'     => $factura->numero_control,
                        'fecEmi'            => $factura->fecha_emision,
                        'montoIva'          => round((float) ($factura->total_iva ?? 0), 2),
                        'codigoGeneracionR' => $codigoGenR,
                        'tipoDocumento'     => $this->_mapTipoDocInvalidacion($factura->cliente_tipo_documento ?? '13'),
                        'numDocumento'      => $factura->cliente_documento ?? $numDocSolicita,
                        'nombre'            => $factura->cliente ?? $nombreSolicita,
                        'telefono'          => ($t = preg_replace('/[^0-9]/', '', $factura->cliente_telefono ?? '')) ? $t : null,
                        'correo'            => $factura->cliente_correo ?? '',
                    ],
                    'motivo' => [
                        'tipoAnulacion'     => $tipoAnulacion,
                        'motivoAnulacion'   => $motivo !== '' ? $motivo : null,
                        'nombreResponsable' => $emisor->nombre,
                        'tipDocResponsable' => '36',
                        'numDocResponsable' => preg_replace('/[^0-9]/', '', $emisor->nit),
                        'nombreSolicita'    => $nombreSolicita ?: ($factura->cliente ?? ''),
                        'tipDocSolicita'    => $tipDocSolicita,
                        'numDocSolicita'    => $numDocSolicita,
                    ],
                ];

                $signer  = new DteSignerService();
                $firmado = $signer->firmar($invalidacion);

                $payloadMH = [
                    'ambiente'         => $ambiente,
                    'idEnvio'          => 1,
                    'version'          => 2,
                    'documento'        => base64_encode(json_encode($firmado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                    'codigoGeneracion' => $codigoGenAnul,
                ];

                $api      = new HaciendaApiService();
                $response = $api->post('fesv/anulaciondte', $payloadMH);

                if ($response['http_code'] !== 200) {
                    $err = $response['error'] ?? json_encode($response['body'] ?? []);
                    return $this->response->setJSON(['success' => false, 'message' => "Error al comunicar con Hacienda: {$err}"]);
                }

                $estadoMh = strtolower($response['body']['estado'] ?? 'error');
                if ($estadoMh === 'rechazado') {
                    $msgMh = $response['body']['descripcionMsg'] ?? 'Hacienda rechazó la invalidación.';
                    return $this->response->setJSON(['success' => false, 'message' => "Hacienda rechazó la invalidación: {$msgMh}"]);
                }

            } catch (\Throwable $e) {
                return $this->response->setJSON(['success' => false, 'message' => 'Error al enviar invalidación a Hacienda: ' . $e->getMessage()]);
            }
        }

        // ── 2. OPERACIONES LOCALES ───────────────────────────────────────────
        $facturaModel      = new FacturaHeadModel();
        $pagosDetailsModel = new PagosDetailsModel();
        $pagosHeadModel    = new PagosHeadModel();
        $transactionModel  = new TransactionModel();
        $accountModel      = new AccountModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $facturaModel->update($id, [
            'anulada'         => 1,
            'saldo'           => 0,
            'anulada_por'     => $userId,
            'fecha_anulacion' => date('Y-m-d H:i:s'),
        ]);

        foreach ($pagosDetailsModel->where('factura_id', $id)->where('anulado', 0)->findAll() as $detalle) {
            $pago = $pagosHeadModel->find($detalle->pago_id);
            if (!$pago) continue;

            $accountId = $pago->numero_cuenta_bancaria ?? 1;
            $cuenta    = $accountModel->find($accountId) ?? $accountModel->find(1);
            if (!$cuenta) continue;

            $accountModel->update($accountId, ['balance' => (float) $cuenta->balance - $detalle->monto]);
            $transactionModel->addSalida($accountId, $detalle->monto,
                'Reversión por anulación de factura', 'Factura Nº ' . substr($factura->numero_control, -6), $detalle->pago_id);
            $pagosDetailsModel->update($detalle->id, ['anulado' => 1, 'anulado_at' => date('Y-m-d H:i:s'), 'anulado_by' => $userId]);
        }

        $db->table('pedidos_head')->where('factura_id', $id)->where('anulada', 0)->update(['factura_id' => null]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al anular la factura en la base de datos.']);
        }

        // ── 3. REVERSIÓN CONTABLE ────────────────────────────────────────────
        $mensajeContable = '';
        if (in_array($factura->tipo_dte, ['01', '03'])) {
            try {
                helper('cont_ventas');
                $contHeadModel    = new \App\Models\ContAsientosHeadModel();
                $contDetalleModel = new \App\Models\ContAsientosDetalleModel();
                $periodosModel    = new \App\Models\ContPeriodosModel();

                $periodo = $periodosModel->abrirObtenerPeriodo((int) date('Y'), (int) date('n'));
                if (!$periodo) {
                    throw new \Exception('El período contable de ' . date('m/Y') . ' está cerrado.');
                }

                $ref           = substr($factura->numero_control, -6);
                $tipoDteHelper = $factura->tipo_dte === '03' ? 'CCF' : 'FAC';
                $monto         = $factura->tipo_dte === '03' ? (float) $factura->total_gravada : (float) $factura->monto_total_operacion;
                $retencion     = (float) ($factura->iva_rete1 ?? 0);

                if ($monto <= 0) throw new \Exception("Monto inválido ({$monto}).");

                $cxcOverrideId = null;
                if (!empty($factura->receptor_id)) {
                    $cli = (new \App\Models\ClienteModel())->select('id, cuenta_contable_id')->find((int) $factura->receptor_id);
                    if ($cli && !empty($cli->cuenta_contable_id)) $cxcOverrideId = (int) $cli->cuenta_contable_id;
                }

                $resultado = cont_asiento_venta_json($tipoDteHelper, $monto, $retencion, $ref, $periodo->id,
                    date('Y-m-d'), 'REVERSA: Anulación ' . $tipoDteHelper . ' ' . $ref, null, $cxcOverrideId);

                if (!$resultado['ok']) throw new \Exception('Error al calcular líneas: ' . implode(', ', $resultado['errores']));

                $lineasReversa = array_map(fn ($l) => [
                    'cuenta_id'   => $l['cuenta_id'],
                    'descripcion' => 'REVERSA: ' . $l['descripcion'],
                    'debe'        => $l['haber'],
                    'haber'       => $l['debe'],
                ], $resultado['payload']['lineas']);

                $numAsiento = $contHeadModel->getSiguienteNumero();
                $tipoPartId = !empty($resultado['payload']['tipo_partida_id']) ? (int) $resultado['payload']['tipo_partida_id'] : null;

                $reversaId = $contHeadModel->insert([
                    'numero_asiento'     => $numAsiento,
                    'numero_partida'     => $tipoPartId ? $contHeadModel->getSiguienteNumeroPartida($tipoPartId, (int) date('Y')) : null,
                    'fecha'              => date('Y-m-d'),
                    'descripcion'        => 'REVERSA: Anulación ' . $tipoDteHelper . ' ' . $ref,
                    'tipo'               => 'DIARIO',
                    'tipo_partida_id'    => $tipoPartId,
                    'estado'             => 'APROBADO',
                    'periodo_id'         => $periodo->id,
                    'total_debe'         => round(array_sum(array_column($lineasReversa, 'debe')), 2),
                    'total_haber'        => round(array_sum(array_column($lineasReversa, 'haber')), 2),
                    'referencia'         => 'Anulación ' . $ref,
                    'documento_tipo'     => 'factura',
                    'documento_id'       => (int) $id,
                    'usuario_id'         => $userId,
                    'usuario_aprueba_id' => $userId,
                    'fecha_aprobacion'   => date('Y-m-d H:i:s'),
                ]);

                if (!$reversaId) throw new \Exception('No se pudo insertar el asiento de reversión.');

                foreach ($lineasReversa as $i => $l) {
                    $contDetalleModel->insert([
                        'asiento_id'  => $reversaId,
                        'cuenta_id'   => $l['cuenta_id'],
                        'descripcion' => $l['descripcion'],
                        'debe'        => $l['debe'],
                        'haber'       => $l['haber'],
                        'orden'       => $i + 1,
                    ]);
                }

                $contHeadModel->aprobarConSaldos($reversaId, $lineasReversa, $periodo->id, date('Y-m-d'),
                    'REVERSA: Anulación ' . $tipoDteHelper . ' ' . $ref, 'DIARIO', $periodo);

                $mensajeContable = ' Asiento AST-' . str_pad($numAsiento, 5, '0', STR_PAD_LEFT) . ' generado.';

            } catch (\Throwable $e) {
                $mensajeContable = ' (Nota: reversión contable no generada: ' . $e->getMessage() . ')';
            }
        }

        registrar_bitacora('Anulación de factura', 'Facturas',
            'Anuló factura Nº ' . substr($factura->numero_control, -6) .
            ' por $' . number_format($factura->total_pagar, 2) . ' el ' . date('d/m/Y H:i'), $userId);

        crear_notificacion('Factura anulada', 'Se anuló la factura Nº ' . substr($factura->numero_control, -6),
            'ver_notificacion_factura_anulada', base_url('facturas/' . $id), 'warning');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Factura anulada correctamente.' . $mensajeContable,
        ]);
    }

    private function _mapTipoDocInvalidacion(?string $tipo): string
    {
        return match (strtoupper((string) $tipo)) {
            '36', 'NIT'              => '36',
            '13', 'DUI'              => '13',
            '02', 'CARNÉ RESIDENTE'  => '02',
            '03', 'PASAP', 'PASAPORTE' => '03',
            default                  => '13',
        };
    }

    private function _generarUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
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
        if (!puedeVerDocumentosTodosVendedores()) {
            $sellerScope = vendedorUsuarioActual();
            if (!$sellerScope || (int)$factura->vendedor_id !== (int)$sellerScope) {
                return 'Factura no encontrada';
            }
        }

        $detalles = $facturaDetalleModel
            ->where('factura_id', $id)
            ->findAll();

        // TRAER PAGOS APLICADOS A ESTA FACTURA
        $pagos = $pagosDetalleModel
            ->distinct()
            ->select('pd.monto,
                pd.observaciones,
                ph.fecha_pago,
                pd.anulado,
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
        if (!puedeVerDocumentosTodosVendedores()) {
            $factura = (new FacturaHeadModel())->find((int)$facturaId);
            $sellerScope = vendedorUsuarioActual();
            if (!$factura || !$sellerScope || (int)$factura->vendedor_id !== (int)$sellerScope) {
                return $this->response->setJSON([
                    'tiene_pagos' => false,
                    'pagos' => []
                ]);
            }
        }

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
        if (!puedeVerDocumentosTodosVendedores()) {
            $sellerScope = vendedorUsuarioActual();
            if (!$sellerScope || (int)$factura->vendedor_id !== (int)$sellerScope) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No tienes acceso a esta factura.'
                ]);
            }
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
