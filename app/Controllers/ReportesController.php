<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\FacturaHeadModel;
use App\Models\ClienteModel;
use App\Models\SettingModel;

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
        $canvas->page_text(520, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 8, array(0, 0, 0));

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

    // Reportes de facturación
    public function facturacionPDF()
    {
        $facturaModel = new FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $modo  = $this->request->getGet('modo') ?? 'resumen';
        $tipo  = $this->request->getGet('tipo_documento');

        $query = $facturaModel
            ->select('facturas_head.*, clientes.nombre as cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('fecha_emision >=', $desde)
            ->where('fecha_emision <=', $hasta)
            ->where('anulada', 0);

        if (!empty($tipo)) {
            $query->where('tipo_dte', $tipo);
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
                    $reporte[$fecha][$tipoDte] = 0;
                }

                $reporte[$fecha][$tipoDte] += $factura->monto_total_operacion;
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

        $data = [
            'desde'       => $desde,
            'hasta'       => $hasta,
            'modo'        => $modo,
            'reporte'     => $reporte,
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
        $canvas = $dompdf->getCanvas();
        $canvas->page_text(
            520,
            820,
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            null,
            8,
            array(0, 0, 0)
        );

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
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

        /*
    ========================================
    FACTURAS Y NOTAS DE CREDITO
    ========================================
    */

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

        /*
    ========================================
    PAGOS
    ========================================
    */

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

        /*
    ========================================
    ORDENAR POR FECHA
    ========================================
    */

        usort($movimientos, function ($a, $b) {
            return strtotime($a->fecha) - strtotime($b->fecha);
        });

        /*
    ========================================
    TOTALES
    ========================================
    */

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

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }
}
