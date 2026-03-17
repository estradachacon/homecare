<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\FacturaHeadModel;
use App\Models\ClienteModel;
use App\Models\SettingModel;
use App\Models\QuedanModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Reader\Html as HtmlReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Dompdf\Dompdf;
use Dompdf\Options;

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
        $logo = $setting->logo ?? null;

        $canvas = $dompdf->getCanvas();
        $fontMetrics = $dompdf->getFontMetrics();

        $fontBold = $fontMetrics->getFont("DejaVu Sans", "bold");
        $fontNormal = $fontMetrics->getFont("DejaVu Sans", "normal");

        $logoPath = $logo ? FCPATH . 'upload/settings/' . $logo : null;

        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($companyName, $fontBold, $fontNormal, $logoPath) {

            $pageWidth = $canvas->get_width();

            /* EMPRESA CENTRADA */
            $textWidth = $fontMetrics->getTextWidth($companyName, $fontBold, 12);
            $x = ($pageWidth - $textWidth) / 2;

            $canvas->text($x, 20, $companyName, $fontBold, 12);

            /* PAGINADO */
            $canvas->text(
                $pageWidth - 120,
                820,
                "Página $pageNumber de $pageCount",
                $fontNormal,
                8
            );

            /* LOGO SOLO EN PAGINA 1 */
            if ($pageNumber == 1 && $logoPath && file_exists($logoPath)) {

                $canvas->image(
                    $logoPath,
                    $pageWidth - 120, // derecha
                    20,
                    80,
                    45
                );
            }
        });
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
        $settingModel = new SettingModel();
        $setting = $settingModel->first();

        $logo = $setting->logo ?? null;
        $companyName = $setting->company_name ?? 'Mi Empresa';

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

        $this->applyPdfHeader($dompdf);

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
            ->where('facturas_head.tipo_dte !=', '14')
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
        $granTotal = [
            '0_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            '90_mas' => 0,
            'total' => 0
        ];
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

            $granTotal['0_30'] += $r0;
            $granTotal['31_60'] += $r30;
            $granTotal['61_90'] += $r60;
            $granTotal['90_mas'] += $r90;
            $granTotal['total'] += $saldo;

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

        foreach ($reporte as $vendedorKey => $vendedor) {

            $totales = [
                '0_30' => 0,
                '31_60' => 0,
                '61_90' => 0,
                '90_mas' => 0,
                'total' => 0
            ];

            foreach ($vendedor['clientes'] as $cliente) {

                $totales['0_30']  += $cliente['totales']['0_30'];
                $totales['31_60'] += $cliente['totales']['31_60'];
                $totales['61_90'] += $cliente['totales']['61_90'];
                $totales['90_mas'] += $cliente['totales']['90_mas'];
                $totales['total'] += $cliente['totales']['total'];
            }

            $reporte[$vendedorKey]['totales_vendedor'] = $totales;
        }

        $data = [
            'reporte'     => $reporte,
            'fecha'       => $fecha_corte,
            'generado_en' => date('d/m/Y H:i'),
            'granTotal'   => $granTotal
        ];

        $html = view('reports/vendedor/saldos_antiguedad_vendedor_detalle_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->render();

        $this->applyPdfHeader($dompdf);

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



    // REPORTES DE FACTURACIÓN
    public function ventasTipoPDF()
    {
        $facturaModel = new FacturaHeadModel();
        $db = \Config\Database::connect();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');

        $clasificado = $this->request->getGet('clasificado') ?? 'vendedor';
        $nivel = $this->request->getGet('nivel') ?? 'detalle';

        $tiposVenta = $this->request->getGet('tipo_venta');
        $vendedores = $this->request->getGet('vendedores');

        $mostrarItems = $this->request->getGet('mostrar_items');
        $saltoTipo = $this->request->getGet('salto_tipo');

        $query = $facturaModel
            ->select('
            facturas_head.*,
            clientes.nombre as cliente_nombre,
            sellers.seller as vendedor_nombre,
            tv.nombre_tipo_venta
        ')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta tv', 'tv.id = facturas_head.tipo_venta', 'left')
            ->where('facturas_head.fecha_emision >=', $desde)
            ->where('facturas_head.fecha_emision <=', $hasta)
            ->where('facturas_head.tipo_dte !=', '14');

        if (!empty($tiposVenta)) {
            $query->whereIn('facturas_head.tipo_venta', $tiposVenta);
        }

        if (!empty($vendedores)) {
            $query->whereIn('facturas_head.vendedor_id', $vendedores);
        }

        $facturas = $query
            ->orderBy('tv.nombre_tipo_venta', 'ASC')
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->orderBy('facturas_head.numero_control', 'ASC')
            ->findAll();

        $reporte = [];

        foreach ($facturas as $factura) {

            $tipoVenta = $factura->nombre_tipo_venta ?? 'SIN TIPO';

            $grupo = ($clasificado === 'cliente')
                ? $factura->cliente_nombre
                : ($factura->vendedor_nombre ?? 'Sin vendedor');

            if (!isset($reporte[$tipoVenta])) {
                $reporte[$tipoVenta] = [];
            }

            if (!isset($reporte[$tipoVenta][$grupo])) {
                $reporte[$tipoVenta][$grupo] = [
                    'documentos' => [],
                    'total' => 0
                ];
            }

            $items = [];

            if ($mostrarItems) {

                $items = $db->table('factura_detalles')
                    ->where('factura_id', $factura->id)
                    ->get()
                    ->getResult();
            }

            $reporte[$tipoVenta][$grupo]['documentos'][] = [
                'factura' => $factura,
                'items' => $items
            ];

            $reporte[$tipoVenta][$grupo]['total'] += $factura->monto_total_operacion;
        }

        $data = [
            'reporte' => $reporte,
            'desde' => $desde,
            'hasta' => $hasta,
            'clasificado' => $clasificado,
            'mostrarItems' => $mostrarItems,
            'saltoTipo' => $saltoTipo,
            'nivel' => $nivel,
            'generado_en' => date('d/m/Y H:i')
        ];

        $html = view('reports/facturacion/tipo/ventas_tipo_pdf', $data);

        $dompdf = new \Dompdf\Dompdf();

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $this->applyPdfHeader($dompdf);

        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }

    public function ventasTipoExcel()
    {
        helper('dte');

        $facturaModel = new FacturaHeadModel();
        $db = \Config\Database::connect();

        $siglas = dte_siglas();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');

        $clasificado = $this->request->getGet('clasificado') ?? 'vendedor';

        $tiposVenta = $this->request->getGet('tipo_venta');
        $vendedores = $this->request->getGet('vendedores');

        $mostrarItems = $this->request->getGet('mostrar_items');

        $query = $facturaModel
            ->select('
            facturas_head.*,
            clientes.nombre as cliente_nombre,
            sellers.seller as vendedor_nombre,
            tv.nombre_tipo_venta
        ')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta tv', 'tv.id = facturas_head.tipo_venta', 'left')
            ->where('facturas_head.fecha_emision >=', $desde)
            ->where('facturas_head.fecha_emision <=', $hasta)
            ->where('facturas_head.tipo_dte !=', '14');

        if (!empty($tiposVenta)) {
            $query->whereIn('facturas_head.tipo_venta', $tiposVenta);
        }

        if (!empty($vendedores)) {
            $query->whereIn('facturas_head.vendedor_id', $vendedores);
        }

        $facturas = $query
            ->orderBy('tv.nombre_tipo_venta', 'ASC')
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->orderBy('facturas_head.numero_control', 'ASC')
            ->findAll();

        /*
    ==========================
    AGRUPAR DATA
    ==========================
    */

        $reporte = [];

        foreach ($facturas as $factura) {

            $tipoVenta = $factura->nombre_tipo_venta ?? 'SIN TIPO';

            $grupo = ($clasificado === 'cliente')
                ? $factura->cliente_nombre
                : ($factura->vendedor_nombre ?? 'Sin vendedor');

            if (!isset($reporte[$tipoVenta])) {
                $reporte[$tipoVenta] = [];
            }

            if (!isset($reporte[$tipoVenta][$grupo])) {
                $reporte[$tipoVenta][$grupo] = [
                    'documentos' => []
                ];
            }

            $items = [];

            if ($mostrarItems) {
                $items = $db->table('factura_detalles')
                    ->where('factura_id', $factura->id)
                    ->get()
                    ->getResult();
            }

            $reporte[$tipoVenta][$grupo]['documentos'][] = [
                'factura' => $factura,
                'items' => $items
            ];
        }

        /*
    ==========================
    CREAR EXCEL
    ==========================
    */

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $row = 1;

        $sheet->setCellValue("A$row", "REPORTE DE VENTAS POR TIPO DE VENTA");
        $sheet->mergeCells("A$row:I$row");

        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);

        $row += 2;

        $sheet->setCellValue("A$row", "Desde:");
        $sheet->setCellValue("B$row", date('d/m/Y', strtotime($desde)));

        $sheet->setCellValue("D$row", "Hasta:");
        $sheet->setCellValue("E$row", date('d/m/Y', strtotime($hasta)));

        $row += 2;

        /*
    ==========================
    GRAN TOTAL
    ==========================
    */

        $gt_base = 0;
        $gt_iva = 0;
        $gt_valor = 0;
        $gt_ret = 0;
        $gt_total = 0;

        foreach ($reporte as $tipoVenta => $grupos) {

            $sheet->setCellValue("A$row", "TIPO DE VENTA: " . $tipoVenta);
            $sheet->getStyle("A$row")->getFont()->setBold(true);
            $row++;

            foreach ($grupos as $grupo => $data) {

                $sheet->setCellValue("A$row", strtoupper($clasificado) . ": " . $grupo);
                $sheet->getStyle("A$row")->getFont()->setBold(true);
                $row++;

                $sheet->fromArray([
                    'Fecha',
                    'Tipo',
                    'Número',
                    'Cliente',
                    'Valor S/IVA',
                    'IVA',
                    'Valor Venta',
                    '1% Ret',
                    'Total'
                ], NULL, "A$row");

                $sheet->getStyle("A$row:I$row")->getFont()->setBold(true);

                $sheet->getStyle("A$row:I$row")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('1F4E79');

                $sheet->getStyle("A$row:I$row")->getFont()->getColor()->setARGB('FFFFFF');

                $row++;

                /*
            ==========================
            TOTAL GRUPO
            ==========================
            */

                $grp_base = 0;
                $grp_iva = 0;
                $grp_valor = 0;
                $grp_ret = 0;
                $grp_total = 0;

                foreach ($data['documentos'] as $doc) {

                    $factura = $doc['factura'];

                    $base  = $factura->total_gravada ?? 0;
                    $iva   = $factura->total_iva ?? 0;
                    $valor = $factura->monto_total_operacion ?? 0;
                    $ret   = $factura->iva_rete1 ?? 0;
                    $total = $factura->total_pagar ?? 0;

                    $grp_base += $base;
                    $grp_iva += $iva;
                    $grp_valor += $valor;
                    $grp_ret += $ret;
                    $grp_total += $total;

                    $gt_base += $base;
                    $gt_iva += $iva;
                    $gt_valor += $valor;
                    $gt_ret += $ret;
                    $gt_total += $total;

                    $sheet->setCellValue("A$row", date('d/m/Y', strtotime($factura->fecha_emision)));
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

                    $row++;

                    /*
                ITEMS CON VALORES
                */

                    if ($mostrarItems && !empty($doc['items'])) {

                        foreach ($doc['items'] as $item) {

                            $precio = $item->precio_unitario ?? 0;
                            $cantidad = $item->cantidad ?? 1;

                            $total_item = $precio * $cantidad;

                            $base_item = $total_item / 1.13;
                            $iva_item  = $total_item - $base_item;

                            $sheet->setCellValue("B$row", "QTY: " . intval($cantidad));
                            $sheet->setCellValue("C$row", $item->descripcion);

                            $sheet->setCellValue("E$row", $base_item);
                            $sheet->setCellValue("F$row", $iva_item);
                            $sheet->setCellValue("G$row", $total_item);

                            $sheet->getStyle("E$row:G$row")
                                ->getNumberFormat()
                                ->setFormatCode('"$"#,##0.00');

                            $row++;
                        }
                    }
                }

                /*
            ==========================
            TOTAL GRUPO
            ==========================
            */

                $sheet->setCellValue("A$row", "TOTAL " . strtoupper($clasificado));

                $sheet->setCellValue("E$row", $grp_base);
                $sheet->setCellValue("F$row", $grp_iva);
                $sheet->setCellValue("G$row", $grp_valor);
                $sheet->setCellValue("H$row", $grp_ret);
                $sheet->setCellValue("I$row", $grp_total);

                $sheet->getStyle("A$row:I$row")->getFont()->setBold(true);

                $row += 2;
            }
        }

        /*
    ==========================
    GRAN TOTAL
    ==========================
    */

        $sheet->setCellValue("A$row", "GRAN TOTAL");

        $sheet->setCellValue("E$row", $gt_base);
        $sheet->setCellValue("F$row", $gt_iva);
        $sheet->setCellValue("G$row", $gt_valor);
        $sheet->setCellValue("H$row", $gt_ret);
        $sheet->setCellValue("I$row", $gt_total);

        $sheet->getStyle("A$row:I$row")->getFont()->setBold(true);

        $sheet->getStyle("E$row:I$row")
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        /*
    ==========================
    AJUSTAR COLUMNAS
    ==========================
    */

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $filename = "reporte_ventas_tipo.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

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
        /* LOGO */

        $settingModel = new SettingModel();
        $setting = $settingModel->first();

        if (!empty($setting->logo)) {

            $logoPath = FCPATH . 'upload/settings/' . $setting->logo;

            if (file_exists($logoPath)) {

                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo empresa');
                $drawing->setPath($logoPath);

                $drawing->setHeight(72);

                // MISMA POSICIÓN QUE EN FACTURACION
                $drawing->setCoordinates('H1');

                $drawing->setOffsetX(10);
                $drawing->setOffsetY(5);

                $drawing->setWorksheet($sheet);
            }
        }
        $row = 1;

        /* TITULO */

        $sheet->setCellValue("A$row", "REPORTE DE VENTAS POR CLIENTE");
        $sheet->mergeCells("A$row:I$row");

        $row += 4;

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

        /* GENERAR HTML DEL VIEW */
        $html = view('reports/facturacion/facturacion_detalle_excel', $data);

        $reader = new HtmlReader();
        $spreadsheet = $reader->loadFromString($html);

        $sheet = $spreadsheet->getActiveSheet();

        /* LOGO */

        $settingModel = new SettingModel();
        $setting = $settingModel->first();

        if (!empty($setting->logo)) {

            $logoPath = FCPATH . 'upload/settings/' . $setting->logo;

            if (file_exists($logoPath)) {

                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo empresa');
                $drawing->setPath($logoPath);

                $drawing->setHeight(76); // tamaño del logo

                $drawing->setCoordinates('H1'); // celda donde aparece

                $drawing->setOffsetX(10);
                $drawing->setOffsetY(5);

                $drawing->setWorksheet($sheet);
            }
        }

        /* ESTILOS */
        $sheet->getStyle('A6:I6')->getFont()->setBold(true);

        $sheet->getStyle('A6:I6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('1F4E79');

        $sheet->getStyle('A6:I6')->getFont()->getColor()->setARGB('FFFFFF');

        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle("E7:I$highestRow")
            ->getNumberFormat()
            ->setFormatCode('"$"#,##0.00');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $filename = "reporte_facturacion_" . date('Ymd_His') . ".xlsx";

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($this->writeSpreadsheet($writer));
    }
    private function writeSpreadsheet($writer)
    {
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }


    //Reportes de quedan
    function estadoFactura($total, $saldo)
    {
        if ($saldo == 0) {
            return 'Pagado';
        }

        if ($saldo < $total) {
            return 'Pagado parcialmente';
        }

        return 'Activo';
    }

    public function quedansPdf()
    {
        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');
        $cliente = $this->request->getGet('cliente_id');

        $model = new QuedanModel();

        $data['quedans'] = $model->getReporteQuedans($desde, $hasta, $cliente);

        // 🔥 HTML
        $html = view('reports/quedans/quedans_pdf', $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        // 🔥 AQUI VA TU HEADER
        $this->applyPdfHeader($dompdf);

        // 🔥 OUTPUT
        return $this->response
            ->setContentType('application/pdf')
            ->setBody($dompdf->output());
    }

    public function quedansExcel()
    {
        $quedanModel = new QuedanModel();
        $detalleModel = new \App\Models\QuedanFacturaModel();

        helper('dte');

        $siglas = dte_siglas();

        $quedans = $quedanModel->getReporteQuedans();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 🔥 ENCABEZADOS
        $sheet->setCellValue('A1', 'Quedan');
        $sheet->setCellValue('B1', 'Cliente');
        $sheet->setCellValue('C1', 'Documento');
        $sheet->setCellValue('D1', 'Fecha');
        $sheet->setCellValue('E1', 'Total');
        $sheet->setCellValue('F1', 'Aplicado');
        $sheet->setCellValue('G1', 'Saldo');
        $sheet->setCellValue('H1', 'Estado');
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);
        $row = 2;
        $granTotal = 0;

        foreach ($quedans as $q) {

            $detalles = $detalleModel->getFacturasPorQuedan($q->id);

            foreach ($detalles as $d) {

                // 🔥 FORMATO DOCUMENTO
                $correlativo = str_pad(substr($d->numero_control ?? '', -6), 6, '0', STR_PAD_LEFT);
                $partes = explode('-', $d->numero_control);
                $tipoCodigo = $partes[1] ?? null;
                $sigla = $siglas[$tipoCodigo] ?? 'DOC';

                $documento = $sigla . ' ' . $correlativo;

                // 🔥 ESTADO
                if (($d->anulada ?? 0) == 1) {
                    $estado = 'Anulada';
                } elseif (($d->saldo ?? 0) == 0) {
                    $estado = 'Pagado';
                } elseif (($d->saldo ?? 0) < ($d->total_pagar ?? 0)) {
                    $estado = 'Parcial';
                } else {
                    $estado = 'Activo';
                }
                $estadoCell = "H$row";

                if ($estado == 'Activo') {
                    $sheet->getStyle($estadoCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FDECEA']
                        ],
                        'font' => ['color' => ['rgb' => 'C0392B']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                }

                if ($estado == 'Parcial') {
                    $sheet->getStyle($estadoCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF4E5']
                        ],
                        'font' => ['color' => ['rgb' => 'E67E22']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                }

                if ($estado == 'Pagado') {
                    $sheet->getStyle($estadoCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E8F8F5']
                        ],
                        'font' => ['color' => ['rgb' => '1E8449']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                }

                if ($estado == 'Anulada') {
                    $sheet->getStyle($estadoCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F2F2F2']
                        ],
                        'font' => ['color' => ['rgb' => '7F8C8D']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                }
                // 🔥 SALDO QUEDAN
                $saldo = ($d->anulada ?? 0) == 0 ? ($d->saldo ?? 0) : 0;

                $granTotal += $saldo;

                // 🔥 ESCRIBIR FILA
                $sheet->setCellValue("A$row", $q->numero_quedan);
                $sheet->setCellValue("B$row", $q->cliente_nombre);
                $sheet->setCellValue("C$row", $documento);
                $sheet->setCellValue("D$row", $d->fecha_emision);
                $sheet->setCellValue("E$row", $d->total_pagar);
                $sheet->setCellValue("F$row", $d->monto_aplicado);
                $sheet->setCellValue("G$row", $saldo);
                $sheet->setCellValue("H$row", $estado);
                $sheet->getStyle("A1:H$row")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '999999']
                        ]
                    ]
                ]);
                $row++;
            }

            // 🔥 FILA DE TOTAL POR QUEDAN
            $sheet->setCellValue("F$row", 'Saldo Quedan:');
            $sheet->setCellValue("G$row", "=SUM(G2:G" . ($row - 1) . ")");
            $sheet->getStyle("F$row:G$row")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ]);
            $row++;
        }

        // 🔥 GRAN TOTAL
        $sheet->setCellValue("F$row", 'TOTAL GENERAL:');
        $sheet->setCellValue("G$row", $granTotal);

        // 🔥 FORMATO MONEDA
        $sheet->getStyle("E2:G$row")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');
        $sheet->getStyle("E2:G$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        // 🔥 AUTO SIZE
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 🔥 DESCARGA
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="reporte_quedans.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
