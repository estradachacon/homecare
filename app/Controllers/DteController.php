<?php

namespace App\Controllers;

use App\Models\FacturaHeadModel;
use App\Models\FacturaDetalleModel;
use App\Models\FacturaJsonModel;
use App\Models\EmisorModel;
use App\Services\DteBuilderService;
use App\Services\DteSignerService;
use App\Services\HaciendaApiService;

class DteController extends BaseController
{
    protected FacturaHeadModel    $headModel;
    protected FacturaDetalleModel $detalleModel;
    protected FacturaJsonModel    $jsonModel;

    public function __construct()
    {
        $this->headModel    = new FacturaHeadModel();
        $this->detalleModel = new FacturaDetalleModel();
        $this->jsonModel    = new FacturaJsonModel();
    }

    // ─────────────────────────────────────────────
    //  LISTADO DE DTEs EMITIDOS
    // ─────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $perPage = (int)($this->request->getGet('per_page') ?? 25);
        if (!in_array($perPage, [10, 15, 25, 50, 100, 99999])) $perPage = 25;

        $q = $this->headModel
            ->select('facturas_head.*, clientes.nombre AS cliente_nombre')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.emitido', 1);

        if ($tipoDte = $this->request->getGet('tipo_dte')) {
            $q->where('facturas_head.tipo_dte', $tipoDte);
        }
        if ($estado = $this->request->getGet('estado')) {
            if ($estado === 'anulada') $q->where('facturas_head.anulada', 1);
            else $q->where('facturas_head.anulada', 0);
        }
        if ($fecha = $this->request->getGet('fecha')) {
            $q->where('facturas_head.fecha_emision', $fecha);
        }
        if ($numero = $this->request->getGet('numero')) {
            $q->like('facturas_head.numero_control', $numero, 'before');
        }

        $q->orderBy('facturas_head.fecha_emision', 'DESC')
          ->orderBy('facturas_head.id', 'DESC');

        $dtEs  = $q->paginate($perPage);
        $pager = $this->headModel->pager;

