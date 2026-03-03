<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\FacturaHeadModel;
use App\Models\ClienteModel;

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

        return view('reports/saldos_antiguedad', [
            'reporte' => $reporte,
            'fecha'   => $hoy
        ]);
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

        // ⚠ Verificación rápida (puedes probar esto una vez)
        // dd($facturas);

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

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }
    public function saldosAntiguedadVendedorDetallePDF()
    {
        $facturaModel = new FacturaHeadModel();
        $db = \Config\Database::connect();

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

            $vendedorKey = $factura->vendedor_id ?? 0;

            if (!isset($reporte[$vendedorKey])) {
                $reporte[$vendedorKey] = [
                    'vendedor' => $factura->vendedor_nombre ?? 'Sin vendedor',
                    'facturas' => [],
                    'totales'  => [
                        'total_facturas' => 0,
                        'total_saldo'    => 0
                    ]
                ];
            }

            // Obtener pagos vivos
            $pagos = $db->table('pagos_details')
                ->select('pagos_details.*, pagos_head.fecha_pago')
                ->join('pagos_head', 'pagos_head.id = pagos_details.pago_id', 'left')
                ->where('pagos_details.factura_id', $factura->id)
                ->where('pagos_details.anulado', 0)
                ->get()
                ->getResult();

            $reporte[$vendedorKey]['facturas'][] = [
                'factura' => $factura,
                'dias'    => $dias,
                'pagos'   => $pagos
            ];

            $reporte[$vendedorKey]['totales']['total_facturas'] += $factura->monto_total_operacion;
            $reporte[$vendedorKey]['totales']['total_saldo']    += $factura->saldo;
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
            520, 820,
            "Página {PAGE_NUM} de {PAGE_COUNT}",
            null,
            8,
            array(0, 0, 0)
        );
        
        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }
}
