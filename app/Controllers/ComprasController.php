<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProveedorModel;
use App\Models\CompraHeadModel;
use App\Models\CompraDetalleModel;
use App\Models\ProductoModel;
use App\Models\ProductoMovimientoModel;
use App\Models\PagosComprasDetallesModel;

class ComprasController extends BaseController
{

    public function index()
    {
        $chk = requerirPermiso('ver_compras');
        if ($chk !== true) return $chk;

        $model = new CompraHeadModel();

        $perPage = (int)($this->request->getGet('per_page') ?? 25);
        if (!in_array($perPage, [10, 15, 25, 50, 100, 99999])) $perPage = 25;

        $query = $model
            ->select('compras_head.*, proveedores.nombre AS proveedor_nombre')
            ->join('proveedores', 'proveedores.id = compras_head.proveedor_id', 'left');

        // FILTROS
        if ($proveedorId = $this->request->getGet('proveedor_id')) {
            $query->where('compras_head.proveedor_id', $proveedorId);
        }

        if ($tipoDte = $this->request->getGet('tipo_dte')) {
            $query->where('compras_head.tipo_dte', $tipoDte);
        }

        if ($fecha = $this->request->getGet('fecha')) {
            $query->where('DATE(compras_head.fecha_emision)', $fecha);
        }

        if ($numero = $this->request->getGet('numero_compra')) {
            $query->like('compras_head.numero_control', $numero, 'before'); // 'before' = %1036
        }

        if ($estado = $this->request->getGet('estado')) {
            switch ($estado) {
                case 'activa':
                    $query->where('compras_head.anulada', 0)
                        ->where('compras_head.saldo >', 0)
                        ->where('compras_head.saldo = compras_head.total_pagar');
                    break;
                case 'parcial':
                    $query->where('compras_head.anulada', 0)
                        ->where('compras_head.saldo >', 0)
                        ->where('compras_head.saldo < compras_head.total_pagar');
                    break;
                case 'pagada':
                    $query->where('compras_head.anulada', 0)
                        ->where('compras_head.saldo', 0);
                    break;
                case 'anulada':
                    $query->where('compras_head.anulada', 1);
                    break;
            }
        }

        $query->orderBy('compras_head.fecha_emision', 'DESC')
            ->orderBy('compras_head.id', 'DESC');

        $compras = $query->paginate($perPage);
        $pager   = $model->pager;

        // RESPUESTA AJAX
        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {

            $tbody = view('compras/_tbody', ['compras' => $compras]);
            $pagerHtml = $pager->links('default', 'bootstrap_full');

            return $this->response->setJSON([
                'tbody' => $tbody,
                'pager' => $pagerHtml,
            ]);
        }

        return view('compras/index', [
            'compras' => $compras,
            'pager'   => $pager,
        ]);
    }
    public function new()
    {
        $chk = requerirPermiso('ingresar_compras');
        if ($chk !== true) return $chk;

        return view('compras/new');
    }
    public function carga()
    {
        $chk = requerirPermiso('cargar_compras_json');
        if ($chk !== true) return $chk;

        $emisorModel = new \App\Models\EmisorModel();
        $emisor = $emisorModel->first();

        // limpiar por si acaso
        $emisor->nrc = preg_replace('/[^0-9]/', '', $emisor->nrc ?? '');
        $emisor->nit = preg_replace('/[^0-9]/', '', $emisor->nit ?? '');

        return view('compras/carga_procesado', [
            'emisor' => $emisor
        ]);
    }
    public function procesarCarga()
    {
        $files = $this->request->getFiles();

        if (!isset($files['archivos'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron archivos'
            ]);
        }

        $proveedorModel     = new ProveedorModel();
        $compraHeadModel    = new CompraHeadModel();
        $compraDetalleModel = new CompraDetalleModel();
        $productoModel      = new ProductoModel();
        $movModel           = new ProductoMovimientoModel();

        $db = \Config\Database::connect();

        $procesadas = 0;
        $saltadas   = [];
        $errores    = [];

        foreach ($files['archivos'] as $file) {

            if (!$file->isValid()) continue;

            $contenido = file_get_contents($file->getTempName());
            $json      = json_decode($contenido, true);

            if (!$json || json_last_error() !== JSON_ERROR_NONE) {
                $errores[] = $file->getClientFilename() . ': JSON inválido';
                continue;
            }

            $numeroControl = $json['identificacion']['numeroControl'] ?? null;

            if (!$numeroControl) {
                $errores[] = $file->getClientFilename() . ': sin número de control';
                continue;
            }

            $existe = $compraHeadModel->where('numero_control', $numeroControl)->first();

            if ($existe) {
                $saltadas[] = $numeroControl;
                continue;
            }

            // Cada archivo en su propia transacción independiente
            $db->transStart();

            try {

                // PROVEEDOR
                $proveedorId = null;

                if (!empty($json['emisor'])) {

                    $emisor   = $json['emisor'];
                    $nombre   = trim($emisor['nombre'] ?? '');
                    $telefono = $emisor['telefono'] ?? null;
                    $correo   = $emisor['correo'] ?? null;

                    $direccion = null;
                    if (!empty($emisor['direccion'])) {
                        $direccion = implode(', ', array_filter([
                            $emisor['direccion']['departamento'] ?? null,
                            $emisor['direccion']['municipio']    ?? null,
                            $emisor['direccion']['complemento']  ?? null,
                        ]));
                    }

                    if ($nombre) {
                        $proveedor = $proveedorModel->where('nombre', $nombre)->first();

                        if (!$proveedor) {
                            $proveedorId = $proveedorModel->insert([
                                'nombre'    => $nombre,
                                'telefono'  => $telefono,
                                'email'     => $correo,
                                'direccion' => $direccion,
                            ]);
                        } else {
                            $proveedorId = $proveedor->id;
                            $proveedorModel->update($proveedorId, [
                                'telefono'  => $telefono ?? $proveedor->telefono,
                                'email'     => $correo   ?? $proveedor->email,
                                'direccion' => $direccion ?? $proveedor->direccion,
                            ]);
                        }
                    }
                }

                // TOTALES
                $total = $json['resumen']['totalPagar'] ?? $json['resumen']['montoTotalOperacion'] ?? 0;
                $totalGravada = $json['resumen']['totalGravada'] ?? 0;

                $iva = 0;
                foreach (($json['resumen']['tributos'] ?? []) as $t) {
                    if (($t['codigo'] ?? null) == '20') {
                        $iva = (float)$t['valor'];
                    }
                }

                $condicion = (int)($json['resumen']['condicionOperacion'] ?? 1);
                $plazo     = $condicion === 2 ? 30 : null;
                $tipoDte   = $json['identificacion']['tipoDte'] ?? null;
                $saldo     = ($tipoDte === '05') ? 0 : $total;

                // INSERT HEAD
                $dataHead = [
                    'numero_control'    => $numeroControl,
                    'codigo_generacion' => $json['identificacion']['codigoGeneracion'] ?? null,
                    'fecha_emision'     => $json['identificacion']['fecEmi'] ?? null,
                    'sello_recibido'    => $json['identificacion']['selloRecibido'] ?? null,
                    'tipo_dte'          => $tipoDte,
                    'proveedor_id'      => $proveedorId,
                    'total_gravada'     => $totalGravada,
                    'sub_total'         => $json['resumen']['subTotal'] ?? 0,
                    'total_iva'         => $iva,
                    'monto_total_operacion' => $json['resumen']['montoTotalOperacion'] ?? 0,
                    'total_pagar'       => $total,
                    'condicion_operacion' => $condicion,
                    'plazo_credito'     => $plazo,
                    'iva_rete1'         => $json['resumen']['ivaRete1'] ?? 0,
                    'saldo'             => $saldo,
                    'codigo_generacion_relacionado' => $json['documentoRelacionado'][0]['numeroDocumento'] ?? null,
                ];

                if (!$compraHeadModel->insert($dataHead)) {
                    throw new \RuntimeException('Error insertando encabezado: ' . implode(', ', $compraHeadModel->errors()));
                }

                $compraId = $compraHeadModel->getInsertID();

                // DETALLES
                $ivaTotal       = $iva;
                $totalGravadaDoc = (float)$totalGravada;

                foreach (($json['cuerpoDocumento'] ?? []) as $item) {

                    $codigo      = strtoupper(preg_replace('/\s+/', '', trim($item['codigo'] ?? '')));
                    $descripcion = strtok(trim($item['descripcion'] ?? ''), "\n");

                    $producto = $codigo
                        ? $productoModel->where('UPPER(codigo)', $codigo)->first()
                        : null;

                    if (!$producto) {
                        $productoId = $productoModel->insert([
                            'codigo'      => $codigo ?: null,
                            'descripcion' => $descripcion ?: 'SIN DESCRIPCIÓN',
                            'activo'      => 1,
                            'tipo'        => 'AUTO'
                        ]);
                        $producto = $productoModel->find($productoId);
                    }

                    if (empty($producto) || empty($producto->id)) {
                        throw new \RuntimeException('Producto inválido: ' . $codigo);
                    }

                    $ventaGravada  = (float)($item['ventaGravada'] ?? 0);
                    $cantidadNueva = (float)($item['cantidad']     ?? 1);

                    $ivaItem = (float)($item['ivaItem'] ?? 0);
                    if ($ivaItem == 0 && $tipoDte === '03' && $totalGravadaDoc > 0) {
                        $ivaItem = round($ivaTotal * ($ventaGravada / $totalGravadaDoc), 2);
                    }

                    if ($tipoDte === '03') {
                        $costoConIva      = $ventaGravada + $ivaItem;
                        $costoUnitarioMov = $cantidadNueva > 0 ? $costoConIva / $cantidadNueva : 0;
                    } else {
                        $costoConIva      = $ventaGravada;
                        $costoUnitarioMov = $cantidadNueva > 0 ? $costoConIva / $cantidadNueva : 0;
                    }

                    $detalle = [
                        'compra_id'       => $compraId,
                        'num_item'        => $item['numItem']      ?? null,
                        'tipo_item'       => $item['tipoItem']     ?? null,
                        'codigo'          => $codigo,
                        'descripcion'     => $descripcion,
                        'cantidad'        => (float)($item['cantidad']     ?? 0),
                        'unidad_medida'   => $item['uniMedida']    ?? null,
                        'precio_unitario' => (float)($item['precioUni']    ?? 0),
                        'venta_gravada'   => (float)($item['ventaGravada'] ?? 0),
                        'monto_descuento' => (float)($item['montoDescu']   ?? 0),
                        'iva_item'        => (float)($item['ivaItem']      ?? 0),
                        'producto_id'     => $producto->id
                    ];

                    if (!$compraDetalleModel->insert($detalle)) {
                        throw new \RuntimeException('Error insertando detalle: ' . implode(', ', $compraDetalleModel->errors()));
                    }

                    $stock = $movModel
                        ->select('
                            SUM(CASE WHEN tipo_movimiento = "ENTRADA" THEN cantidad ELSE 0 END) -
                            SUM(CASE WHEN tipo_movimiento = "SALIDA"  THEN cantidad ELSE 0 END)
                            as stock
                        ')
                        ->where('producto_id', $producto->id)
                        ->first();

                    $stockActual = (float)($stock->stock ?? 0);
                    $costoActual = (float)($producto->costo_promedio ?? 0);

                    $nuevoCosto = $stockActual > 0
                        ? (($stockActual * $costoActual) + ($cantidadNueva * $costoUnitarioMov)) / ($stockActual + $cantidadNueva)
                        : $costoUnitarioMov;

                    $movData = [
                        'producto_id'     => $producto->id,
                        'tipo_movimiento' => 'ENTRADA',
                        'cantidad'        => $cantidadNueva,
                        'costo_unitario'  => $costoUnitarioMov,
                        'referencia_tipo' => 'compra',
                        'referencia_id'   => $compraId,
                    ];

                    if (!$movModel->insert($movData)) {
                        throw new \RuntimeException('Error insertando movimiento');
                    }

                    $productoModel->update($producto->id, ['costo_promedio' => $nuevoCosto]);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    $error = $db->error();
                    $errores[] = $numeroControl . ': error BD (' . ($error['message'] ?? 'desconocido') . ')';
                    log_message('error', 'Error BD procesando compra ' . $numeroControl . ': ' . ($error['message'] ?? ''));
                } else {
                    $procesadas++;
                }

            } catch (\Throwable $e) {

                $db->transRollback();
                $errores[] = $numeroControl . ': ' . $e->getMessage();
                log_message('error', 'Excepción procesando compra ' . $numeroControl . ': ' . $e->getMessage());
            }
        }

        return $this->response->setJSON([
            'success'  => true,
            'total'    => $procesadas,
            'saltadas' => $saltadas,
            'errores'  => $errores,
            'message'  => "Compras procesadas correctamente",
        ]);
    }
    public function validarProductos()
    {
        $data = $this->request->getJSON(true);
        $productos = $data['productos'] ?? [];

        $productoModel = new ProductoModel();

        $noExistentes = [];

        foreach ($productos as $p) {

            $codigo = trim($p['codigo'] ?? '');

            // 🔥 limpiar código (por seguridad)
            $codigo = preg_replace('/\s+/', '', $codigo);
            $codigo = strtoupper($codigo);

            if (!$codigo) {
                // si no trae código, lo consideras inválido
                $noExistentes[] = 'SIN CÓDIGO';
                continue;
            }

            $producto = $productoModel
                ->where('UPPER(codigo)', $codigo)
                ->first();

            if (!$producto) {
                $noExistentes[] = $codigo;
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'no_existen' => $noExistentes
        ]);
    }
    public function show($id)
    {
        $compraHeadModel = new CompraHeadModel();
        $compraDetalleModel = new CompraDetalleModel();

        // 🔥 TRAER COMPRA + PROVEEDOR
        $compra = $compraHeadModel
            ->select('compras_head.*, proveedores.nombre as proveedor_nombre')
            ->join('proveedores', 'proveedores.id = compras_head.proveedor_id', 'left')
            ->where('compras_head.id', $id)
            ->first();

        if (!$compra) {
            return redirect()->to(base_url('purchases'))
                ->with('error', 'Compra no encontrada');
        }

        // 🔥 DETALLES
        $detalles = $compraDetalleModel
            ->where('compra_id', $id)
            ->orderBy('num_item', 'ASC')
            ->findAll();

        // 🔥 DATA PARA VIEW
        $data = [
            'compra'   => $compra,
            'detalles' => $detalles,
        ];

        return view('compras/show', $data);
    }
    public function delete($id)
    {
        $compraHeadModel = new \App\Models\CompraHeadModel();
        $compraDetalleModel = new \App\Models\CompraDetalleModel();
        $movModel = new \App\Models\ProductoMovimientoModel();
        $productoModel = new \App\Models\ProductoModel();

        $db = \Config\Database::connect();
        $db->transStart();

        // 🔍 VALIDAR EXISTENCIA
        $compra = $compraHeadModel->find($id);

        if (!$compra) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Compra no encontrada'
            ]);
        }

        // 🔥 OBTENER PRODUCTOS AFECTADOS
        $detalles = $compraDetalleModel
            ->where('compra_id', $id)
            ->findAll();

        $productosIds = [];

        foreach ($detalles as $d) {
            if (!empty($d->producto_id)) {
                $productosIds[] = $d->producto_id;
            }
        }

        $productosIds = array_unique($productosIds);

        // ❌ BORRAR MOVIMIENTOS
        $movModel
            ->where('referencia_tipo', 'COMPRA')
            ->where('referencia_id', $id)
            ->delete();

        // ❌ BORRAR DETALLES
        $compraDetalleModel
            ->where('compra_id', $id)
            ->delete();

        // ❌ BORRAR HEAD
        if (!$compraHeadModel->delete($id)) {

            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error eliminando compra'
            ]);
        }

        // =============================
        // 🔥 RECALCULAR COSTOS
        // =============================

        foreach ($productosIds as $productoId) {

            $movimientos = $movModel
                ->where('producto_id', $productoId)
                ->orderBy('id', 'ASC')
                ->findAll();

            $stock = 0;
            $costoPromedio = 0;

            foreach ($movimientos as $m) {

                if ($m->tipo_movimiento === 'ENTRADA') {

                    $cantidad = (float)$m->cantidad;
                    $costo = (float)$m->costo_unitario;

                    if ($stock > 0) {

                        $costoPromedio = (
                            ($stock * $costoPromedio) +
                            ($cantidad * $costo)
                        ) / ($stock + $cantidad);
                    } else {

                        $costoPromedio = $costo;
                    }

                    $stock += $cantidad;
                } elseif ($m->tipo_movimiento === 'SALIDA') {

                    $stock -= (float)$m->cantidad;

                    // 🔥 opcional: evitar negativos
                    if ($stock < 0) $stock = 0;
                }
            }

            // 🔥 ACTUALIZAR PRODUCTO
            $productoModel->update($productoId, [
                'costo_promedio' => $costoPromedio
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en la base de datos'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Compra eliminada y costos recalculados correctamente'
        ]);
    }
    // =========================================================
    // CARGA MANUAL (Facturas tradicionales via Excel)
    // =========================================================

    public function cargaManual()
    {
        $chk = requerirPermiso('cargar_compras_manual');
        if ($chk !== true) return $chk;

        return view('compras/carga_manual');
    }

    public function descargarPlantilla()
    {
        $chk = requerirPermiso('cargar_compras_manual');
        if ($chk !== true) return $chk;

        $proveedorModel = new ProveedorModel();
        $productoModel  = new ProductoModel();

        $proveedores = $proveedorModel->orderBy('nombre')->findAll();
        $productos   = $productoModel->where('activo', 1)->orderBy('descripcion')->findAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()->setTitle('Plantilla Compras Tradicionales');

        // ── Sheet 1: Facturas ──────────────────────────────────
        $sh = $spreadsheet->getActiveSheet();
        $sh->setTitle('Facturas');

        $colWidths = [
            'A' => 6,  'B' => 8,  'C' => 13, 'D' => 16, 'E' => 36,
            'F' => 16, 'G' => 10, 'H' => 14, 'I' => 12, 'J' => 12,
            'K' => 12, 'L' => 14, 'M' => 10, 'N' => 40,
            'O' => 13, 'P' => 15, 'Q' => 11,
        ];
        foreach ($colWidths as $col => $w) {
            $sh->getColumnDimension($col)->setWidth($w);
        }

        // Headers row 1
        $headers = [
            'A1' => '#', 'B1' => 'TIPO', 'C1' => 'FECHA',
            'D1' => 'CORRELATIVO', 'E1' => 'PROVEEDOR', 'F1' => 'NIT/NRC',
            'G1' => 'TIPO DOC', 'H1' => 'TOTAL $', 'I1' => 'IVA $',
            'J1' => 'CONDICIÓN', 'K1' => '∑ LÍNEAS', 'L1' => 'ESTADO',
            'M1' => 'CANTIDAD', 'N1' => 'DESCRIPCIÓN',
            'O1' => 'PRECIO UNIT', 'P1' => 'VENTA GRAVADA', 'Q1' => 'DESCUENTO',
        ];
        foreach ($headers as $coord => $val) {
            $sh->setCellValue($coord, $val);
        }
        $sh->getStyle('A1:Q1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F3864'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sh->getRowDimension(1)->setRowHeight(22);
        $sh->freezePane('A2');

        // Sub-header grouping labels (merged row, informational)
        $sh->getStyle('C1:J1')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
        ]);
        $sh->getStyle('M1:Q1')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
        ]);

        // ── Sample data ──
        $firstProv  = !empty($proveedores) ? $proveedores[0]->nombre   : 'PROVEEDOR EJEMPLO';
        $firstProd  = !empty($productos)   ? $productos[0]->descripcion : 'PRODUCTO EJEMPLO';
        $secondProd = (count($productos) > 1) ? $productos[1]->descripcion : 'OTRO PRODUCTO';

        $sh->setCellValue('A2', 1); $sh->setCellValue('B2', 'CAB');
        $sh->setCellValue('C2', date('Y-m-d'));
        $sh->setCellValue('D2', 'F-00001');
        $sh->setCellValue('E2', $firstProv);
        $sh->setCellValue('G2', 'CCF');
        $sh->setCellValue('H2', 1130.00);
        $sh->setCellValue('I2', 130.00);
        $sh->setCellValue('J2', 'Contado');

        $sh->setCellValue('A3', 1); $sh->setCellValue('B3', 'DET');
        $sh->setCellValue('M3', 10); $sh->setCellValue('N3', $firstProd);
        $sh->setCellValue('O3', 100.00); $sh->setCellValue('P3', 1000.00); $sh->setCellValue('Q3', 0.00);

        $sh->setCellValue('A4', 1); $sh->setCellValue('B4', 'DET');
        $sh->setCellValue('M4', 1); $sh->setCellValue('N4', $secondProd);
        $sh->setCellValue('O4', 130.00); $sh->setCellValue('P4', 130.00); $sh->setCellValue('Q4', 0.00);

        // ── Formulas K (∑ lines) and L (estado) for rows 2-1001 ──
        $kData = [];
        $lData = [];
        for ($r = 2; $r <= 1001; $r++) {
            $kData[] = ['=IF($B'.$r.'="CAB",SUMPRODUCT(($A$2:$A$1001=A'.$r.')*($B$2:$B$1001="DET")*($P$2:$P$1001)),"")'];
            $lData[] = ['=IF($H'.$r.'="","",IF(ABS(($H'.$r.'-$I'.$r.')-K'.$r.')<=0.02,"✓ OK","⚠ NO CUADRA"))'];
        }
        $sh->fromArray($kData, null, 'K2');
        $sh->fromArray($lData, null, 'L2');

        // ── Data validation: Proveedor (col E) ──
        $provCount = min(count($proveedores), 999);
        if ($provCount > 0) {
            $vProv = $sh->getCell('E2')->getDataValidation();
            $vProv->setSqref('E2:E1001');
            $vProv->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $vProv->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $vProv->setAllowBlank(true);
            $vProv->setShowDropDown(false);
            $vProv->setShowErrorMessage(true);
            $vProv->setErrorTitle('Proveedor nuevo');
            $vProv->setError('Si es nuevo, escríbalo manualmente y complete la columna NIT/NRC.');
            $vProv->setFormula1('Datos!$A$2:$A$' . ($provCount + 1));
        }

        // ── Data validation: Tipo Doc (col G) ──
        $vTipo = $sh->getCell('G2')->getDataValidation();
        $vTipo->setSqref('G2:G1001');
        $vTipo->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $vTipo->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $vTipo->setAllowBlank(false);
        $vTipo->setShowDropDown(false);
        $vTipo->setFormula1('"CCF,Factura"');

        // ── Data validation: Condición (col J) ──
        $vCond = $sh->getCell('J2')->getDataValidation();
        $vCond->setSqref('J2:J1001');
        $vCond->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $vCond->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $vCond->setAllowBlank(false);
        $vCond->setShowDropDown(false);
        $vCond->setFormula1('"Contado,Crédito"');

        // ── Data validation: Descripción producto (col N) ──
        $prodCount = min(count($productos), 499);
        if ($prodCount > 0) {
            $vProd = $sh->getCell('N2')->getDataValidation();
            $vProd->setSqref('N2:N1001');
            $vProd->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $vProd->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $vProd->setAllowBlank(true);
            $vProd->setShowDropDown(false);
            $vProd->setShowErrorMessage(true);
            $vProd->setErrorTitle('Producto nuevo');
            $vProd->setError('Se creará automáticamente como producto nuevo.');
            $vProd->setFormula1('Datos!$B$2:$B$' . ($prodCount + 1));
        }

        // ── Conditional formatting (applied to entire row A:Q) ──
        $cRed = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $cRed->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $cRed->addCondition('=AND($B2="CAB",$H2<>"",ABS(($H2-$I2)-$K2)>0.02)');
        $cRed->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFC7CE');
        $cRed->getStyle()->getFont()->getColor()->setARGB('FF9C0006');

        $cGreen = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $cGreen->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $cGreen->addCondition('=AND($B2="CAB",$H2<>"",ABS(($H2-$I2)-$K2)<=0.02)');
        $cGreen->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFC6EFCE');
        $cGreen->getStyle()->getFont()->getColor()->setARGB('FF276221');

        $cBlue = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $cBlue->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $cBlue->addCondition('=$B2="DET"');
        $cBlue->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F0FE');

        $sh->getStyle('A2:Q1001')->setConditionalStyles([$cRed, $cGreen, $cBlue]);

        // ── Sheet 2: Datos (hidden) ──
        $shD = $spreadsheet->createSheet();
        $shD->setTitle('Datos');
        $shD->getColumnDimension('A')->setWidth(40);
        $shD->getColumnDimension('B')->setWidth(40);
        $shD->setCellValue('A1', 'Proveedores');
        $shD->setCellValue('B1', 'Productos');

        foreach ($proveedores as $i => $prov) {
            $shD->setCellValue('A' . ($i + 2), $prov->nombre);
        }
        $prodMax = min(count($productos), 499);
        for ($i = 0; $i < $prodMax; $i++) {
            $shD->setCellValue('B' . ($i + 2), $productos[$i]->descripcion);
        }
        $shD->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        $spreadsheet->setActiveSheetIndex(0);

        // ── Output ──
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'plantilla_compras_' . date('Y-m-d') . '.xlsx';

        if (ob_get_level()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function previewManual()
    {
        $chk = requerirPermiso('cargar_compras_manual');
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

                // Date parsing (serial number or string)
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

                $condRaw  = strtolower(trim((string)$sh->getCell('J' . $row)->getValue()));
                $condicion = str_contains($condRaw, 'r') ? 2 : 1; // crédito vs contado

                $facturas[$numFac] = [
                    'num_fac'          => $numFac,
                    'correlativo'      => trim((string)$sh->getCell('D' . $row)->getValue()),
                    'fecha'            => $fechaStr,
                    'proveedor_nombre' => trim((string)$sh->getCell('E' . $row)->getValue()),
                    'nit_ruc'          => trim((string)$sh->getCell('F' . $row)->getValue()),
                    'tipo_dte'         => $tipoDte,
                    'total_declarado'  => (float)$sh->getCell('H' . $row)->getValue(),
                    'iva_declarado'    => (float)$sh->getCell('I' . $row)->getValue(),
                    'condicion'        => $condicion,
                    'lineas'           => [],
                ];

            } elseif ($tipo === 'DET' && isset($facturas[$numFac])) {

                $facturas[$numFac]['lineas'][] = [
                    'cantidad'        => (float)$sh->getCell('M' . $row)->getValue(),
                    'descripcion'     => trim((string)$sh->getCell('N' . $row)->getValue()),
                    'precio_unitario' => (float)$sh->getCell('O' . $row)->getValue(),
                    'venta_gravada'   => (float)$sh->getCell('P' . $row)->getValue(),
                    'descuento'       => (float)$sh->getCell('Q' . $row)->getValue(),
                ];
            }
        }

        if (empty($facturas)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se encontraron facturas en el archivo']);
        }

        // Validate against DB
        $proveedorModel  = new ProveedorModel();
        $productoModel   = new ProductoModel();
        $compraHeadModel = new CompraHeadModel();

        $result = [];

        foreach ($facturas as $fac) {

            // Proveedor exists?
            $prov = $fac['proveedor_nombre']
                ? $proveedorModel->where('nombre', $fac['proveedor_nombre'])->first()
                : null;
            $fac['proveedor_nuevo'] = !$prov;
            $fac['proveedor_id']    = $prov ? $prov->id : null;

            // Duplicate correlativo?
            $existe = $fac['correlativo']
                ? $compraHeadModel->where('numero_control', $fac['correlativo'])->first()
                : null;
            $fac['duplicado'] = (bool)$existe;

            // Sum lines
            $sumaLineas        = array_sum(array_column($fac['lineas'], 'venta_gravada'));
            $fac['suma_lineas'] = $sumaLineas;
            $fac['cuadra']     = abs($fac['total_declarado'] - ($sumaLineas + $fac['iva_declarado'])) <= 0.02;

            // Producto checks
            $tieneNuevos = false;
            foreach ($fac['lineas'] as &$linea) {
                $prod = null;
                if ($linea['descripcion']) {
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

        return $this->response->setJSON(['success' => true, 'facturas' => $result]);
    }

    public function procesarCargaManual()
    {
        $chk = requerirPermiso('cargar_compras_manual');
        if ($chk !== true) return $chk;

        $data    = $this->request->getJSON(true);
        $facturas = $data['facturas'] ?? [];

        if (empty($facturas)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin datos']);
        }

        $proveedorModel     = new ProveedorModel();
        $compraHeadModel    = new CompraHeadModel();
        $compraDetalleModel = new CompraDetalleModel();
        $productoModel      = new ProductoModel();
        $movModel           = new ProductoMovimientoModel();

        $db = \Config\Database::connect();

        $procesadas = 0;
        $saltadas   = [];
        $errores    = [];

        foreach ($facturas as $fac) {

            $correlativo = trim($fac['correlativo'] ?? '');

            if (!$correlativo) {
                $errores[] = 'Fila sin correlativo, omitida';
                continue;
            }

            // Skip duplicate
            if ($compraHeadModel->where('numero_control', $correlativo)->first()) {
                $saltadas[] = $correlativo;
                continue;
            }

            $db->transStart();

            try {

                // PROVEEDOR
                $nombreProv = trim($fac['proveedor_nombre'] ?? '');
                $proveedorId = null;

                if ($nombreProv) {
                    $prov = $proveedorModel->where('nombre', $nombreProv)->first();

                    if (!$prov) {
                        $proveedorId = $proveedorModel->insert([
                            'nombre'    => $nombreProv,
                            'telefono'  => null,
                            'email'     => null,
                            'direccion' => trim($fac['nit_ruc'] ?? '') ?: null,
                        ]);
                    } else {
                        $proveedorId = $prov->id;
                    }
                }

                $tipoDte      = $fac['tipo_dte'] ?? '01';
                $total        = (float)($fac['total_declarado'] ?? 0);
                $iva          = (float)($fac['iva_declarado']   ?? 0);
                $sumaGravada  = array_sum(array_column($fac['lineas'], 'venta_gravada'));
                $condicion    = (int)($fac['condicion'] ?? 1);
                $plazo        = $condicion === 2 ? 30 : null;

                $dataHead = [
                    'numero_control'             => $correlativo,
                    'codigo_generacion'          => null,
                    'fecha_emision'              => $fac['fecha'] ?? null,
                    'sello_recibido'             => null,
                    'tipo_dte'                   => $tipoDte,
                    'proveedor_id'               => $proveedorId,
                    'total_gravada'              => $sumaGravada,
                    'sub_total'                  => $sumaGravada,
                    'total_iva'                  => $iva,
                    'monto_total_operacion'      => $total,
                    'total_pagar'                => $total,
                    'condicion_operacion'        => $condicion,
                    'plazo_credito'              => $plazo,
                    'iva_rete1'                  => 0,
                    'saldo'                      => $total,
                    'codigo_generacion_relacionado' => null,
                ];

                if (!$compraHeadModel->insert($dataHead)) {
                    throw new \RuntimeException('Error insertando cabecera: ' . implode(', ', $compraHeadModel->errors()));
                }

                $compraId = $compraHeadModel->getInsertID();

                // DETALLES
                foreach ($fac['lineas'] as $idx => $linea) {

                    $descripcion = trim($linea['descripcion'] ?? '');
                    if (!$descripcion) continue;

                    $ventaGravada  = (float)($linea['venta_gravada']  ?? 0);
                    $cantidadNueva = (float)($linea['cantidad']        ?? 1);
                    $precioUni     = (float)($linea['precio_unitario'] ?? 0);
                    $descuento     = (float)($linea['descuento']       ?? 0);

                    // Find or create product
                    $producto = $productoModel
                        ->where('LOWER(TRIM(descripcion))', strtolower($descripcion))
                        ->first();

                    if (!$producto) {
                        $newId    = $productoModel->insert([
                            'descripcion' => $descripcion,
                            'activo'      => 1,
                            'tipo'        => 'AUTO',
                        ]);
                        $producto = $productoModel->find($newId);
                    }

                    if (empty($producto->id)) {
                        throw new \RuntimeException('Producto inválido: ' . $descripcion);
                    }

                    // IVA item proportional
                    $ivaItem = 0;
                    if ($tipoDte === '03' && $sumaGravada > 0) {
                        $ivaItem = round($iva * ($ventaGravada / $sumaGravada), 2);
                    }

                    $costoConIva = ($tipoDte === '03') ? ($ventaGravada + $ivaItem) : $ventaGravada;
                    $costoUni    = $cantidadNueva > 0 ? $costoConIva / $cantidadNueva : 0;

                    $detalle = [
                        'compra_id'       => $compraId,
                        'num_item'        => $idx + 1,
                        'tipo_item'       => null,
                        'codigo'          => null,
                        'descripcion'     => $descripcion,
                        'cantidad'        => $cantidadNueva,
                        'unidad_medida'   => null,
                        'precio_unitario' => $precioUni,
                        'venta_gravada'   => $ventaGravada,
                        'monto_descuento' => $descuento,
                        'iva_item'        => $ivaItem,
                        'producto_id'     => $producto->id,
                    ];

                    if (!$compraDetalleModel->insert($detalle)) {
                        throw new \RuntimeException('Error en detalle: ' . implode(', ', $compraDetalleModel->errors()));
                    }

                    // Stock & costo promedio
                    $stockRow  = $movModel
                        ->select('SUM(CASE WHEN tipo_movimiento="ENTRADA" THEN cantidad ELSE 0 END) - SUM(CASE WHEN tipo_movimiento="SALIDA" THEN cantidad ELSE 0 END) as stock')
                        ->where('producto_id', $producto->id)
                        ->first();

                    $stockActual = (float)($stockRow->stock ?? 0);
                    $costoActual = (float)($producto->costo_promedio ?? 0);

                    $nuevoCosto = $stockActual > 0
                        ? (($stockActual * $costoActual) + ($cantidadNueva * $costoUni)) / ($stockActual + $cantidadNueva)
                        : $costoUni;

                    $movModel->insert([
                        'producto_id'     => $producto->id,
                        'tipo_movimiento' => 'ENTRADA',
                        'cantidad'        => $cantidadNueva,
                        'costo_unitario'  => $costoUni,
                        'referencia_tipo' => 'compra',
                        'referencia_id'   => $compraId,
                    ]);

                    $productoModel->update($producto->id, ['costo_promedio' => $nuevoCosto]);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    $err = $db->error();
                    $errores[] = $correlativo . ': error BD (' . ($err['message'] ?? 'desconocido') . ')';
                } else {
                    $procesadas++;
                }

            } catch (\Throwable $e) {
                $db->transRollback();
                $errores[] = $correlativo . ': ' . $e->getMessage();
                log_message('error', 'cargaManual ' . $correlativo . ': ' . $e->getMessage());
            }
        }

        return $this->response->setJSON([
            'success'  => true,
            'total'    => $procesadas,
            'saltadas' => $saltadas,
            'errores'  => $errores,
        ]);
    }

    public function validarDocumento()
    {
        $data = $this->request->getJSON(true);

        $codigo = $data['codigo'] ?? null;

        if (!$codigo) {
            return $this->response->setJSON(['ok' => false]);
        }

        $model = new CompraHeadModel();

        $existe = $model
            ->where('codigo_generacion', $codigo)
            ->first();

        return $this->response->setJSON([
            'ok' => true,
            'existe' => $existe ? true : false
        ]);
    }
    public function preview($id)
    {
        $compraModel = new CompraHeadModel();
        $detalleModel = new CompraDetalleModel();
        $pagosModel = new PagosComprasDetallesModel();

        // 🔥 JOIN con proveedores
        $compra = $compraModel
            ->select('compras_head.*, proveedores.nombre as proveedor_nombre')
            ->join('proveedores', 'proveedores.id = compras_head.proveedor_id')
            ->where('compras_head.id', $id)
            ->first();

        if (!$compra) {
            return 'Compra no encontrada';
        }

        $detalles = $detalleModel->getByCompra($id);

        // Pagos aplicados
        $pagos = $pagosModel
            ->select('pagos_compras_detalles.*, pagos_compras_head.fecha_pago, pagos_compras_head.forma_pago')
            ->join('pagos_compras_head', 'pagos_compras_head.id = pagos_compras_detalles.pago_id')
            ->where('pagos_compras_detalles.compra_id', $id)
            ->findAll();

        return view('compras/_preview_modal', [
            'factura' => $compra,
            'detalles' => $detalles,
            'pagos' => $pagos
        ]);
    }
}
