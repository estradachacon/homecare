<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\PagosDetailsModel;
use App\Models\TipoVentaModel;
use App\Models\FacturaHeadModel;
use App\Models\PagosHeadModel;

class PaymentController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_facturas');
        if ($chk !== true) return $chk;

        $model = new FacturaHeadModel();

        // SELECT PRINCIPAL + JOINS
        $model->select(
            'facturas_head.*, 
            clientes.nombre AS cliente_nombre, 
            sellers.seller AS vendedor,
            tipo_venta.nombre_tipo_venta AS tipo_venta_nombre'
        )
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->join('tipo_venta', 'tipo_venta.id = facturas_head.tipo_venta', 'left');

        // ================= FILTROS =================

        $clienteId = $this->request->getGet('cliente_id');
        $sellerId  = $this->request->getGet('seller_id');
        $estado    = $this->request->getGet('estado');
        $tipoDte   = $this->request->getGet('tipo_dte');
        $fecha     = $this->request->getGet('fecha');
        $tipoVenta = $this->request->getGet('tipo_venta');

        if (is_numeric($clienteId)) {
            $model->where('facturas_head.receptor_id', $clienteId);
        }

        if (is_numeric($sellerId)) {
            $model->where('facturas_head.vendedor_id', $sellerId);
        }

        if ($estado === 'activa') {
            $model->where('facturas_head.anulada', 0);
        }

        if ($estado === 'anulada') {
            $model->where('facturas_head.anulada', 1);
        }

        if ($estado === 'pagada') {
            $model->where('facturas_head.anulada', 0)
                ->where('facturas_head.saldo', 0);
        }

        if (is_numeric($tipoDte)) {
            $model->where('facturas_head.tipo_dte', $tipoDte);
        }

        if (!empty($fecha)) {
            $model->where('facturas_head.fecha_emision', $fecha);
        }

        if (is_numeric($tipoVenta)) {
            $model->where('facturas_head.tipo_venta', $tipoVenta);
        }

        // ==========================================

        $model->orderBy('fecha_emision', 'DESC')
            ->orderBy("CAST(SUBSTRING(numero_control, -6) AS UNSIGNED)", 'DESC', false);

        $facturas = $model->paginate(10);
        $pager = $model->pager;

        // CATÁLOGO TIPO VENTA PARA EL SELECT
        $tipoVentaModel = new TipoVentaModel();
        $tiposVenta = $tipoVentaModel
            ->orderBy('nombre_tipo_venta')
            ->findAll();

        // RESPUESTA AJAX
        if ($this->request->isAJAX()) {

            $tbody = view('pagos/tbody_row', compact('facturas'));
            $pagerHtml = $pager->links('default', 'bootstrap_full');

            return $this->response->setJSON([
                'tbody' => $tbody,
                'pager' => $pagerHtml
            ]);
        }

        // VISTA NORMAL
        return view('pagos/index', compact('facturas', 'pager', 'tiposVenta'));
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

            // ================= CUENTA =================

            if ($data['tipo_pago'] === 'transferencia') {

                helper('cuentas');

                registrarEntrada(
                    $data['cuenta_bancaria'],
                    $data['total'],
                    'Pago de facturas',
                    'Pago ID ' . $pagoId,
                    $pagoId
                );
            }

            $db->transCommit();

            registrar_bitacora(
                'Pago de facturas ID ' . esc($pagoId),
                'Pagos',
                'Se pagó un total de $' . number_format($data['total'], 2) . ' al cliente con ID ' . esc($data['cliente_id']) . '.' . ' Usando cuenta ID ' . esc($data['cuenta_bancaria']),
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
}
