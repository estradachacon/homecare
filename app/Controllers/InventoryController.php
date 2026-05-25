<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductoModel;
use App\Models\ProductoMovimientoModel;
use App\Models\SettingModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class InventoryController extends BaseController
{
    public function index()
    {
        $productoModel = new ProductoModel();

        $buscar = $this->request->getGet('buscar');
        $estado = $this->request->getGet('estado');
        $stock  = $this->request->getGet('stock');
        $orden  = $this->request->getGet('orden');
        $tipo = $this->request->getGet('tipo');

        $productoModel->conStock();

        if ($buscar) {
            $productoModel->groupStart()
                ->like('productos.descripcion', $buscar)
                ->orLike('productos.codigo', $buscar)
                ->groupEnd();
        }

        if ($estado !== '' && $estado !== null) {
            $productoModel->where('productos.activo', $estado);
        }

        if ($stock === 'con') {
            $productoModel->where('COALESCE(mov.stock,0) > 0');
        }

        if ($stock === 'sin') {
            $productoModel->where('COALESCE(mov.stock,0) <= 0');
        }

        if ($orden === 'stock_desc') {
            $productoModel->orderBy('stock', 'DESC');
        }

        if ($orden === 'stock_asc') {
            $productoModel->orderBy('stock', 'ASC');
        }

        $perPage = $this->request->getGet('perPage') ?? 10;

        if (!$perPage || $perPage == 'all') {
            $perPage = 10000; // o un número grande
        }

        if ($tipo !== '' && $tipo !== null) {
            $productoModel->where('productos.tipo', $tipo);
        }

        $productos = $productoModel->paginate($perPage);
        $pager = $productoModel->pager;

        // Calcular texto del paginador
        $pagina = $pager->getCurrentPage();
        $porPagina = $pager->getPerPage();
        $total = $pager->getTotal();

        $desde = ($pagina - 1) * $porPagina + 1;
        $hasta = min($pagina * $porPagina, $total);

        $info = "Mostrando {$desde} a {$hasta} de {$total} productos";

        if ($this->request->isAJAX()) {

            return $this->response->setJSON([
                'tbody' => view('inventario/_rows', ['productos' => $productos]),
                'pager' => $pager->links('default', 'bootstrap_full'),
                'info'  => $info
            ]);
        }

        return view('inventario/index', [
            'productos' => $productos,
            'pager' => $pager,
            'info' => $info
        ]);
    }

    public function delete($id)
    {
        $productoModel = new ProductoModel();
        $movimientoModel = new ProductoMovimientoModel();

        $producto = $productoModel->find($id);

        if (!$producto) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Producto no encontrado'
            ]);
        }

        // Validar si tiene ventas asociadas
        $tieneVentas = $movimientoModel
            ->where('producto_id', $id)
            ->where('tipo_movimiento', 'venta')
            ->countAllResults();

        if ($tieneVentas > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El producto no puede eliminarse porque tiene facturas asociadas'
            ]);
        }

        // En lugar de borrar → desactivar
        $productoModel->update($id, [
            'activo' => 0
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Producto desactivado correctamente'
        ]);
    }

    public function update($id)
    {
        $productoModel = new ProductoModel();

        $data = $this->request->getJSON(true);

        $codigo = trim($data['codigo']);

        // Validar si existe otro producto con el mismo código
        $existe = $productoModel
            ->where('codigo', $codigo)
            ->where('id !=', $id)
            ->first();

        if ($existe) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya existe otro producto con ese código'
            ]);
        }

        $productoModel->update($id, [
            'descripcion'      => $data['descripcion'],
            'codigo'           => $codigo,
            'tipo'             => (int)($data['tipo'] ?? 1),
            'activo'           => $data['activo'],
            'marca'            => trim($data['marca'] ?? '') ?: null,
            'clasificacion_id' => ($data['clasificacion_id'] ?? null) ?: null,
            'precio_minimo'    => isset($data['precio_minimo']) ? (float)$data['precio_minimo'] : null,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Producto actualizado correctamente'
        ]);
    }
    public function excel()
    {
        $productoModel = new ProductoModel();
        $settingModel  = new SettingModel();

        $settings = $settingModel->first();

        $buscar = $this->request->getGet('buscar');
        $estado = $this->request->getGet('estado');
        $stock  = $this->request->getGet('stock');
        $orden  = $this->request->getGet('orden');

        $productoModel->conStock();

        if ($buscar) {
            $productoModel->groupStart()
                ->like('productos.descripcion', $buscar)
                ->orLike('productos.codigo', $buscar)
                ->groupEnd();
        }

        if ($estado !== '') {
            $productoModel->where('productos.activo', $estado);
        }

        if ($stock === 'con') {
            $productoModel->where('COALESCE(mov.stock,0) >', 0);
        }

        if ($stock === 'sin') {
            $productoModel->where('COALESCE(mov.stock,0) <=', 0);
        }

        if ($orden === 'stock_desc') {
            $productoModel->orderBy('mov.stock', 'DESC');
        }

        if ($orden === 'stock_asc') {
            $productoModel->orderBy('mov.stock', 'ASC');
        }

        $productos = $productoModel->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if (!empty($settings->logo)) {

            $logoPath = FCPATH . 'upload/settings/' . $settings->logo;

            if (file_exists($logoPath)) {

                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo Empresa');
                $drawing->setPath($logoPath);
                $drawing->setHeight(85);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);
            }
        }

        $sheet->setCellValue('C1', 'Reporte de Inventario');
        $sheet->mergeCells('C1:E1');

        $sheet->getStyle('C1')->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet->setCellValue('C2', 'Fecha: ' . date('d/m/Y H:i'));
        $sheet->setCellValue('C3', 'Registros: ' . count($productos));

        $sheet->setCellValue('A5', 'Producto');
        $sheet->setCellValue('B5', 'Código');
        $sheet->setCellValue('C5', 'Stock');
        $sheet->setCellValue('D5', 'Estado');

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ];

        $sheet->getStyle('A5:D5')->applyFromArray($headerStyle);

        $row = 6;

        foreach ($productos as $p) {

            $sheet->setCellValue('A' . $row, $p->descripcion);
            $sheet->setCellValue('B' . $row, $p->codigo);
            $sheet->setCellValue('C' . $row, $p->stock);
            $sheet->setCellValue('D' . $row, $p->activo ? 'Activo' : 'Inactivo');

            $row++;
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A5:D' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);

        $sheet->setAutoFilter('A5:D' . ($row - 1));

        $sheet->freezePane('A6');

        $filename = "inventario_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function store()
    {
        $productoModel = new ProductoModel();

        $data = $this->request->getJSON(true);

        $descripcion = trim($data['descripcion'] ?? '');
        $codigo      = trim($data['codigo'] ?? '');
        $tipo        = (int)($data['tipo'] ?? 1);
        $activo      = isset($data['activo']) ? (int)$data['activo'] : 1;

        if (!$descripcion) {
            return $this->response->setJSON(['success' => false, 'message' => 'La descripción es requerida']);
        }

        if (!$codigo) {
            return $this->response->setJSON(['success' => false, 'message' => 'El código es requerido']);
        }

        if (!in_array($tipo, [1, 2, 3])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tipo de producto inválido']);
        }

        $existe = $productoModel->where('codigo', $codigo)->first();

        if ($existe) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe un producto con ese código']);
        }

        $id = $productoModel->insert([
            'descripcion'      => $descripcion,
            'codigo'           => $codigo,
            'tipo'             => $tipo,
            'activo'           => $activo,
            'marca'            => trim($data['marca'] ?? '') ?: null,
            'clasificacion_id' => ($data['clasificacion_id'] ?? null) ?: null,
            'precio_minimo'    => isset($data['precio_minimo']) ? (float)$data['precio_minimo'] : 0,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'id'      => $id
        ]);
    }

    public function plantillaExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos');

        $sheet->setCellValue('A1', 'descripcion');
        $sheet->setCellValue('B1', 'codigo');
        $sheet->setCellValue('C1', 'tipo');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        $sheet->setCellValue('A2', 'Suero Fisiológico 500ml');
        $sheet->setCellValue('B2', 'PRD-001');
        $sheet->setCellValue('C2', '1');
        $sheet->getStyle('A2:C2')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F0FE']],
        ]);

        $sheet->setCellValue('A3', 'Nota: tipo 1=Bien  |  2=Servicio  |  3=Otro.  Elimina esta fila de nota antes de importar.');
        $sheet->mergeCells('A3:C3');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A3')->getFont()->getColor()->setRGB('888888');

        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $filename = 'plantilla_productos.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importarExcel()
    {
        $file = $this->request->getFile('archivo');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Archivo inválido o no recibido']);
        }

        if (!in_array(strtolower($file->getClientExtension()), ['xlsx', 'xls'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se aceptan archivos .xlsx o .xls']);
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getTempName());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getTempName());
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'El archivo no contiene datos']);
        }

        array_shift($rows); // quitar encabezado

        $productoModel    = new ProductoModel();
        $importados       = 0;
        $errores          = [];
        $codigosEnArchivo = [];

        foreach ($rows as $i => $row) {
            $fila        = $i + 2;
            $descripcion = trim((string)($row[0] ?? ''));
            $codigo      = trim((string)($row[1] ?? ''));
            $tipo        = (int)($row[2] ?? 1);

            if ($descripcion === '' && $codigo === '') {
                continue;
            }

            if ($descripcion === '') {
                $errores[] = "Fila {$fila}: la descripción es requerida";
                continue;
            }

            if ($codigo === '') {
                $errores[] = "Fila {$fila}: el código es requerido";
                continue;
            }

            if (!in_array($tipo, [1, 2, 3])) {
                $tipo = 1;
            }

            if (in_array($codigo, $codigosEnArchivo)) {
                $errores[] = "Fila {$fila}: código '{$codigo}' duplicado dentro del archivo";
                continue;
            }

            if ($productoModel->where('codigo', $codigo)->first()) {
                $errores[] = "Fila {$fila}: el código '{$codigo}' ya existe en el sistema";
                continue;
            }

            $productoModel->insert([
                'descripcion' => $descripcion,
                'codigo'      => $codigo,
                'tipo'        => $tipo,
                'activo'      => 1,
            ]);

            $codigosEnArchivo[] = $codigo;
            $importados++;
        }

        return $this->response->setJSON([
            'success'    => true,
            'importados' => $importados,
            'errores'    => $errores,
            'message'    => "Se importaron {$importados} producto(s) correctamente"
        ]);
    }

    public function searchAjax()
    {
        $q = $this->request->getGet('q');

        $model = new ProductoModel();

        $builder = $model;

        if ($q) {
            $builder = $builder->groupStart()
                ->like('descripcion', $q)
                ->orLike('codigo', $q)
                ->groupEnd();
        }

        $productos = $builder
            ->where('activo', 1) // 🔥 solo activos (opcional pero recomendado)
            ->orderBy('descripcion', 'ASC')
            ->limit(20)
            ->findAll();

        $results = [];

        foreach ($productos as $p) {
            $results[] = [
                'id'            => $p->id,
                'text'          => $p->descripcion . ' (' . $p->codigo . ')',
                'codigo'        => $p->codigo,
                'tipo'          => $p->tipo,
                'precio_minimo' => (float)($p->precio_minimo ?? 0),
            ];
        }

        return $this->response->setJSON([
            'results' => $results
        ]);
    }
    public function show($id)
    {
        $productoModel = new ProductoModel();
        $movModel = new ProductoMovimientoModel();

        $producto = $productoModel->find($id);

        if (!$producto) {
            return redirect()->to(base_url('productos'))
                ->with('error', 'Producto no encontrado');
        }

        $anio = $this->request->getGet('anio') ?? date('Y');

        $filtros = [
            'desde'     => $this->request->getGet('desde'),
            'hasta'     => $this->request->getGet('hasta'),
            'documento' => trim($this->request->getGet('documento') ?? ''),
            'tipo'      => $this->request->getGet('tipo'),
        ];

        $movQuery = $movModel
            ->select("
            productos_movimientos.*,

            facturas_head.numero_control,
            facturas_head.tipo_dte,
            facturas_head.fecha_emision    AS factura_fecha,
            clientes.nombre                AS cliente_nombre,

            compras_head.numero_control    AS compra_numero_control,
            compras_head.tipo_dte          AS compra_tipo_dte,
            compras_head.fecha_emision     AS compra_fecha,
            proveedores.nombre             AS proveedor_nombre,

            COALESCE(
                facturas_head.fecha_emision,
                compras_head.fecha_emision,
                DATE(productos_movimientos.created_at)
            ) AS fecha_documento,

            COALESCE(
                facturas_head.numero_control,
                compras_head.numero_control
            ) AS numero_documento
        ")
            ->join(
                'facturas_head',
                "facturas_head.id = productos_movimientos.referencia_id 
            AND productos_movimientos.referencia_tipo = 'factura'",
                'left'
            )
            ->join(
                'clientes',
                'clientes.id = facturas_head.receptor_id',
                'left'
            )
            ->join(
                'compras_head',
                "compras_head.id = productos_movimientos.referencia_id 
            AND productos_movimientos.referencia_tipo = 'compra'",
                'left'
            )
            ->join(
                'proveedores',
                'proveedores.id = compras_head.proveedor_id',
                'left'
            )
            ->where('productos_movimientos.producto_id', $id);

        // 🔥 FILTRO POR AÑO
        if (!empty($anio)) {
            $movQuery->groupStart()
                ->where("YEAR(facturas_head.fecha_emision)", $anio)
                ->orWhere("YEAR(compras_head.fecha_emision)", $anio)
                ->orWhere(
                    "facturas_head.fecha_emision IS NULL 
                AND compras_head.fecha_emision IS NULL 
                AND YEAR(productos_movimientos.created_at) = {$anio}",
                    null,
                    false
                )
                ->groupEnd();
        }

        $movimientos = $movQuery
            ->orderBy('fecha_documento', 'ASC')
            ->orderBy('productos_movimientos.cantidad', 'DESC')
            ->orderBy('numero_documento', 'ASC')
            ->orderBy('productos_movimientos.id', 'ASC')
            ->findAll();

        // 🔥 STOCK TOTAL (el que se muestra en el header, sin filtro de año)
        $stockData = $movModel
            ->select('SUM(cantidad) as stock')
            ->where('producto_id', $id)
            ->first();

        $stock = (float)($stockData->stock ?? 0);

        // 🔥 STOCK APERTURA (suma de todo LO ANTERIOR al año filtrado)
        $stockApertura = 0;

        if (!empty($anio)) {
            $aperturaData = $movModel
                ->select('SUM(productos_movimientos.cantidad) as stock')
                ->join(
                    'facturas_head',
                    "facturas_head.id = productos_movimientos.referencia_id 
                AND productos_movimientos.referencia_tipo = 'factura'",
                    'left'
                )
                ->join(
                    'compras_head',
                    "compras_head.id = productos_movimientos.referencia_id 
                AND productos_movimientos.referencia_tipo = 'compra'",
                    'left'
                )
                ->where('productos_movimientos.producto_id', $id)
                ->where(
                    "YEAR(COALESCE(
                    facturas_head.fecha_emision,
                    compras_head.fecha_emision,
                    productos_movimientos.created_at
                )) <",
                    $anio
                )
                ->first();

            $stockApertura = (float)($aperturaData->stock ?? 0);
        }
        // 🔥 CALCULAR SALDO REAL DEL KARDEX DEL AÑO
        $saldoKardex = $stockApertura;
        $costoKardex = 0;

        foreach ($movimientos as $m) {
            $saldoKardex += (float) $m->cantidad;

            if ((float) $m->cantidad > 0) {
                $costoKardex = (float) $m->costo_unitario;
            }

            $m->saldo_kardex = $saldoKardex;
            $m->costo_kardex = $costoKardex;
        }
        $stockCierre = $saldoKardex;

        // 🔥 FILTROS VISUALES, NO AFECTAN SALDOS
        $movimientos = array_values(array_filter($movimientos, function ($m) use ($filtros) {
            $fecha = !empty($m->fecha_documento)
                ? date('Y-m-d', strtotime($m->fecha_documento))
                : date('Y-m-d', strtotime($m->created_at));

            if (!empty($filtros['desde']) && $fecha < $filtros['desde']) {
                return false;
            }

            if (!empty($filtros['hasta']) && $fecha > $filtros['hasta']) {
                return false;
            }

            if (($filtros['tipo'] ?? '') === 'entrada' && (float) $m->cantidad <= 0) {
                return false;
            }

            if (($filtros['tipo'] ?? '') === 'salida' && (float) $m->cantidad >= 0) {
                return false;
            }

            if (!empty($filtros['documento'])) {
                $buscar = mb_strtolower($filtros['documento']);

                $texto = mb_strtolower(
                    ($m->numero_documento ?? '') . ' ' .
                        ($m->numero_control ?? '') . ' ' .
                        ($m->compra_numero_control ?? '') . ' ' .
                        ($m->cliente_nombre ?? '') . ' ' .
                        ($m->proveedor_nombre ?? '') . ' ' .
                        ($m->referencia_tipo ?? '') . ' ' .
                        ($m->referencia_id ?? '')
                );

                if (!str_contains($texto, $buscar)) {
                    return false;
                }
            }

            return true;
        }));
        // Lotes del catálogo para este producto
        $lotes = (new \App\Models\ConsignacionLoteModel())
            ->where('producto_id', $id)
            ->orderBy('activo', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('inventario/show', [
            'producto'      => $producto,
            'movimientos'   => $movimientos,
            'stock'         => $stock,
            'anio'          => $anio,
            'stockApertura' => $stockApertura,
            'stockCierre' => $stockCierre,
            'lotes'         => $lotes,
            'filtros' => $filtros,
        ]);
    }
}
