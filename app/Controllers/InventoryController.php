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
            'descripcion' => $data['descripcion'],
            'codigo'      => $codigo,
            'activo'      => $data['activo']
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
                'id'   => $p->id,
                'text' => $p->descripcion . ' (' . $p->codigo . ')'
            ];
        }

        return $this->response->setJSON([
            'results' => $results
        ]);
    }
}
