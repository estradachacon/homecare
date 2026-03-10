<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\FacturaHeadModel;
use App\Models\ClienteModel;
use App\Models\SettingModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportesController extends Controller
{
    public function index()
    {
        $chk = requerirPermiso('ver_reportes');
        if ($chk !== true) return $chk;
        return view('reports/index');
    }

    public function formSaldosAntiguedad()
    {
        return view('reports/saldos_antiguedad');
    }

    public function facturacion()
    {
        return view('reports/facturacion');
    }

    private function applyPdfHeader($dompdf)
    {
        $settingModel = new SettingModel();
        $setting = $settingModel->first();

        $companyName = $setting->company_name ?? 'Mi Empresa';

        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        $fontBold = $fontMetrics->getFont("DejaVu Sans", "bold");
        $fontNormal = $fontMetrics->getFont("DejaVu Sans", "normal");

        $textWidth = $fontMetrics->getTextWidth($companyName, $fontBold, 12);
        $pageWidth = $canvas->get_width();

        $x = ($pageWidth - $textWidth) / 2;

        $canvas->page_text($x, 20, $companyName, $fontBold, 12, [0, 0, 0]);

        $canvas->page_text(
            500,
            820,
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            $fontNormal,
            8,
            [0, 0, 0]
        );
    }



    // REPORTES DE CUENTAS POR COBRAR
    public function saldosAntiguedad()
    {
        $facturaModel = new FacturaHeadModel();

        $hoy = date('Y-m-d');

        $facturas = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id')
            ->where('facturas_head.saldo >', 0)
            ->where('facturas_head.anulada', 0)
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $dias = (strtotime($hoy) - strtotime($factura->fecha_emision)) / 86400;

            if (!isset($reporte[$factura->receptor_id])) {
                $reporte[$factura->receptor_id] = [
                    'cliente' => $factura->cliente_nombre,
                    '0_30'    => 0,
                    '31_60'   => 0,
                    '61_90'   => 0,
                    '91_mas'  => 0,
                    'total'   => 0,
                ];
            }

            if ($dias <= 30) {
                $reporte[$factura->receptor_id]['0_30'] += $factura->saldo;
            } elseif ($dias <= 60) {
                $reporte[$factura->receptor_id]['31_60'] += $factura->saldo;
            } elseif ($dias <= 90) {
                $reporte[$factura->receptor_id]['61_90'] += $factura->saldo;
            } else {
                $reporte[$factura->receptor_id]['91_mas'] += $factura->saldo;
            }

            $reporte[$factura->receptor_id]['total'] += $factura->saldo;
        }

        $data = [
            'reporte' => $reporte,
            'fecha'   => $hoy
        ];

        /* GENERAR HTML */
        $html = view('reports/saldos_antiguedad', $data);

        /* DOMPDF */
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        /* HEADER + PAGINADO */
        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }

    public function saldosAntiguedadPDF()
    {
        $facturaModel = new FacturaHeadModel();

        // 📅 Tomar fecha desde el form
        $fecha_corte = $this->request->getGet('fecha_corte');
        $fecha_corte = $fecha_corte ?: date('Y-m-d');
        $cliente_id = $this->request->getGet('cliente_id');

        $query = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.saldo >', 0)
            ->where('facturas_head.anulada', 0)
            ->where('facturas_head.fecha_emision <=', $fecha_corte);

        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        $facturas = $query
            ->orderBy('clientes.nombre', 'ASC')
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->findAll();

        $reporte = [];
        foreach ($facturas as $factura) {

            $dias = floor(
                (strtotime($fecha_corte) - strtotime($factura->fecha_emision)) / 86400
            );

            if (!isset($reporte[$factura->receptor_id])) {
                $reporte[$factura->receptor_id] = [
                    'cliente' => $factura->cliente_nombre,
                    '0_30'    => 0,
                    '31_60'   => 0,
                    '61_90'   => 0,
                    '91_mas'  => 0,
                    'total'   => 0,
                ];
            }

            if ($dias <= 30) {
                $reporte[$factura->receptor_id]['0_30'] += $factura->saldo;
            } elseif ($dias <= 60) {
                $reporte[$factura->receptor_id]['31_60'] += $factura->saldo;
            } elseif ($dias <= 90) {
                $reporte[$factura->receptor_id]['61_90'] += $factura->saldo;
            } else {
                $reporte[$factura->receptor_id]['91_mas'] += $factura->saldo;
            }

            $reporte[$factura->receptor_id]['total'] += $factura->saldo;
        }

        $data = [
            'reporte' => $reporte,
            'fecha'   => $fecha_corte,
            'generado_en' => date('d/m/Y H:i')
        ];

        $html = view('reports/maestro/saldos_antiguedad_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $canvas = $dompdf->getCanvas();
        $dompdf->render();

        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }
    public function saldosAntiguedadDetallePDF()
    {
        $facturaModel = new FacturaHeadModel();
        $db = \Config\Database::connect();

        $fecha_corte = $this->request->getGet('fecha_corte') ?: date('Y-m-d');
        $cliente_id = $this->request->getGet('cliente_id');

        $query = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.saldo >', 0)
            ->where('facturas_head.anulada', 0)
            ->where('facturas_head.fecha_emision <=', $fecha_corte);

        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        $facturas = $query
            ->orderBy('clientes.nombre', 'ASC')
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->orderBy('facturas_head.numero_control', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $dias = floor((strtotime($fecha_corte) - strtotime($factura->fecha_emision)) / 86400);

            if (!isset($reporte[$factura->receptor_id])) {
                $reporte[$factura->receptor_id] = [
                    'cliente' => $factura->cliente_nombre,
                    'facturas' => [],
                    'totales' => [
                        'total_facturas' => 0,
                        'total_saldo' => 0
                    ]
                ];
            }

            // 🔎 Obtener pagos vivos de esta factura
            $pagos = $db->table('pagos_details')
                ->select('pagos_details.*, pagos_head.fecha_pago as fecha_pago')
                ->join('pagos_head', 'pagos_head.id = pagos_details.pago_id', 'left')
                ->where('pagos_details.factura_id', $factura->id)
                ->where('pagos_details.anulado', 0)
                ->get()
                ->getResult();

            $reporte[$factura->receptor_id]['facturas'][] = [
                'factura' => $factura,
                'dias'    => $dias,
                'pagos'   => $pagos
            ];

            $reporte[$factura->receptor_id]['totales']['total_facturas'] += $factura->monto_total_operacion;
            $reporte[$factura->receptor_id]['totales']['total_saldo'] += $factura->saldo;
        }

        $data = [
            'reporte' => $reporte,
            'fecha'   => $fecha_corte,
            'generado_en' => date('d/m/Y h:i A')
        ];

        $html = view('reports/maestro/saldos_antiguedad_detalle_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }

    //Metodo para reportes de vendedores:
    public function saldosAntiguedadVendedorPDF()
    {
        $facturaModel = new FacturaHeadModel();
        $settingModel = new SettingModel();

        $setting = $settingModel->first();
        $companyName = $setting->company_name ?? 'Mi Empresa';

        $fecha_corte = $this->request->getGet('fecha_corte') ?: date('Y-m-d');
        $cliente_id  = $this->request->getGet('cliente_id');
        $seller_id   = $this->request->getGet('seller_id');

        $query = $facturaModel
            ->select('facturas_head.*, 
            clientes.nombre as cliente_nombre,
            sellers.seller as vendedor_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->where('facturas_head.saldo >', 0)
            ->where('facturas_head.anulada', 0)
            ->where('facturas_head.fecha_emision <=', $fecha_corte);

        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        if (!empty($seller_id)) {
            $query->where('facturas_head.vendedor_id', $seller_id);
        }

        $facturas = $query
            ->orderBy('sellers.seller', 'ASC')
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $dias = floor(
                (strtotime($fecha_corte) - strtotime($factura->fecha_emision)) / 86400
            );

            if (!isset($reporte[$factura->vendedor_id])) {
                $reporte[$factura->vendedor_id] = [
                    'vendedor' => $factura->vendedor_nombre ?? 'Sin vendedor',
                    '0_30'     => 0,
                    '31_60'    => 0,
                    '61_90'    => 0,
                    '91_mas'   => 0,
                    'total'    => 0,
                ];
            }

            if ($dias <= 30) {
                $reporte[$factura->vendedor_id]['0_30'] += $factura->saldo;
            } elseif ($dias <= 60) {
                $reporte[$factura->vendedor_id]['31_60'] += $factura->saldo;
            } elseif ($dias <= 90) {
                $reporte[$factura->vendedor_id]['61_90'] += $factura->saldo;
            } else {
                $reporte[$factura->vendedor_id]['91_mas'] += $factura->saldo;
            }

            $reporte[$factura->vendedor_id]['total'] += $factura->saldo;
        }

        $data = [
            'reporte'     => $reporte,
            'fecha'       => $fecha_corte,
            'generado_en' => date('d/m/Y H:i')
        ];

        $html = view('reports/vendedor/saldos_antiguedad_vendedor_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        /* CANVAS */
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        $fontBold = $fontMetrics->getFont("DejaVu Sans", "bold");
        $fontNormal = $fontMetrics->getFont("DejaVu Sans", "normal");

        /* CENTRAR NOMBRE EMPRESA */
        $textWidth = $fontMetrics->getTextWidth($companyName, $fontBold, 12);
        $pageWidth = $canvas->get_width();

        $x = ($pageWidth - $textWidth) / 2;

        $canvas->page_text(
            $x,
            20,
            $companyName,
            $fontBold,
            12,
            [0, 0, 0]
        );

        /* PAGINADO */
        $canvas->page_text(
            500,
            820,
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            $fontNormal,
            8,
            [0, 0, 0]
        );

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }
    public function saldosAntiguedadVendedorDetallePDF()
    {
        $facturaModel = new FacturaHeadModel();
        $db = \Config\Database::connect();
        $settingModel = new SettingModel();
        $setting = $settingModel->first();
        $companyName = $setting->company_name ?? 'Mi Empresa';

        $fecha_corte = $this->request->getGet('fecha_corte') ?: date('Y-m-d');
        $cliente_id  = $this->request->getGet('cliente_id');
        $seller_id   = $this->request->getGet('seller_id');

        $query = $facturaModel
            ->select('facturas_head.*, 
                  clientes.nombre as cliente_nombre,
                  sellers.seller as vendedor_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->where('facturas_head.saldo >', 0)
            ->where('facturas_head.anulada', 0)
            ->where('facturas_head.fecha_emision <=', $fecha_corte);

        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        if (!empty($seller_id)) {
            $query->where('facturas_head.vendedor_id', $seller_id);
        }

        $facturas = $query
            ->orderBy('sellers.seller', 'ASC')
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->orderBy('facturas_head.numero_control', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $dias = floor(
                (strtotime($fecha_corte) - strtotime($factura->fecha_emision)) / 86400
            );

            $saldo = $factura->saldo;

            // Rangos de antigüedad
            $r0 = $r30 = $r60 = $r90 = 0;

            if ($dias <= 30) {
                $r0 = $saldo;
            } elseif ($dias <= 60) {
                $r30 = $saldo;
            } elseif ($dias <= 90) {
                $r60 = $saldo;
            } else {
                $r90 = $saldo;
            }

            $vendedorKey = $factura->vendedor_id ?? 0;
            $clienteKey  = $factura->receptor_id ?? 0;

            if (!isset($reporte[$vendedorKey])) {

                $reporte[$vendedorKey] = [
                    'vendedor' => $factura->vendedor_nombre ?? 'Sin vendedor',
                    'clientes' => []
                ];
            }

            if (!isset($reporte[$vendedorKey]['clientes'][$clienteKey])) {

                $reporte[$vendedorKey]['clientes'][$clienteKey] = [
                    'cliente' => $factura->cliente_nombre,
                    'documentos' => [],
                    'totales' => [
                        '0_30' => 0,
                        '31_60' => 0,
                        '61_90' => 0,
                        '90_mas' => 0,
                        'total' => 0
                    ]
                ];
            }

            if ($factura->condicion_operacion == 1) {

                $plazo = 'CONTADO';
            } else {

                $diasCredito = $factura->plazo_credito ?? 0;

                $plazo = $diasCredito > 0
                    ? $diasCredito . ' días'
                    : 'CRÉDITO';
            }
            // Documento
            $doc = [
                'fecha' => $factura->fecha_emision,
                'doc'   => $factura->numero_control,
                'tipo'  => $factura->tipo_dte,
                'plazo' => $plazo,
                'rango_0_30' => $r0,
                'rango_31_60' => $r30,
                'rango_61_90' => $r60,
                'rango_90_mas' => $r90,
                'total' => $saldo
            ];

            $reporte[$vendedorKey]['clientes'][$clienteKey]['documentos'][] = $doc;

            // Totales por cliente
            $reporte[$vendedorKey]['clientes'][$clienteKey]['totales']['0_30'] += $r0;
            $reporte[$vendedorKey]['clientes'][$clienteKey]['totales']['31_60'] += $r30;
            $reporte[$vendedorKey]['clientes'][$clienteKey]['totales']['61_90'] += $r60;
            $reporte[$vendedorKey]['clientes'][$clienteKey]['totales']['90_mas'] += $r90;
            $reporte[$vendedorKey]['clientes'][$clienteKey]['totales']['total'] += $saldo;
        }

        $data = [
            'reporte'     => $reporte,
            'fecha'       => $fecha_corte,
            'generado_en' => date('d/m/Y H:i')
        ];

        $html = view('reports/vendedor/saldos_antiguedad_vendedor_detalle_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        /* CANVAS */
        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        $fontBold = $fontMetrics->getFont("DejaVu Sans", "bold");
        $fontNormal = $fontMetrics->getFont("DejaVu Sans", "normal");

        /* CENTRAR NOMBRE EMPRESA */
        $textWidth = $fontMetrics->getTextWidth($companyName, $fontBold, 12);
        $pageWidth = $canvas->get_width();

        $x = ($pageWidth - $textWidth) / 2;

        $canvas->page_text(
            $x,
            20,
            $companyName,
            $fontBold,
            12,
            [0, 0, 0]
        );

        /* PAGINADO */
        $canvas->page_text(
            500,
            820,
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            $fontNormal,
            8,
            [0, 0, 0]
        );

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }




    // REPORTES DE FACTURACIÓN

    public function ventasClienteExcel()
    {
        $facturaModel = new FacturaHeadModel();
        $clienteModel = new ClienteModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $tipo  = $this->request->getGet('tipo_documento');
        $cliente_id = $this->request->getGet('cliente_id');

        $query = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('fecha_emision >=', $desde)
            ->where('fecha_emision <=', $hasta)
            ->where('tipo_dte !=', '14');

        if (!empty($tipo)) {
            $query->where('tipo_dte', $tipo);
        }

        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        $facturas = $query
            ->orderBy('clientes.nombre', 'ASC')
            ->orderBy('tipo_dte', 'ASC')
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();


        /* AGRUPACIÓN ORIGINAL */
        $reporte = [];

        foreach ($facturas as $factura) {

            $cliente = $factura->cliente_nombre;
            $tipoDte = $factura->tipo_dte;

            if (!isset($reporte[$cliente])) {
                $reporte[$cliente] = [];
            }

            if (!isset($reporte[$cliente][$tipoDte])) {
                $reporte[$cliente][$tipoDte] = [
                    'facturas' => []
                ];
            }

            $reporte[$cliente][$tipoDte]['facturas'][] = $factura;
        }


        $cliente = null;

        if (!empty($cliente_id)) {
            $cliente = $clienteModel->find($cliente_id);
        }


        $tiposDocumento = dte_tipos();
        $siglas = dte_siglas();


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        /* TITULO */

        $sheet->setCellValue("A$row", "REPORTE DE VENTAS POR CLIENTE");
        $sheet->mergeCells("A$row:I$row");

        $row += 2;

        /* INFORMACIÓN */

        $sheet->setCellValue("A$row", "Desde:");
        $sheet->setCellValue("B$row", $desde);

        $sheet->setCellValue("D$row", "Hasta:");
        $sheet->setCellValue("E$row", $hasta);

        $sheet->setCellValue("G$row", "Generado:");
        $sheet->setCellValue("H$row", date('d/m/Y H:i'));

        $row += 2;


        /* HEADERS */

        $headers = [
            "Fecha",
            "Tipo",
            "Número",
            "Cliente",
            "Total S/IVA",
            "IVA 13%",
            "Valor Venta",
            "1% Ret",
            "Total"
        ];

        $col = 'A';

        foreach ($headers as $h) {

            $sheet->setCellValue($col . $row, $h);

            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1F4E79');

            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($col . $row)->getFont()->setBold(true);

            $col++;
        }

        $row++;


        $gt_base = 0;
        $gt_iva = 0;
        $gt_valor = 0;
        $gt_ret = 0;
        $gt_total = 0;


        foreach ($reporte as $clienteNombre => $tipos) {

            /* CLIENTE */

            $sheet->setCellValue("A$row", "CLIENTE: " . $clienteNombre);
            $sheet->mergeCells("A$row:I$row");

            $sheet->getStyle("A$row")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('DDEBF7');

            $row++;


            foreach ($tipos as $tipo => $data) {

                $sheet->setCellValue("A$row", "TIPO DOCUMENTO: " . ($tiposDocumento[$tipo] ?? $tipo));
                $sheet->mergeCells("A$row:I$row");

                $row++;

                $sub_base = 0;
                $sub_iva = 0;
                $sub_valor = 0;
                $sub_ret = 0;
                $sub_total = 0;


                foreach ($data['facturas'] as $factura) {

                    $base = $factura->total_gravada ?? 0;
                    $iva = $factura->total_iva ?? 0;
                    $valor = $factura->monto_total_operacion ?? 0;
                    $ret = $factura->iva_rete1 ?? 0;
                    $total = $factura->total_pagar ?? 0;

                    if ($factura->tipo_dte == '05') {
                        $base *= -1;
                        $iva *= -1;
                        $valor *= -1;
                        $ret *= -1;
                        $total *= -1;
                    }

                    if ($factura->anulada) {
                        $base = $iva = $valor = $ret = $total = 0;
                    }

                    $excelDate = Date::PHPToExcel(new \DateTime($factura->fecha_emision));

                    $sheet->setCellValue("A$row", $excelDate);

                    $sheet->getStyle("A$row")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                    $sheet->setCellValue("B$row", $siglas[$factura->tipo_dte] ?? $factura->tipo_dte);
                    $sheet->setCellValue("C$row", substr($factura->numero_control, -6));
                    $sheet->setCellValue("D$row", $factura->cliente_nombre);

                    $sheet->setCellValue("E$row", $base);
                    $sheet->setCellValue("F$row", $iva);
                    $sheet->setCellValue("G$row", $valor);
                    $sheet->setCellValue("H$row", $ret);
                    $sheet->setCellValue("I$row", $total);
                    $sheet->getStyle("E$row:I$row")
                        ->getNumberFormat()
                        ->setFormatCode('"$"#,##0.00');

                    $sub_base += $base;
                    $sub_iva += $iva;
                    $sub_valor += $valor;
                    $sub_ret += $ret;
                    $sub_total += $total;

                    $gt_base += $base;
                    $gt_iva += $iva;
                    $gt_valor += $valor;
                    $gt_ret += $ret;
                    $gt_total += $total;

                    $row++;
                }

                /* SUBTOTAL */

                $sheet->setCellValue("A$row", "SUBTOTAL " . ($siglas[$tipo] ?? $tipo));
                $sheet->mergeCells("A$row:D$row");

                $sheet->setCellValue("E$row", $sub_base);
                $sheet->setCellValue("F$row", $sub_iva);
                $sheet->setCellValue("G$row", $sub_valor);
                $sheet->setCellValue("H$row", $sub_ret);
                $sheet->setCellValue("I$row", $sub_total);
                $sheet->getStyle("E$row:I$row")
                    ->getNumberFormat()
                    ->setFormatCode('"$"#,##0.00');

                $sheet->getStyle("A$row:I$row")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E2EFDA');

                $row += 2;
            }
        }


        /* GRAN TOTAL */
        $sheet->setCellValue("A$row", "GRAN TOTAL");
        $sheet->mergeCells("A$row:D$row");

        $sheet->setCellValue("E$row", $gt_base);
        $sheet->setCellValue("F$row", $gt_iva);
        $sheet->setCellValue("G$row", $gt_valor);
        $sheet->setCellValue("H$row", $gt_ret);
        $sheet->setCellValue("I$row", $gt_total);

        $sheet->getStyle("E$row:I$row")
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        $sheet->getStyle("A$row:I$row")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('C6E0B4');


        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }


        $filename = "ventas_cliente_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function ventasClientePDF()
    {
        $facturaModel = new FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $tipo  = $this->request->getGet('tipo_documento');
        $cliente_id = $this->request->getGet('cliente_id');

        $query = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('fecha_emision >=', $desde)
            ->where('fecha_emision <=', $hasta)
            ->where('tipo_dte !=', '14'); // excluir sujeto excluido

        if (!empty($tipo)) {
            $query->where('tipo_dte', $tipo);
        }

        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        $facturas = $query
            ->orderBy('clientes.nombre', 'ASC')
            ->orderBy('tipo_dte', 'ASC')
            ->orderBy('fecha_emision', 'ASC')
            ->orderBy('numero_control', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $cliente = $factura->cliente_nombre;
            $tipoDte = $factura->tipo_dte;

            if (!isset($reporte[$cliente])) {

                $reporte[$cliente] = [];
            }

            if (!isset($reporte[$cliente][$tipoDte])) {

                $reporte[$cliente][$tipoDte] = [
                    'facturas' => [],
                    'totales' => [
                        'base' => 0,
                        'iva' => 0,
                        'valor' => 0,
                        'ret' => 0,
                        'total' => 0
                    ]
                ];
            }

            $base  = $factura->total_gravada ?? 0;
            $iva   = $factura->total_iva ?? 0;
            $valor = $factura->monto_total_operacion ?? 0;
            $ret   = $factura->iva_rete1 ?? 0;
            $total = $factura->total_pagar ?? 0;

            if ($factura->anulada) {
                $base = $iva = $valor = $ret = $total = 0;
            }

            if ($factura->tipo_dte == '05') {
                $base *= -1;
                $iva *= -1;
                $valor *= -1;
                $ret *= -1;
                $total *= -1;
            }

            $reporte[$cliente][$tipoDte]['facturas'][] = $factura;

            $reporte[$cliente][$tipoDte]['totales']['base']  += $base;
            $reporte[$cliente][$tipoDte]['totales']['iva']   += $iva;
            $reporte[$cliente][$tipoDte]['totales']['valor'] += $valor;
            $reporte[$cliente][$tipoDte]['totales']['ret']   += $ret;
            $reporte[$cliente][$tipoDte]['totales']['total'] += $total;
        }

        $data = [
            'desde'       => $desde,
            'hasta'       => $hasta,
            'reporte'     => $reporte,
            'generado_en' => date('d/m/Y H:i')
        ];

        $html = view('reports/facturacion/clientes/ventas_cliente_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }
    public function facturacionPDF()
    {
        $facturaModel = new FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $modo  = $this->request->getGet('modo') ?? 'resumen';
        $tipo  = $this->request->getGet('tipo_documento');
        $cliente_id = $this->request->getGet('cliente_id');

        $query = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('fecha_emision >=', $desde)
            ->where('fecha_emision <=', $hasta)
            ->where('tipo_dte !=', '14'); // EXCLUIR SUJETO EXCLUIDO

        if (!empty($tipo)) {
            $query->where('tipo_dte', $tipo);
        }

        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        $facturas = $query
            ->orderBy('tipo_dte', 'ASC')
            ->orderBy('fecha_emision', 'ASC')
            ->orderBy('numero_control', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $fecha = $factura->fecha_emision;
            $tipoDte = $factura->tipo_dte;

            if ($modo === 'resumen') {

                if (!isset($reporte[$fecha][$tipoDte])) {

                    $reporte[$fecha][$tipoDte] = [
                        'base'  => 0,
                        'iva'   => 0,
                        'valor' => 0,
                        'ret'   => 0,
                        'total' => 0
                    ];
                }

                $base  = $factura->total_gravada ?? 0;
                $iva   = $factura->total_iva ?? 0;
                $valor = $factura->monto_total_operacion ?? 0;
                $ret   = $factura->iva_rete1 ?? 0;
                $total = $factura->total_pagar ?? 0;

                $esAnulada = $factura->anulada == 1;

                if ($esAnulada) {
                    $base = $iva = $valor = $ret = $total = 0;
                }

                if ($factura->tipo_dte == '05') {
                    $base  *= -1;
                    $iva   *= -1;
                    $valor *= -1;
                    $ret   *= -1;
                    $total *= -1;
                }

                $reporte[$fecha][$tipoDte]['base']  += $base;
                $reporte[$fecha][$tipoDte]['iva']   += $iva;
                $reporte[$fecha][$tipoDte]['valor'] += $valor;
                $reporte[$fecha][$tipoDte]['ret']   += $ret;
                $reporte[$fecha][$tipoDte]['total'] += $total;
            } else {

                if (!isset($reporte[$tipoDte][$fecha])) {
                    $reporte[$tipoDte][$fecha] = [
                        'facturas' => [],
                        'total'    => 0
                    ];
                }

                $reporte[$tipoDte][$fecha]['facturas'][] = $factura;
                $reporte[$tipoDte][$fecha]['total'] += $factura->monto_total_operacion;
            }
        }

        ksort($reporte);

        $cliente = null;

        if (!empty($cliente_id)) {

            $clienteModel = new ClienteModel();
            $cliente = $clienteModel->find($cliente_id);
        }

        $data = [
            'desde'       => $desde,
            'hasta'       => $hasta,
            'modo'        => $modo,
            'reporte'     => $reporte,
            'cliente'     => $cliente,
            'generado_en' => date('d/m/Y H:i')
        ];

        $view = $modo === 'resumen'
            ? 'reports/facturacion/facturacion_resumen_pdf'
            : 'reports/facturacion/facturacion_detalle_pdf';

        $html = view($view, $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }

    public function facturacionExcel()
    {
        $facturaModel = new FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $tipo  = $this->request->getGet('tipo_documento');
        $cliente_id = $this->request->getGet('cliente_id');

        $query = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('fecha_emision >=', $desde)
            ->where('fecha_emision <=', $hasta)
            ->where('tipo_dte !=', '14');

        if (!empty($tipo)) {
            $query->where('tipo_dte', $tipo);
        }

        /* FILTRO CLIENTE */
        if (!empty($cliente_id)) {
            $query->where('facturas_head.receptor_id', $cliente_id);
        }

        $facturas = $query
            ->orderBy('tipo_dte', 'ASC')
            ->orderBy('fecha_emision', 'ASC')
            ->orderBy('numero_control', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $fecha = $factura->fecha_emision;
            $tipoDte = $factura->tipo_dte;

            if (!isset($reporte[$tipoDte][$fecha])) {
                $reporte[$tipoDte][$fecha] = [
                    'facturas' => []
                ];
            }

            $reporte[$tipoDte][$fecha]['facturas'][] = $factura;
        }

        /* BUSCAR CLIENTE PARA ENCABEZADO */
        $cliente = null;

        if (!empty($cliente_id)) {
            $clienteModel = new ClienteModel();
            $cliente = $clienteModel->find($cliente_id);
        }

        $data = [
            'desde' => $desde,
            'hasta' => $hasta,
            'reporte' => $reporte,
            'cliente' => $cliente,
            'generado_en' => date('d/m/Y H:i')
        ];

        $filename = "reporte_facturacion_" . date('Ymd_His') . ".xls";

        $html = "\xEF\xBB\xBF" . view('reports/facturacion/facturacion_detalle_excel', $data);

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setBody($html);
    }

    public function estadoCuentaClientePDF()
    {
        $db = \Config\Database::connect();

        $cliente_id = $this->request->getGet('cliente_id');

        if (!$cliente_id) {
            return "Debe seleccionar un cliente";
        }

        $facturaModel = new FacturaHeadModel();

        $cliente = $db->table('clientes')
            ->where('id', $cliente_id)
            ->get()
            ->getRow();

        $movimientos = [];

        //FACTURAS Y NOTAS DE CREDITO

        $docs = $facturaModel
            ->where('receptor_id', $cliente_id)
            ->where('anulada', 0)
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();

        foreach ($docs as $d) {

            $correlativo = substr($d->numero_control, -6);

            // FACTURAS
            if ($d->tipo_dte == '01' || $d->tipo_dte == '03') {

                $tipo = $d->tipo_dte == '03' ? 'CCF' : 'FCF';

                $movimientos[] = (object)[
                    'fecha' => $d->fecha_emision,
                    'tipo' => $tipo,
                    'numDoc' => $correlativo,
                    'asociado' => '-',
                    'cargo' => $d->monto_total_operacion,
                    'abono' => 0
                ];
            }

            // NOTAS DE CREDITO
            if ($d->tipo_dte == '05') {

                $facturaRelacionada = $db->table('facturas_head')
                    ->select('numero_control')
                    ->where('codigo_generacion', $d->codigo_generacion_relacionado)
                    ->get()
                    ->getRow();

                $asociado = '-';

                if ($facturaRelacionada) {
                    $asociado = 'CCF ' . substr($facturaRelacionada->numero_control, -6);
                }

                $movimientos[] = (object)[
                    'fecha' => $d->fecha_emision,
                    'tipo' => 'NC',
                    'numDoc' => $correlativo,
                    'asociado' => $asociado,
                    'cargo' => 0,
                    'abono' => $d->monto_total_operacion
                ];
            }
        }

        //Pagos

        $pagos = $db->table('pagos_details')
            ->select('
            pagos_head.fecha_pago,
            pagos_head.numero_recupero,
            pagos_details.monto,
            facturas_head.numero_control
        ')
            ->join('pagos_head', 'pagos_head.id = pagos_details.pago_id')
            ->join('facturas_head', 'facturas_head.id = pagos_details.factura_id')
            ->where('pagos_head.cliente_id', $cliente_id)
            ->where('pagos_details.anulado', 0)
            ->get()
            ->getResult();

        foreach ($pagos as $p) {

            $movimientos[] = (object)[
                'fecha' => $p->fecha_pago,
                'tipo' => 'ABONO',
                'numDoc' => str_pad($p->numero_recupero, 6, '0', STR_PAD_LEFT),
                'asociado' => 'CCF ' . substr($p->numero_control, -6),
                'cargo' => 0,
                'abono' => $p->monto
            ];
        }

        //Ordenar por fecha

        usort($movimientos, function ($a, $b) {
            return strtotime($a->fecha) - strtotime($b->fecha);
        });

        //Totales

        $totalCargo = 0;
        $totalAbono = 0;

        foreach ($movimientos as $m) {

            $totalCargo += $m->cargo;
            $totalAbono += $m->abono;
        }

        $saldo = $totalCargo - $totalAbono;

        $data = [
            'cliente' => $cliente,
            'movimientos' => $movimientos,
            'totalCargo' => $totalCargo,
            'totalAbono' => $totalAbono,
            'saldo' => $saldo,
            'generado_en' => date('d/m/Y h:i A')
        ];

        $html = view('reports/estadodecuenta/estado_cuenta_cliente_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }

    public function facturacionVendedoresPDF()
    {
        $facturaModel = new FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $seller_ids = $this->request->getGet('vendedores');
        $agrupar = $this->request->getGet('agrupar') ?? 'vendedor';
        $nivel = $this->request->getGet('nivel') ?? 'dia';

        $query = $facturaModel
            ->select('
            facturas_head.*,
            clientes.nombre as cliente_nombre,
            sellers.seller as vendedor_nombre
        ')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->where('fecha_emision >=', $desde)
            ->where('fecha_emision <=', $hasta)
            ->where('tipo_dte !=', '14');

        if (!empty($seller_ids)) {

            if (!is_array($seller_ids)) {
                $seller_ids = [$seller_ids];
            }

            $query->whereIn('facturas_head.vendedor_id', $seller_ids);
        }

        $facturas = $query
            ->orderBy('sellers.seller', 'ASC')
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();

        if (empty($facturas)) {

            $data = [
                'desde' => $desde,
                'hasta' => $hasta,
                'sin_datos' => true,
                'generado_en' => date('d/m/Y H:i')
            ];

            $html = view('reports/facturacion/vendedores/vendedores_resumen_pdf', $data);

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $this->applyPdfHeader($dompdf);

            return $this->response
                ->setContentType('application/pdf')
                ->setBody($dompdf->output());
        }

        $reporte = [];

        foreach ($facturas as $factura) {

            $vendedor = $factura->vendedor_nombre ?? 'Sin vendedor';
            $fecha = $factura->fecha_emision;

            $base  = $factura->total_gravada ?? 0;
            $iva   = $factura->total_iva ?? 0;
            $valor = $factura->monto_total_operacion ?? 0;
            $ret   = $factura->iva_rete1 ?? 0;
            $total = $factura->total_pagar ?? 0;

            if ($factura->anulada) {
                $base = $iva = $valor = $ret = $total = 0;
            }

            if ($factura->tipo_dte == '05') {
                $base *= -1;
                $iva *= -1;
                $valor *= -1;
                $ret *= -1;
                $total *= -1;
            }

            /*
    =========================================
    NIVEL 1 → RESUMEN POR DIA
    =========================================
    */

            if ($nivel === 'dia') {

                if (!isset($reporte[$fecha][$vendedor])) {

                    $reporte[$fecha][$vendedor] = [
                        'base' => 0,
                        'iva' => 0,
                        'valor' => 0,
                        'ret' => 0,
                        'total' => 0
                    ];
                }

                $reporte[$fecha][$vendedor]['base'] += $base;
                $reporte[$fecha][$vendedor]['iva'] += $iva;
                $reporte[$fecha][$vendedor]['valor'] += $valor;
                $reporte[$fecha][$vendedor]['ret'] += $ret;
                $reporte[$fecha][$vendedor]['total'] += $total;
            }

            /*
    =========================================
    NIVEL 2 → DETALLE DE FACTURAS
    =========================================
    */ elseif ($nivel === 'factura') {

                $reporte[$fecha][] = [
                    'vendedor' => $vendedor,
                    'factura' => $factura->numero_control,
                    'base' => $base,
                    'iva' => $iva,
                    'valor' => $valor,
                    'ret' => $ret,
                    'total' => $total
                ];
            }
        }

        ksort($reporte);

        $data = [
            'desde' => $desde,
            'hasta' => $hasta,
            'reporte' => $reporte,
            'agrupar' => $agrupar,
            'nivel' => $nivel,
            'generado_en' => date('d/m/Y H:i')
        ];

        $html = view('reports/facturacion/vendedores/vendedores_resumen_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        /* HEADER + PAGINADO */
        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }
    public function ventasVendedoresExcel()
    {
        $facturaModel = new FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $seller_ids = $this->request->getGet('vendedores');
        $nivel = $this->request->getGet('nivel') ?? 'dia';
        $agrupar = $this->request->getGet('agrupar') ?? 'vendedor';

        $query = $facturaModel
            ->select('
        facturas_head.*,
        clientes.nombre as cliente_nombre,
        sellers.seller as vendedor_nombre
    ')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->where('fecha_emision >=', $desde)
            ->where('fecha_emision <=', $hasta)
            ->where('tipo_dte !=', '14');

        if (!empty($seller_ids)) {

            if (!is_array($seller_ids)) {
                $seller_ids = [$seller_ids];
            }

            $query->whereIn('facturas_head.vendedor_id', $seller_ids);
        }

        $facturas = $query
            ->orderBy('sellers.seller', 'ASC')
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $vendedor = $factura->vendedor_nombre ?? 'Sin vendedor';
            $fecha = $factura->fecha_emision;

            $base  = $factura->total_gravada ?? 0;
            $iva   = $factura->total_iva ?? 0;
            $valor = $factura->monto_total_operacion ?? 0;
            $ret   = $factura->iva_rete1 ?? 0;
            $total = $factura->total_pagar ?? 0;

            if ($factura->anulada) {
                $base = $iva = $valor = $ret = $total = 0;
            }

            if ($factura->tipo_dte == '05') {
                $base *= -1;
                $iva *= -1;
                $valor *= -1;
                $ret *= -1;
                $total *= -1;
            }

            if ($nivel === 'dia') {

                if (!isset($reporte[$fecha][$vendedor])) {

                    $reporte[$fecha][$vendedor] = [
                        'base' => 0,
                        'iva' => 0,
                        'valor' => 0,
                        'ret' => 0,
                        'total' => 0
                    ];
                }

                $reporte[$fecha][$vendedor]['base'] += $base;
                $reporte[$fecha][$vendedor]['iva'] += $iva;
                $reporte[$fecha][$vendedor]['valor'] += $valor;
                $reporte[$fecha][$vendedor]['ret'] += $ret;
                $reporte[$fecha][$vendedor]['total'] += $total;
            } elseif ($nivel === 'factura') {

                $reporte[$fecha][] = [
                    'vendedor' => $vendedor,
                    'factura' => $factura->numero_control,
                    'base' => $base,
                    'iva' => $iva,
                    'valor' => $valor,
                    'ret' => $ret,
                    'total' => $total
                ];
            }
        }

        $data = [
            'desde' => $desde,
            'hasta' => $hasta,
            'reporte' => $reporte,
            'agrupar' => $agrupar,
            'nivel' => $nivel,
            'generado_en' => date('d/m/Y H:i')
        ];

        $filename = "ventas_vendedores_" . date('Ymd_His') . ".xls";

        $html = "\xEF\xBB\xBF" . view('reports/facturacion/vendedores/vendedores_excel', $data);

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setBody($html);
    }
}
