<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\SellerModel;
use App\Models\SettledPointModel;
use App\Models\TransactionModel;
use App\Models\CashierMovementModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends BaseController
{
    protected $packageModel;
    protected $sellerModel;
    protected $settledPointModel;
    protected $packages;
    protected $transactionModel;
    protected $cashierMovementModel;
    public function __construct()
    {
        $this->packageModel = new PackageModel();
        $this->settledPointModel = new SettledPointModel();
        $this->sellerModel = new SellerModel();
        $this->transactionModel = new TransactionModel();
        $this->packages = new PackageModel();
        $this->cashierMovementModel = new CashierMovementModel();
    }
    public function index()
    {
        $chk = requerirPermiso('ver_reportes');
        if ($chk !== true) return $chk;
        return view('reports/index');
    }
    public function packages()
    {
        $chk = requerirPermiso('ver_reportes');
        if ($chk !== true) return $chk;
        $filters = [
            'vendedor_id' => $this->request->getGet('vendedor_id'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];
        $perPage = (int) ($this->request->getGet('perPage') ?? 25);
        $allowed = [10, 25, 50, 100];

        if (!in_array($perPage, $allowed)) {
            $perPage = 25;
        }
        $model = $this->packageModel;
        $model->select("
        packages.id,
        packages.cliente,
        packages.tipo_servicio,
        packages.fecha_ingreso,
        packages.estatus,
        packages.estatus2,
        packages.flete_total,
        packages.flete_pagado,
        packages.flete_pendiente,
        packages.monto,
        packages.amount_paid,
        packages.fragil,
        sellers.seller AS vendedor,
        settled_points.point_name AS punto_fijo,
        branches.branch_name AS sucursal
    ")
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->join('branches', 'branches.id = packages.branch', 'left')
            ->orderBy('packages.id', 'DESC');
        if (!empty($filters['vendedor_id'])) {
            $model->where('packages.vendedor', $filters['vendedor_id']);
        }
        if (!empty($filters['estatus'])) {
            $model->groupStart()
                ->where('packages.estatus', $filters['estatus'])
                ->orWhere('packages.estatus2', $filters['estatus'])
                ->groupEnd();
        }
        if (!empty($filters['fecha_desde'])) {
            $model->where('packages.fecha_ingreso >=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $model->where('packages.fecha_ingreso <=', $filters['fecha_hasta']);
        }
        $packages = $model->paginate($perPage, 'packages');
        $pager    = $model->pager;

        return view('reports/packages', [
            'packages' => $packages,
            'pager'    => $pager,
            'sellers'  => $this->sellerModel->findAll(),
            'filters'  => $filters,
            'perPage'  => $perPage
        ]);
    }
    private function getPackagesForReport(array $filters)
    {
        $builder = $this->packageModel
            ->select('
            packages.*,
            sellers.seller AS vendedor
        ')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->orderBy('packages.id', 'DESC');
        if (!empty($filters['vendedor_id'])) {
            $builder->where('packages.vendedor', $filters['vendedor_id']);
        }
        if (!empty($filters['fecha_desde'])) {
            $builder->where('packages.fecha_ingreso >=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $builder->where('packages.fecha_ingreso <=', $filters['fecha_hasta']);
        }
        return $builder->get()->getResult();
    }
    public function packagesExcel()
    {
        $filters = $this->request->getGet();
        $packages = $this->getPackagesForReport($filters);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            'ID',
            'Cliente',
            'Vendedor',
            'Servicio',
            'Fecha Ingreso',
            'Estatus',
            'Flete',
            'Monto'
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        $row = 2;
        $totalFlete = 0;
        $totalMonto = 0;
        foreach ($packages as $pkg) {
            $sheet->setCellValue("A{$row}", $pkg->id);
            $sheet->setCellValue("B{$row}", $pkg->cliente);
            $sheet->setCellValue("C{$row}", $pkg->vendedor);
            $sheet->setCellValue("D{$row}", $pkg->tipo_servicio);
            $sheet->setCellValue("E{$row}", $pkg->fecha_ingreso);
            $sheet->setCellValue("F{$row}", $pkg->estatus);
            $sheet->setCellValue("G{$row}", $pkg->flete_total);
            $sheet->setCellValue("H{$row}", $pkg->monto);

            $totalFlete += $pkg->flete_total;
            $totalMonto += $pkg->monto;
            $row++;
        }
        $sheet->setCellValue("F{$row}", 'TOTALES');
        $sheet->setCellValue("G{$row}", $totalFlete);
        $sheet->setCellValue("H{$row}", $totalMonto);
        $sheet->getStyle("F{$row}:H{$row}")->getFont()->setBold(true);
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $filename = 'reporte_paquetes_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    public function packagesPDF()
    {
        $filters = $this->request->getGet();
        $packages = $this->getPackagesForReport($filters);

        $html = view('reports/packages_pdf', [
            'packages' => $packages,
            'filters'  => $filters
        ]);
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(
            'reporte_paquetes_' . date('Ymd_His') . '.pdf',
            ['Attachment' => true]
        );
    }
    public function trans()
    {
        $chk = requerirPermiso('ver_reportes');
        if ($chk !== true) return $chk;
        $filters = [
            'tipo' => $this->request->getGet('tipo'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];
        $perPage = (int) ($this->request->getGet('perPage') ?? 25);
        $allowed = [10, 25, 50, 100];
        if (!in_array($perPage, $allowed)) {
            $perPage = 25;
        }
        $model = $this->transactionModel;
        $model->orderBy('id', 'DESC');
        if (!empty($filters['tipo'])) {
            $model->where('transactions.tipo', $filters['tipo']);
        }
        if (!empty($filters['fecha_desde'])) {
            $model->where('transactions.created_at >=', $filters['fecha_desde'] . ' 00:00:00');
        }
        if (!empty($filters['fecha_hasta'])) {
            $model->where('transactions.created_at <=', $filters['fecha_hasta'] . ' 23:59:59');
        }
        $packages = $model->paginate($perPage, 'transactions');
        $pager    = $model->pager;
        return view('reports/trans', [
            'pager'    => $pager,
            'trans' => $packages,
            'filters'  => $filters,
            'perPage'  => $perPage
        ]);
    }
    private function getTransForReport(array $filters)
    {
        $builder = $this->transactionModel
            ->select('
            transactions.id,
            transactions.tipo,
            transactions.monto,
            transactions.origen,
            transactions.referencia,
            transactions.created_at,
            accounts.name AS cuenta
        ')
            ->join('accounts', 'accounts.id = transactions.account_id', 'left')
            ->orderBy('transactions.id', 'DESC');

        if (!empty($filters['tipo'])) {
            $builder->where('transactions.tipo', $filters['tipo']);
        }

        if (!empty($filters['fecha_desde'])) {
            $builder->where('transactions.created_at >=', $filters['fecha_desde'] . ' 00:00:00');
        }

        if (!empty($filters['fecha_hasta'])) {
            $builder->where('transactions.created_at <=', $filters['fecha_hasta'] . ' 23:59:59');
        }

        return $builder->get()->getResult();
    }

    public function transExcel()
    {
        $filters = $this->request->getGet();
        $trans = $this->getTransForReport($filters);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            'ID',
            'Tipo',
            'Cuenta',
            'Monto',
            'Origen',
            'Referencia',
            'Fecha'
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        $row = 2;
        $totalEntradas = 0;
        $totalSalidas  = 0;
        foreach ($trans as $t) {
            $sheet->setCellValue("A{$row}", $t->id);
            $sheet->setCellValue("B{$row}", ucfirst($t->tipo));
            $sheet->setCellValue("C{$row}", $t->cuenta);
            $sheet->setCellValue("D{$row}", $t->monto);
            $sheet->setCellValue("E{$row}", $t->origen);
            $sheet->setCellValue("F{$row}", $t->referencia);
            $sheet->setCellValue("G{$row}", $t->created_at);

            if ($t->tipo === 'entrada') {
                $totalEntradas += $t->monto;
            } else {
                $totalSalidas += $t->monto;
            }

            $row++;
        }
        $sheet->setCellValue("C{$row}", 'TOTAL ENTRADAS');
        $sheet->setCellValue("D{$row}", $totalEntradas);
        $sheet->setCellValue("C" . ($row + 1), 'TOTAL SALIDAS');
        $sheet->setCellValue("D" . ($row + 1), $totalSalidas);
        $sheet->getStyle("C{$row}:D" . ($row + 1))->getFont()->setBold(true);
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $filename = 'reporte_transacciones_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    public function transPDF()
    {
        $filters = $this->request->getGet();
        $trans = $this->getTransForReport($filters);
        $html = view('reports/trans_pdf', [
            'trans'   => $trans,
            'filters' => $filters
        ]);
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream(
            'reporte_transacciones_' . date('Ymd_His') . '.pdf',
            ['Attachment' => true]
        );
    }
    public function cashiersmovements()
    {
        $chk = requerirPermiso('ver_reportes');
        if ($chk !== true) return $chk;
        $filters = [
            'tipo' => $this->request->getGet('tipo'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];
        $perPage = (int) ($this->request->getGet('perPage') ?? 25);
        $allowed = [10, 25, 50, 100];
        if (!in_array($perPage, $allowed)) {
            $perPage = 25;
        }
        $model = $this->cashierMovementModel;
        $model->orderBy('id', 'DESC');
        if (!empty($filters['tipo'])) {
            $model->where('cashier_movements.type', $filters['tipo']);
        }
        if (!empty($filters['fecha_desde'])) {
            $model->where('cashier_movements.created_at >=', $filters['fecha_desde'] . ' 00:00:00');
        }
        if (!empty($filters['fecha_hasta'])) {
            $model->where('cashier_movements.created_at <=', $filters['fecha_hasta'] . ' 23:59:59');
        }
        $packages = $model->paginate($perPage, 'cashier_movements');
        $pager    = $model->pager;
        $total = $model->selectSum('amount')->where('type', 'in')->get()->getRow()->amount;

        return view('reports/cashiersmovements', [
            'pager'    => $pager,
            'cashier_movements' => $packages,
            'total'    => $total,
            'filters'  => $filters,
            'perPage'  => $perPage
        ]);
    }

public function cashiersmovementsExcel()
{
    $filters = $this->request->getGet();

    // 🔹 Movimientos SIN paginación
    $rows = $this->getCashiersForReport($filters);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // 🔹 Encabezados
    $headers = [
        'ID',
        'Caja',
        'Tipo',
        'Concepto',
        'Origen',
        'Referencia',
        'Fecha',
        'Monto'
    ];

    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        $col++;
    }

    $row = 2;
    $totalEntradas = 0;
    $totalSalidas  = 0;

    foreach ($rows as $r) {

        $tipoTexto = $r['type'] === 'in' ? 'Entrada' : 'Salida';

        $sheet->setCellValue("A{$row}", $r['id']);
        $sheet->setCellValue("B{$row}", $r['cashier_id']);
        $sheet->setCellValue("C{$row}", $tipoTexto);
        $sheet->setCellValue("D{$row}", $r['concept']);
        $sheet->setCellValue("E{$row}", $r['reference_type'] ?? '-');
        $sheet->setCellValue(
            "F{$row}",
            $r['reference_id'] ? '#' . $r['reference_id'] : '-'
        );
        $sheet->setCellValue(
            "G{$row}",
            date('d/m/Y H:i', strtotime($r['created_at']))
        );
        $sheet->setCellValue("H{$row}", $r['amount']);

        if ($r['type'] === 'in') {
            $totalEntradas += $r['amount'];
        } else {
            $totalSalidas += $r['amount'];
        }

        $row++;
    }

    // 🔹 Totales
    $sheet->setCellValue("G{$row}", 'TOTAL ENTRADAS');
    $sheet->setCellValue("H{$row}", $totalEntradas);

    $sheet->setCellValue("G" . ($row + 1), 'TOTAL SALIDAS');
    $sheet->setCellValue("H" . ($row + 1), $totalSalidas);

    $sheet->getStyle("G{$row}:H" . ($row + 1))
        ->getFont()
        ->setBold(true);

    // 🔹 Autosize
    foreach (range('A', 'H') as $c) {
        $sheet->getColumnDimension($c)->setAutoSize(true);
    }

    $filename = 'reporte_movimientos_caja_' . date('Ymd_His') . '.xlsx';

    // 🔹 Enviar archivo (FORMA CI4)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}


    public function cashiersmovementsPDF()
    {
        $filters = $this->request->getGet();
        // 🔹 Movimientos filtrados (SIN paginar)
        $rows = $this->getCashiersForReport($filters);

        // 🔹 Total real
        $total = array_sum(array_map(fn($x) => $x['amount'], $rows));

        $html = view('reports/cashiers_pdf', [
            'rows'    => $rows,
            'filters' => $filters,
            'total'   => $total
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->stream(
            'reporte_movimientos_caja_' . date('Ymd_His') . '.pdf',
            ['Attachment' => true]
        );
    }
    protected function getCashiersForReport(array $filters = [])
    {
        $model = new CashierMovementModel();

        $builder = $model
            ->select('
            cashier_movements.id,
            cashier_movements.cashier_id,
            cashier_movements.type,
            cashier_movements.amount,
            cashier_movements.concept,
            cashier_movements.reference_type,
            cashier_movements.reference_id,
            cashier_movements.created_at
        ')
            ->orderBy('cashier_movements.created_at', 'DESC');

        // 🔹 Filtro: fecha desde
        if (!empty($filters['fecha_desde'])) {
            $builder->where(
                'DATE(cashier_movements.created_at) >=',
                $filters['fecha_desde']
            );
        }
        if (!empty($filters['fecha_hasta'])) {
            $builder->where(
                'DATE(cashier_movements.created_at) <=',
                $filters['fecha_hasta']
            );
        }

        // 🔹 Filtro: tipo (entrada / salida)
        if (!empty($filters['tipo'])) {
            $type = $filters['tipo'] === 'entrada' ? 'in' : 'out';
            $builder->where('cashier_movements.type', $type);
        }

        return $builder->findAll();
    }
}
