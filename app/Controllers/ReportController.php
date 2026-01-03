<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\SellerModel;
use App\Models\SettledPointModel;
use App\Models\TransactionModel;
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
    public function __construct()
    {
        $this->packageModel = new PackageModel();
        $this->settledPointModel = new SettledPointModel();
        $this->sellerModel = new SellerModel();
        $this->transactionModel = new TransactionModel();
        $this->packages = new PackageModel();
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

        // =========================
        // Filtros (GET)
        // =========================
        $filters = [
            'vendedor_id' => $this->request->getGet('vendedor_id'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];

        // =========================
        // PerPage dinámico (solo vista)
        // =========================
        $perPage = (int) ($this->request->getGet('perPage') ?? 25);
        $allowed = [10, 25, 50, 100];

        if (!in_array($perPage, $allowed)) {
            $perPage = 25;
        }

        // =========================
        // Modelo
        // =========================
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

        // =========================
        // Aplicar filtros
        // =========================
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

        // =========================
        // Paginación (SOLO vista)
        // =========================
        $packages = $model->paginate($perPage, 'packages');
        $pager    = $model->pager;

        // =========================
        // Vista
        // =========================
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

        // ENCABEZADOS
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

        // DATA
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

        // TOTALES
        $sheet->setCellValue("F{$row}", 'TOTALES');
        $sheet->setCellValue("G{$row}", $totalFlete);
        $sheet->setCellValue("H{$row}", $totalMonto);
        $sheet->getStyle("F{$row}:H{$row}")->getFont()->setBold(true);

        // AUTO SIZE
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // OUTPUT
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

        // =========================
        // Filtros (GET)
        // =========================
        $filters = [
            'tipo' => $this->request->getGet('tipo'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];

        // =========================
        // PerPage dinámico (solo vista)
        // =========================
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

        // =========================
        // Vista
        // =========================
        return view('reports/trans', [
            'pager'    => $pager,
            'trans' => $packages,
            'filters'  => $filters,
            'perPage'  => $perPage
        ]);
    }
}
