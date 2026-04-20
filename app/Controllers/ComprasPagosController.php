<?php

namespace App\Controllers;

use App\Models\PagosComprasHeadModel;
use App\Models\PagosComprasDetallesModel;
use App\Models\CompraHeadModel;
use App\Models\ProveedorModel;
use App\Models\AccountModel;

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

    // ─────────────────────────────────────────────
    //  LISTADO
    // ─────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('ver_pagos_a_compras');
        if ($chk !== true) return $chk;

        $perPage = (int)($this->request->getGet('per_page') ?? 25);
        if (!in_array($perPage, [10, 15, 25, 50, 100, 99999])) $perPage = 25;

        $query = $this->pagosHeadModel
            ->select('pagos_compras_head.*, proveedores.nombre AS proveedor_nombre')
            ->join('proveedores', 'proveedores.id = pagos_compras_head.proveedor_id', 'left');

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
            return $this->response->setJSON([
                'tbody' => view('compraspagos/_tbody', ['pagos' => $pagos]),
                'pager' => $pager->links('default', 'bootstrap_full'),
            ]);
        }

        return view('compraspagos/index', ['pagos' => $pagos, 'pager' => $pager]);
    }

    // ─────────────────────────────────────────────
    //  FORMULARIO NUEVO
    // ─────────────────────────────────────────────

    public function new()
    {
        $chk = requerirPermiso('registrar_pagos_a_compras');
        if ($chk !== true) return $chk;

        return view('compraspagos/new');
    }

    // ─────────────────────────────────────────────
    //  DETALLE
    // ─────────────────────────────────────────────

    public function show($id)
    {
        $chk = requerirPermiso('ver_pagos_a_compras');
        if ($chk !== true) return $chk;

        $pago = $this->pagosHeadModel
            ->select('pagos_compras_head.*, proveedores.nombre AS proveedor_nombre, accounts.name AS cuenta_nombre')
            ->join('proveedores', 'proveedores.id = pagos_compras_head.proveedor_id', 'left')
            ->join('accounts', 'accounts.id = pagos_compras_head.numero_cuenta_bancaria', 'left')
            ->where('pagos_compras_head.id', $id)
            ->first();

        if (!$pago) {
            return redirect()->to('/compraspagos')->with('error', 'Pago no encontrado.');
        }

        $detalles = $this->pagosDetallesModel
            ->select('pagos_compras_detalles.*, compras_head.numero_control')
            ->join('compras_head', 'compras_head.id = pagos_compras_detalles.compra_id', 'left')
            ->where('pagos_compras_detalles.pago_id', $id)
            ->findAll();

        $hayAnulaciones  = false;
        $totalActivo     = 0;
        $totalDetalles   = count($detalles);
        $totalAnulados   = 0;

        foreach ($detalles as $d) {
            if ($d->anulado) {
                $hayAnulaciones = true;
                $totalAnulados++;
            } else {
                $totalActivo += $d->monto;
            }
        }

        $anulacionParcial = ($totalDetalles > 0 && $totalAnulados > 0 && $totalAnulados < $totalDetalles);

        return view('compraspagos/show', compact(
            'pago', 'detalles', 'hayAnulaciones', 'totalActivo', 'anulacionParcial'
        ));
    }

    // ─────────────────────────────────────────────
    //  COMPRAS PENDIENTES (AJAX)
    // ─────────────────────────────────────────────

    public function comprasPendientes($proveedorId)
    {
        $compras = $this->compraModel
            ->where('proveedor_id', $proveedorId)
            ->where('saldo >', 0)
            ->where('anulada', 0)
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();

        return $this->response->setJSON(array_map(fn($c) => [
            'id'     => $c->id,
            'numero' => $c->numero_control,
            'fecha'  => date('d/m/Y', strtotime($c->fecha_emision)),
            'total'  => $c->total_pagar,
            'saldo'  => $c->saldo,
        ], $compras));
    }

    // ─────────────────────────────────────────────
    //  GUARDAR PAGO (JSON)
    // ─────────────────────────────────────────────

    public function store()
    {
        $chk = requerirPermiso('registrar_pagos_a_compras');
        if ($chk !== true) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $data = $this->request->getJSON(true);
        $session = session();

        if (empty($data['proveedor_id']) || empty($data['fecha_pago']) || empty($data['forma_pago'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'Datos incompletos.']);
        }

        if (empty($data['compras']) || !is_array($data['compras'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'No hay compras a procesar.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Auto-número correlativo
        $maxId      = $db->table('pagos_compras_head')->selectMax('id')->get()->getRow()->id ?? 0;
        $numeroPago = 'PC-' . str_pad((int)$maxId + 1, 5, '0', STR_PAD_LEFT);

        $cuentaId = ($data['forma_pago'] === 'transferencia' && !empty($data['numero_cuenta_bancaria']))
            ? (int)$data['numero_cuenta_bancaria']
            : null;

        $pagoId = $this->pagosHeadModel->insert([
            'numero_pago'            => $numeroPago,
            'proveedor_id'           => $data['proveedor_id'],
            'numero_cuenta_bancaria' => $cuentaId,
            'forma_pago'             => $data['forma_pago'],
            'total'                  => $data['total'],
            'fecha_pago'             => $data['fecha_pago'],
            'observaciones'          => $data['observaciones'] ?? null,
            'anulado'                => 0,
        ]);

        $totalAplicado = 0;

        foreach ($data['compras'] as $c) {
            $compra = $this->compraModel->find($c['compra_id']);
            if (!$compra) continue;

            $monto = min((float)$c['monto'], (float)$compra->saldo);
            if ($monto <= 0) continue;

            $this->pagosDetallesModel->insert([
                'pago_id'      => $pagoId,
                'compra_id'    => $compra->id,
                'monto'        => $monto,
                'observaciones'=> $c['observaciones'] ?? null,
                'anulado'      => 0,
            ]);

            $this->compraModel->update($compra->id, [
                'saldo' => max(0, $compra->saldo - $monto),
            ]);

            $totalAplicado += $monto;
        }

        // Movimiento de cuenta (salida de dinero)
        if ($cuentaId && $totalAplicado > 0) {
            registrarSalida(
                $cuentaId,
                $totalAplicado,
                'pago_proveedor',
                'Pago ' . $numeroPago . ' a proveedor',
                $pagoId
            );
            actualizarSaldoCuenta($cuentaId);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'Error al guardar el pago.']);
        }

        registrar_bitacora(
            'Registrar pago a proveedor',
            'Pagos Compras',
            'Pago ' . $numeroPago . ' por $' . number_format($totalAplicado, 2) . ' al proveedor ID ' . $data['proveedor_id'] . '.',
            $session->get('id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pago registrado correctamente.',
            'pago_id' => $pagoId,
            'numero'  => $numeroPago,
        ]);
    }

    // ─────────────────────────────────────────────
    //  ANULAR
    // ─────────────────────────────────────────────

    public function anular($id)
    {
        $chk = requerirPermiso('registrar_pagos_a_compras');
        if ($chk !== true) return $chk;

        $session = session();
        $db      = \Config\Database::connect();

        $pago = $this->pagosHeadModel->find($id);

        if (!$pago) {
            return redirect()->to('/compraspagos')->with('error', 'Pago no encontrado.');
        }

        if ($pago->anulado) {
            return redirect()->to('/compraspagos/' . $id)->with('error', 'Este pago ya está anulado.');
        }

        $db->transStart();

        $detallesActivos = $this->pagosDetallesModel
            ->where('pago_id', $id)
            ->where('anulado', 0)
            ->findAll();

        $totalRevertido = 0;

        foreach ($detallesActivos as $det) {
            // Restaurar saldo de la compra
            $db->table('compras_head')
                ->where('id', $det->compra_id)
                ->set('saldo', 'saldo + ' . (float)$det->monto, false)
                ->update();

            $totalRevertido += $det->monto;

            // Marcar detalle como anulado
            $this->pagosDetallesModel->update($det->id, [
                'anulado'    => 1,
                'anulado_at' => date('Y-m-d H:i:s'),
                'anulado_by' => $session->get('id'),
            ]);
        }

        // Movimiento de cuenta compensatorio (entrada, dinero vuelve)
        $cuentaId = $pago->numero_cuenta_bancaria ? (int)$pago->numero_cuenta_bancaria : null;

        if ($cuentaId && $totalRevertido > 0) {
            registrarEntrada(
                $cuentaId,
                $totalRevertido,
                'anulacion_pago_proveedor',
                'Anulación pago ' . $pago->numero_pago,
                $pago->id
            );
            actualizarSaldoCuenta($cuentaId);
        }

        $this->pagosHeadModel->update($id, [
            'anulado'    => 1,
            'anulado_at' => date('Y-m-d H:i:s'),
            'anulado_by' => $session->get('id'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/compraspagos/' . $id)
                ->with('error', 'Error al anular el pago.');
        }

        registrar_bitacora(
            'Anular pago a proveedor',
            'Pagos Compras',
            'Se anuló el pago ' . $pago->numero_pago . ' por $' . number_format($totalRevertido, 2) . '.',
            $session->get('id')
        );

        return redirect()->to('/compraspagos/' . $id)
            ->with('success', 'Pago anulado correctamente. Saldos de compras revertidos.');
    }
}
