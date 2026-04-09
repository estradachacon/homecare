<?php

namespace App\Controllers;

use App\Models\PagosComprasHeadModel;
use App\Models\PagosComprasDetallesModel;
use App\Models\CompraHeadModel;
use App\Models\ProveedorModel;

class ComprasPagosController extends BaseController
{
    protected $pagosHeadModel;
    protected $pagosDetallesModel;
    protected $compraModel;
    protected $proveedorModel;

    public function __construct()
    {
        $this->pagosHeadModel     = new PagosComprasHeadModel();
        $this->pagosDetallesModel = new PagosComprasDetallesModel();
        $this->compraModel        = new CompraHeadModel();
        $this->proveedorModel     = new ProveedorModel();
    }

    public function index()
    {
        $chk = requerirPermiso('ver_pagos_a_compras');
        if ($chk !== true) return $chk;

        $perPage = (int)($this->request->getGet('per_page') ?? 25);
        if (!in_array($perPage, [10, 15, 25, 50, 100, 99999])) $perPage = 25;

        $query = $this->pagosHeadModel
            ->select('pagos_compras_head.*, proveedores.nombre AS proveedor_nombre')
            ->join('proveedores', 'proveedores.id = pagos_compras_head.proveedor_id', 'left');

        // FILTROS
        if ($proveedorId = $this->request->getGet('proveedor_id')) {
            $query->where('pagos_compras_head.proveedor_id', $proveedorId);
        }

        if ($estado = $this->request->getGet('estado')) {
            $query->where('pagos_compras_head.anulado', $estado === 'anulado' ? 1 : 0);
        }

        if ($fecha = $this->request->getGet('fecha')) {
            $query->where('DATE(pagos_compras_head.fecha_pago)', $fecha);
        }

        if ($numero = $this->request->getGet('numero_pago')) {
            $query->like('pagos_compras_head.numero_pago', $numero, 'before');
        }

        $query->orderBy('pagos_compras_head.fecha_pago', 'DESC');

        $pagos = $query->paginate($perPage);
        $pager = $this->pagosHeadModel->pager;

        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $tbody    = view('compraspagos/_tbody', ['pagos' => $pagos]);
            $pagerHtml = $pager->links('default', 'bootstrap_full');
            return $this->response->setJSON([
                'tbody' => $tbody,
                'pager' => $pagerHtml,
            ]);
        }

        return view('compraspagos/index', [
            'pagos' => $pagos,
            'pager' => $pager,
        ]);
    }
    
    public function new()
    {
        return view('compraspagos/new');
    }

    public function comprasPendientes($proveedorId)
    {
        $model = new CompraHeadModel();

        $compras = $model
            ->where('proveedor_id', $proveedorId)
            ->where('saldo >', 0)
            ->where('anulada', 0)
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();

        // Formatear para el frontend
        $data = array_map(function ($c) {
            return [
                'id'     => $c->id,
                'numero' => substr($c->numero_control, -6), // igual que ventas 👀
                'fecha'  => date('d/m/Y', strtotime($c->fecha_emision)),
                'saldo'  => $c->saldo,
            ];
        }, $compras);

        return $this->response->setJSON($data);
    }
    public function store()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos inválidos'
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $headModel = new PagosComprasHeadModel();
        $detModel  = new PagosComprasDetallesModel();
        $compraModel = new CompraHeadModel();

        // ================= VALIDACIONES BASE =================
        if (empty($data['proveedor_id']) || empty($data['fecha_pago']) || empty($data['forma_pago'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ])->setStatusCode(400);
        }

        if (empty($data['compras']) || !is_array($data['compras'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay compras a procesar'
            ])->setStatusCode(400);
        }

        // ================= INSERT HEAD =================
        $pagoId = $headModel->insert([
            'proveedor_id' => $data['proveedor_id'],
            'numero_cuenta_bancaria' => $data['numero_cuenta_bancaria'] ?? null,
            'forma_pago' => $data['forma_pago'],
            'total' => $data['total'],
            'fecha_pago' => $data['fecha_pago'],
            'observaciones' => $data['observaciones'] ?? null,
            'anulado' => null
        ]);

        // ================= DETALLES =================
        foreach ($data['compras'] as $c) {

            $compra = $compraModel->find($c['compra_id']);

            if (!$compra) {
                continue; // o podés lanzar error
            }

            $monto = floatval($c['monto']);

            // 🔥 PROTECCIÓN BACKEND (CLAVE)
            if ($monto > $compra->saldo) {
                $monto = $compra->saldo;
            }

            if ($monto <= 0) continue;

            // Insert detalle
            $detModel->insert([
                'pago_id'   => $pagoId,
                'compra_id' => $compra->id,
                'monto'     => $monto,
                'observaciones' => $c['observaciones'] ?? null,
                'anulado'   => null
            ]);

            // Actualizar saldo
            $nuevoSaldo = $compra->saldo - $monto;

            $compraModel->update($compra->id, [
                'saldo' => $nuevoSaldo
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar pago'
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pago registrado correctamente',
            'pago_id' => $pagoId
        ]);
    }
}
