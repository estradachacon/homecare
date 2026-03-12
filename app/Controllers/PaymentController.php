<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\PagosDetailsModel;
use App\Models\TipoVentaModel;
use App\Models\FacturaHeadModel;
use App\Models\PagosHeadModel;
use App\Models\AccountModel;

class PaymentController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_pagos');
        if ($chk !== true) return $chk;

        $model = new PagosHeadModel();

        $model->select('
        pagos_head.*,
        clientes.nombre AS cliente_nombre,

        COALESCE(SUM(CASE WHEN pagos_details.anulado = 0 THEN pagos_details.monto ELSE 0 END),0) AS total_aplicado,
        COALESCE(SUM(CASE WHEN pagos_details.anulado = 1 THEN pagos_details.monto ELSE 0 END),0) AS total_anulado
    ')
            ->join('clientes', 'clientes.id = pagos_head.cliente_id', 'left')
            ->join('pagos_details', 'pagos_details.pago_id = pagos_head.id', 'left')
            ->groupBy('pagos_head.id');

        // ===== FILTROS =====

        $clienteId = $this->request->getGet('cliente_id');
        $estado    = $this->request->getGet('estado');
        $fecha     = $this->request->getGet('fecha');
        $tipoAplicacion = $this->request->getGet('tipo_aplicacion');

        if (is_numeric($clienteId)) {
            $model->where('pagos_head.cliente_id', $clienteId);
        }

        if ($estado === 'activa') {
            $model->where('pagos_head.anulado', 0);
        }

        if ($estado === 'anulada') {
            $model->where('pagos_head.anulado', 1);
        }

        if (!empty($fecha)) {
            $model->where('pagos_head.fecha_pago', $fecha);
        }

        if ($tipoAplicacion === 'con_anulaciones') {
            $model->having('total_anulado >', 0);
        }

        if ($tipoAplicacion === 'sin_efecto') {
            $model->having('total_aplicado', 0);
        }

        if ($tipoAplicacion === 'normal') {
            $model->having('total_anulado', 0);
        }

        $model->orderBy('pagos_head.fecha_pago', 'DESC');

        $pagos = $model->paginate(10);
        $pager = $model->pager;

        if ($this->request->isAJAX()) {

            $tbody = view('pagos/tbody_row', compact('pagos'));
            $pagerHtml = $pager->links('default', 'bootstrap_full');

            return $this->response->setJSON([
                'tbody' => $tbody,
                'pager' => $pagerHtml
            ]);
        }

        return view('pagos/index', compact('pagos', 'pager'));
    }
    public function new()
    {
        $chk = requerirPermiso('crear_pagos');
        if ($chk !== true) return $chk;

        // Traer clientes (si luego quieres Select2 remoto, esto puede quitarse)
        $clienteModel = new \App\Models\ClienteModel();
        $clientes = $clienteModel->orderBy('nombre')->findAll();

        // Traer vendedores
        $sellerModel = new \App\Models\SellerModel();
        $sellers = $sellerModel->orderBy('seller')->findAll();

        return view('pagos/new', [
            'clientes' => $clientes,
            'sellers'  => $sellers
        ]);
    }
    public function show($id)
    {
        $pagoModel = new PagosHeadModel();
        $detalleModel = new PagosDetailsModel();

        $pago = $pagoModel
            ->select('
            pagos_head.*,
            clientes.nombre AS cliente_nombre,
            accounts.name AS cuenta_nombre
        ')
            ->join('clientes', 'clientes.id = pagos_head.cliente_id', 'left')
            ->join('accounts', 'accounts.id = pagos_head.numero_cuenta_bancaria', 'left')
            ->where('pagos_head.id', $id)
            ->first();

        if (!$pago) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $facturas = $detalleModel
            ->select('pagos_details.*, facturas_head.numero_control')
            ->join('facturas_head', 'facturas_head.id = pagos_details.factura_id')
            ->where('pagos_details.pago_id', $id)
            ->findAll();

        // ==============================
        // 🔎 Detectar anulaciones parciales
        // ==============================

        $totalDetalles   = count($facturas);
        $totalAnulados   = 0;
        $totalActivo     = 0;

        foreach ($facturas as $f) {
            if ($f->anulado) {
                $totalAnulados++;
            } else {
                $totalActivo += $f->monto;
            }
        }

        $anulacionParcial = (
            $totalDetalles > 0 &&
            $totalAnulados > 0 &&
            $totalAnulados < $totalDetalles
        );

        return view('pagos/show', compact(
            'pago',
            'facturas',
            'anulacionParcial',
            'totalActivo'
        ));
    }
    public function facturas($pagoId)
    {
        $model = new PagosDetailsModel();

        $facturas = $model
            ->select('facturas_head.numero_control, pagos_details.monto')
            ->join('facturas_head', 'facturas_head.id = pagos_details.factura_id')
            ->where('pagos_details.pago_id', $pagoId)
            ->findAll();

        return $this->response->setJSON($facturas);
    }

    public function facturasPendientes($clienteId)
    {
        $model = new FacturaHeadModel();

        $facturas = $model
            ->select('facturas_head.*, sellers.seller AS vendedor, tipo_venta.nombre_tipo_venta AS tipo_venta_nombre')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta', 'tipo_venta.id = facturas_head.tipo_venta', 'left')
            ->where('facturas_head.receptor_id', $clienteId)
            ->where('facturas_head.saldo >', 0)
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->orderBy('numero_control', 'ASC')
            ->findAll();

        return $this->response->setJSON($facturas);
    }
    public function store()
    {
        $db = \Config\Database::connect();
        $db->transBegin();
        $session = session();

        try {

            $data = $this->request->getJSON(true);
            if (empty($data['facturas'])) {
                throw new \Exception('No hay facturas');
            }

            $pagosHead = new PagosHeadModel();
            $pagosDet  = new PagosDetailsModel();
            $facturas  = new FacturaHeadModel();

            // ================= HEAD =================

            $pagosHead->insert([
                'cliente_id' => $data['cliente_id'],
                'fecha_pago' => $data['fecha_pago'],
                'forma_pago' => $data['tipo_pago'],
                'numero_recupero' => $data['recupero'],
                'numero_cuenta_bancaria' => $data['cuenta_bancaria'],
                'total' => $data['total'],
                'observaciones' => $data['observaciones'],
                'anulado' => 0
            ], true);

            $pagoId = $pagosHead->getInsertID();
            // ================= DETAILS + FACTURAS =================

            foreach ($data['facturas'] as $f) {

                // detalle
                $pagosDet->insert([
                    'pago_id' => $pagoId,
                    'factura_id' => $f['factura_id'],
                    'monto' => $f['monto'],
                    'observaciones' => $f['comentario'] ?? null
                ]);

                // obtener saldo actual
                $factura = $facturas->find($f['factura_id']);

                if (!$factura) {
                    throw new \Exception('Factura no encontrada');
                }

                $nuevoSaldo = $factura->saldo - $f['monto'];

                if ($nuevoSaldo < 0) {
                    throw new \Exception('Monto mayor al saldo');
                }

                $facturas->update($f['factura_id'], [
                    'saldo' => $nuevoSaldo
                ]);
            }

            // ================= CUENTAS =================

            helper('cuentas');

            $accountId = null;

            // Si es recupero → efectivo (ID 1)
            if ($data['tipo_pago'] === 'recupero') {
                $accountId = 1;
            }

            // Si es transferencia → cuenta seleccionada
            if ($data['tipo_pago'] === 'transferencia') {
                $accountId = $data['cuenta_bancaria'];
            }

            if ($accountId) {

                registrarEntrada(
                    $accountId,
                    $data['total'],
                    'Pago de facturas',
                    'Pago ID ' . $pagoId,
                    $pagoId
                );

                // ACTUALIZAR BALANCE CACHE (opcional pero recomendado)
                $accountModel = new AccountModel();
                $nuevoBalance = $accountModel->getBalance($accountId);

                $accountModel->update($accountId, [
                    'balance' => $nuevoBalance
                ]);
            }

            $db->transCommit();

            registrar_bitacora(
                'Pago de facturas ID ' . esc($pagoId),
                'Pagos',
                'Se pagó un total de $' . number_format($data['total'], 2) . ' al cliente con ID ' . esc($data['cliente_id']) . '.' . 'Forma de pago: ' . esc($data['tipo_pago']),
                $session->get('user_id')
            );

            return $this->response->setJSON([
                'status' => 'ok',
                'pago_id' => $pagoId
            ]);
        } catch (\Throwable $e) {

            $db->transRollback();

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
        }
    }
    public function anular($id)
    {
        $pagoHeadModel    = new PagosHeadModel();
        $pagoDetailsModel = new PagosDetailsModel();
        $facturaHeadModel = new FacturaHeadModel();
        $accountModel     = new AccountModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $pago = $pagoHeadModel->find($id);

        if (!$pago) {
            return redirect()->back()->with('error', 'Pago no encontrado');
        }

        if ($pago->anulado) {
            return redirect()->back()->with('error', 'El pago ya está anulado');
        }

        // Obtener solo detalles activos

        $detallesActivos = $pagoDetailsModel
            ->where('pago_id', $id)
            ->where('anulado', 0)
            ->findAll();

        $totalARevertir = 0;
        foreach ($detallesActivos as $detalle) {

            $db->table('facturas_head')
                ->where('id', $detalle->factura_id)
                ->set('saldo', 'saldo + ' . $detalle->monto, false)
                ->update();

            $totalARevertir += $detalle->monto;

            $pagoDetailsModel->update($detalle->id, [
                'anulado'    => 1,
                'anulado_at' => date('Y-m-d H:i:s'),
                'anulado_by' => session()->get('user_id')
            ]);
        }
        // Reversión bancaria SOLO si hay monto activo

        if (!empty($pago->numero_cuenta_bancaria) && $totalARevertir > 0) {

            $accountId = $pago->numero_cuenta_bancaria;

            $db->table('transactions')->insert([
                'account_id'  => $accountId,
                'tracking_id' => $pago->id,
                'tipo'        => 'salida',
                'monto'       => $totalARevertir,
                'origen'      => 'anulacion_pago',
                'referencia'  => 'Anulación de pago #' . $pago->id,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            // Recalcular balance real desde transactions
            $nuevoBalance = $accountModel->getBalance($accountId);

            $accountModel->update($accountId, [
                'balance' => $nuevoBalance
            ]);
        }

        // Marcar pago como anulado

        $pagoHeadModel->update($id, [
            'anulado' => 1
        ]);
        
        // Registrar bitácora

        $session = session();

        registrar_bitacora(
            'Anulación de pago #' . esc($pago->id),
            'Pagos',
            'Se anuló el pago #' . esc($pago->id) .
                ' por un monto total de $' . number_format($totalARevertir, 2) .
                '. Se revirtieron los saldos aplicados a las facturas correspondientes.',
            $session->get('user_id')
        );
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()
                ->with('error', 'Error al anular el pago');
        }

        return redirect()->to(base_url('payments/' . $id))
            ->with('success', 'Pago anulado correctamente y movimiento compensado');
    }
}