        return view('emisiondte/index', compact('dtEs', 'pager', 'perPage'));
    }

    // ─────────────────────────────────────────────
    //  FORMULARIO EMISIÓN
    // ─────────────────────────────────────────────

    public function new()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $emisor = new EmisorModel();
        $emisor = $emisor->first();

        return view('emisiondte/create', ['emisor' => $emisor]);
    }

    // ─────────────────────────────────────────────
    //  GUARDAR Y ENVIAR DTE (JSON POST)
    // ─────────────────────────────────────────────

    public function store()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $data = $this->request->getJSON(true);

        // Validación mínima
        if (empty($data['tipo_dte']) || empty($data['fecha_emision']) || empty($data['items'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos. Se requiere tipo_dte, fecha_emision e items.',
            ]);
        }

        if (!is_array($data['items']) || count($data['items']) === 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debe incluir al menos un producto.',
            ]);
        }

        try {
            // 1. Construir JSON del DTE
            $builder = new DteBuilderService();

            $dte = match ($data['tipo_dte']) {
                '01'    => $builder->buildFactura($data),
                '03'    => $builder->buildCCF($data),
                '04'    => $builder->buildNR($data),
                default => throw new \InvalidArgumentException("Tipo DTE '{$data['tipo_dte']}' no soportado."),
            };

            // 2. Firmar
            $signer    = new DteSignerService();
            $dteFirmado = $signer->firmar($dte);
            $payload   = $signer->buildPayload($dteFirmado);

            // 3. Transmitir a MH
            $api      = new HaciendaApiService();
            $response = $api->post('fesv/recepciondte', $payload);

            $selloRecibido = null;
            $estadoMh      = 'error';
            $mensajeMh     = null;

            if ($response['http_code'] === 200 && isset($response['body']['selloRecibido'])) {
                $selloRecibido = $response['body']['selloRecibido'];
                $estadoMh      = 'procesado';
            } elseif ($response['http_code'] === 200 && isset($response['body']['estado'])) {
                $estadoMh  = strtolower($response['body']['estado']);
                $mensajeMh = $response['body']['descripcionMsg'] ?? null;
            } else {
                $mensajeMh = $response['error'] ?? json_encode($response['body'] ?? []);
            }

            // 4. Persistir en DB
            $ident   = $dte['identificacion'];
            $resumen = $dte['resumen'];

            $db = \Config\Database::connect();
            $db->transStart();

            $facturaId = $this->headModel->insert([
                'ambiente'           => $ident['ambiente'],
                'tipo_dte'           => $ident['tipoDte'],
                'emitido'            => 1,
                'estado_mh'          => $estadoMh,
                'respuesta_mh'       => json_encode($response['body'] ?? []),
                'numero_control'     => $ident['numeroControl'],
                'codigo_generacion'  => $ident['codigoGeneracion'],
                'fecha_emision'      => $ident['fecEmi'],
                'hora_emision'       => $ident['horEmi'],
                'tipo_moneda'        => 'USD',
                'sello_recibido'     => $selloRecibido,
                'receptor_id'        => $data['cliente_id'] ?? null,
                'total_gravada'      => $resumen['totalGravada'],
                'sub_total'          => $resumen['subTotal'],
                'total_iva'          => $resumen['totalIva'],
                'monto_total_operacion' => $resumen['montoTotalOperacion'],
                'total_pagar'        => $resumen['montoTotalOperacion'],
                'saldo'              => $resumen['montoTotalOperacion'],
                'condicion_operacion'=> $resumen['condicionOperacion'],
                'plazo_credito'      => $data['plazo_credito'] ?? null,
                'vendedor_id'        => session()->get('id'),
                'firma_electronica'  => $dteFirmado['firma'],
                'anulada'            => 0,
            ]);

            foreach ($dte['cuerpoDocumento'] as $item) {
                $this->detalleModel->insert([
                    'factura_id'      => $facturaId,
                    'num_item'        => $item['numItem'],
                    'tipo_item'       => $item['tipoItem'],
                    'cantidad'        => $item['cantidad'],
                    'descripcion'     => $item['descripcion'],
                    'precio_unitario' => $item['precioUni'],
                    'monto_descuento' => $item['montoDescu'],
                    'venta_no_sujeta' => $item['ventaNoSuj'],
                    'venta_exenta'    => $item['ventaExenta'],
                    'venta_gravada'   => $item['ventaGravada'],
                    'iva_item'        => $item['ivaItem'],
                    'codigo'          => $item['codigo'],
                    'unidad_medida'   => $item['uniMedida'],
                ]);
            }

            $this->jsonModel->guardarJson($facturaId, $dteFirmado);

            $db->transComplete();

            if (!$db->transStatus()) {
                throw new \RuntimeException('Error al guardar el DTE en la base de datos.');
            }

            registrar_bitacora(
                'Emitir DTE',
                'Facturación Electrónica',
                "DTE {$ident['tipoDte']} {$ident['numeroControl']} emitido por $" .
                number_format($resumen['montoTotalOperacion'], 2),
                session()->get('id')
            );

            return $this->response->setJSON([
                'success'      => true,
                'estado_mh'    => $estadoMh,
                'sello'        => $selloRecibido,
                'numero'       => $ident['numeroControl'],
                'total'        => $resumen['montoTotalOperacion'],
                'factura_id'   => $facturaId,
                'mensaje'      => $mensajeMh,
            ]);

        } catch (\Throwable $e) {
            log_message('error', '[DteController::store] ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────
    //  DETALLE / VISTA DTE EMITIDO
    // ─────────────────────────────────────────────

    public function show(int $id)
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $dte = $this->headModel
            ->select('facturas_head.*, clientes.nombre AS cliente_nombre, clientes.numero_documento, clientes.nrc')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.id', $id)
            ->where('facturas_head.emitido', 1)
            ->first();

        if (!$dte) {
            return redirect()->to('/emision-dte')->with('error', 'DTE no encontrado.');
        }

        $detalles = $this->detalleModel->where('factura_id', $id)->findAll();
        $jsonDte  = $this->jsonModel->getByFactura($id);

        return view('emisiondte/show', compact('dte', 'detalles', 'jsonDte'));
    }

    // ─────────────────────────────────────────────
    //  CONSULTAR ESTADO EN MH
    // ─────────────────────────────────────────────

    public function consultarEstado(int $id)
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $dte = $this->headModel->find($id);
        if (!$dte || !$dte->emitido) {
            return $this->response->setJSON(['success' => false, 'message' => 'DTE no encontrado.']);
        }

        try {
            $api      = new HaciendaApiService();
            $ambiente = env('hacienda.env', '00');
            $endpoint = "fesv/consultadte?ambiente={$ambiente}&codigoGeneracion={$dte->codigo_generacion}&fechaEmi={$dte->fecha_emision}";
            $response = $api->get($endpoint);

            $estado = $response['body']['estado'] ?? null;
            $sello  = $response['body']['selloRecibido'] ?? $dte->sello_recibido;

            if ($estado) {
                $this->headModel->update($id, [
                    'estado_mh'      => strtolower($estado),
                    'sello_recibido' => $sello ?: $dte->sello_recibido,
                    'respuesta_mh'   => json_encode($response['body'] ?? []),
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'estado'  => $estado,
                'sello'   => $sello,
                'body'    => $response['body'],
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────
    //  PRÓXIMO NÚMERO DE CONTROL (AJAX)
    // ─────────────────────────────────────────────

    public function proximoNumero(string $tipoDte)
    {
        try {
            $builder = new DteBuilderService();
            return $this->response->setJSON([
                'numero' => $builder->siguienteNumeroControl($tipoDte),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['numero' => '—', 'error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────
    //  NOTA DE CRÉDITO — FORMULARIO
    // ─────────────────────────────────────────────

    public function nuevaNc(int $id)
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) return $chk;

        $original = $this->headModel
            ->select('facturas_head.*, clientes.nombre AS cliente_nombre, clientes.numero_documento, clientes.nrc')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->where('facturas_head.id', $id)
            ->where('facturas_head.emitido', 1)
            ->first();

        if (!$original) {
            return redirect()->to('/emision-dte')->with('error', 'Documento original no encontrado.');
        }

        if ($original->anulada) {
            return redirect()->to('/emision-dte/' . $id)->with('error', 'No se puede generar NC de un documento anulado.');
        }

        if (!in_array($original->tipo_dte, ['01', '03'])) {
            return redirect()->to('/emision-dte/' . $id)->with('error', 'Solo se puede generar NC desde Facturas y CCF.');
        }

        $detalles = $this->detalleModel
            ->where('factura_id', $id)
            ->orderBy('num_item', 'ASC')
            ->findAll();

        return view('emisiondte/nc', compact('original', 'detalles'));
    }

    // ─────────────────────────────────────────────
    //  NOTA DE CRÉDITO — GUARDAR Y ENVIAR
    // ─────────────────────────────────────────────

    public function storeNc()
    {
        $chk = requerirPermiso('emitir_dte');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $data = $this->request->getJSON(true);

        if (empty($data['original_id']) || empty($data['items'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos.',
            ]);
        }

        $original = $this->headModel->find($data['original_id']);
        if (!$original || !$original->emitido) {
            return $this->response->setJSON(['success' => false, 'message' => 'Documento original no encontrado.']);
        }

        try {
            $builder    = new DteBuilderService();
            $dte        = $builder->buildNC($data, $original);

            $signer     = new DteSignerService();
            $dteFirmado = $signer->firmar($dte);
            $payload    = $signer->buildPayload($dteFirmado);

            $api      = new HaciendaApiService();
            $response = $api->post('fesv/recepciondte', $payload);

            $selloRecibido = $response['body']['selloRecibido'] ?? null;
            $estadoMh      = $selloRecibido ? 'procesado'
                           : strtolower($response['body']['estado'] ?? 'error');
            $mensajeMh     = $response['body']['descripcionMsg'] ?? $response['error'] ?? null;

            $ident   = $dte['identificacion'];
            $resumen = $dte['resumen'];

            $db = \Config\Database::connect();
            $db->transStart();

            $ncId = $this->headModel->insert([
                'ambiente'              => $ident['ambiente'],
                'tipo_dte'              => '05',
                'emitido'               => 1,
                'estado_mh'             => $estadoMh,
                'respuesta_mh'          => json_encode($response['body'] ?? []),
                'numero_control'        => $ident['numeroControl'],
                'codigo_generacion'     => $ident['codigoGeneracion'],
                'codigo_generacion_relacionado' => $original->codigo_generacion,
                'fecha_emision'         => $ident['fecEmi'],
                'hora_emision'          => $ident['horEmi'],
                'tipo_moneda'           => 'USD',
                'sello_recibido'        => $selloRecibido,
                'receptor_id'           => $original->receptor_id,
                'total_gravada'         => $resumen['totalGravada'],
                'sub_total'             => $resumen['subTotal'],
                'total_iva'             => $resumen['totalIva'],
                'monto_total_operacion' => $resumen['montoTotalOperacion'],
                'total_pagar'           => $resumen['montoTotalOperacion'],
                'saldo'                 => 0,
                'condicion_operacion'   => $resumen['condicionOperacion'],
                'vendedor_id'           => session()->get('id'),
                'firma_electronica'     => $dteFirmado['firma'],
                'anulada'               => 0,
            ]);

            foreach ($dte['cuerpoDocumento'] as $item) {
                $this->detalleModel->insert([
                    'factura_id'      => $ncId,
                    'num_item'        => $item['numItem'],
                    'tipo_item'       => $item['tipoItem'],
                    'cantidad'        => $item['cantidad'],
                    'descripcion'     => $item['descripcion'],
                    'precio_unitario' => $item['precioUni'],
                    'monto_descuento' => $item['montoDescu'],
                    'venta_no_sujeta' => $item['ventaNoSuj'],
                    'venta_exenta'    => $item['ventaExenta'],
                    'venta_gravada'   => $item['ventaGravada'],
                    'iva_item'        => $item['ivaItem'],
                    'codigo'          => $item['codigo'],
                    'unidad_medida'   => $item['uniMedida'],
                ]);
            }

            $this->jsonModel->guardarJson($ncId, $dteFirmado);

            // Reducir saldo del documento original
            $nuevoSaldo = max(0, $original->saldo - $resumen['montoTotalOperacion']);
            $this->headModel->update($original->id, ['saldo' => $nuevoSaldo]);

            $db->transComplete();

            if (!$db->transStatus()) {
                throw new \RuntimeException('Error al guardar la Nota de Crédito.');
            }

            registrar_bitacora(
                'Emitir Nota de Crédito',
                'Facturación Electrónica',
                "NC {$ident['numeroControl']} referencia {$original->numero_control} por $" .
                number_format($resumen['montoTotalOperacion'], 2),
                session()->get('id')
            );

            return $this->response->setJSON([
                'success'    => true,
                'estado_mh'  => $estadoMh,
                'sello'      => $selloRecibido,
                'numero'     => $ident['numeroControl'],
                'total'      => $resumen['montoTotalOperacion'],
                'factura_id' => $ncId,
                'mensaje'    => $mensajeMh,
            ]);

        } catch (\Throwable $e) {
            log_message('error', '[DteController::storeNc] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
