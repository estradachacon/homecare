<?php

namespace App\Controllers;

use App\Models\RecuperosModel;
use App\Models\RecuperosDetalleModel;
use App\Models\FacturaHeadModel;
use App\Models\FacturaDetalleModel;
use App\Models\ClienteModel;

class RecuperosController extends BaseController
{
    // ─── LISTADO ──────────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $model   = new RecuperosModel();
        $filtros = [
            'cliente_id'  => $this->request->getGet('cliente_id'),
            'estado'      => $this->request->getGet('estado'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];

        $recuperos = $model->getListado($filtros, 20);
        $pager     = $model->pager;
        $clientes  = (new ClienteModel())->orderBy('nombre')->findAll();

        return view('recuperos/index', [
            'recuperos' => $recuperos,
            'pager'     => $pager,
            'clientes'  => $clientes,
            'filtros'   => $filtros,
        ]);
    }

    // ─── FORMULARIO NUEVO ─────────────────────────────────────────

    public function nuevo()
    {
        $chk = requerirPermiso('crear_recupero');
        if ($chk !== true) return $chk;

        return view('recuperos/nuevo');
    }

    // ─── GUARDAR ──────────────────────────────────────────────────

    public function store()
    {
        $chk = requerirPermiso('crear_recupero');
        if ($chk !== true) return $chk;

        $data = $this->request->getJSON(true);

        if (empty($data['cliente_id']) || empty($data['fecha']) || empty($data['facturas'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Verifica cliente, fecha y facturas.']);
        }

        if (count($data['facturas']) < 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Agrega al menos una factura al recupero']);
        }

        $recuperosModel = new RecuperosModel();
        $detalleModel   = new RecuperosDetalleModel();
        $facturaModel   = new FacturaHeadModel();
        $db             = \Config\Database::connect();

        // Validar montos antes de la transacción
        $total = 0;
        foreach ($data['facturas'] as $f) {
            $monto     = (float)($f['monto'] ?? 0);
            $facturaId = (int)($f['factura_id'] ?? 0);

            if ($monto <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Todos los montos deben ser mayores a cero']);
            }

            $factura = $facturaModel->find($facturaId);
            if (!$factura) {
                return $this->response->setJSON(['success' => false, 'message' => "Factura ID {$facturaId} no encontrada"]);
            }
            if ($monto > (float)$factura->saldo + 0.01) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "El monto \$" . number_format($monto, 2) . " supera el saldo \$" . number_format($factura->saldo, 2) . " de {$factura->numero_control}",
                ]);
            }

            $total += $monto;
        }

        $db->transStart();

        $numero     = $recuperosModel->getSiguienteNumero();
        $recuperoId = $recuperosModel->insert([
            'numero_recupero' => $numero,
            'cliente_id'      => (int)$data['cliente_id'],
            'fecha'           => $data['fecha'],
            'forma_cobro'     => $data['forma_cobro'] ?? 'efectivo',
            'referencia'      => $data['referencia'] ?? null,
            'total'           => round($total, 2),
            'observaciones'   => $data['observaciones'] ?? null,
            'estado'          => 'ACTIVO',
            'usuario_id'      => (int)session()->get('id'),
        ]);

        foreach ($data['facturas'] as $f) {
            $monto     = round((float)$f['monto'], 2);
            $facturaId = (int)$f['factura_id'];

            $detalleModel->insert([
                'recupero_id'    => $recuperoId,
                'factura_id'     => $facturaId,
                'monto_aplicado' => $monto,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error de base de datos al guardar el recupero']);
        }

        registrar_bitacora(
            'Crear recupero',
            'Recuperos',
            "Se creó el recupero {$numero} por \$" . number_format($total, 2),
            session()->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => "Recupero {$numero} guardado correctamente",
            'id'      => $recuperoId,
            'numero'  => $numero,
        ]);
    }

    // ─── DETALLE ──────────────────────────────────────────────────

    public function show($id)
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $recuperosModel = new RecuperosModel();
        $detalleModel   = new RecuperosDetalleModel();

        $recupero = $recuperosModel->getConCliente((int)$id);
        if (!$recupero) {
            return redirect()->to(base_url('recuperos'))->with('error', 'Recupero no encontrado');
        }

        $detalles = $detalleModel->getByRecupero((int)$id);

        return view('recuperos/show', [
            'recupero' => $recupero,
            'detalles' => $detalles,
        ]);
    }

    // ─── ANULAR ───────────────────────────────────────────────────

    public function anular($id)
    {
        $chk = requerirPermiso('anular_recupero');
        if ($chk !== true) return $chk;

        $motivo = trim($this->request->getPost('motivo') ?? '');
        if (!$motivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'El motivo de anulación es obligatorio']);
        }

        $recuperosModel = new RecuperosModel();
        $db             = \Config\Database::connect();

        $recupero = $recuperosModel->find((int)$id);
        if (!$recupero) {
            return $this->response->setJSON(['success' => false, 'message' => 'Recupero no encontrado']);
        }
        if ($recupero->estado === 'ANULADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'Este recupero ya está anulado']);
        }
        if ($recupero->estado === 'APLICADO') {
            $ref = $recupero->pago_id ? ' (vinculado al pago #' . $recupero->pago_id . ')' : '';
            return $this->response->setJSON(['success' => false, 'message' => 'Este recupero ya fue aplicado a un pago' . $ref . ' y no puede anularse']);
        }

        $db->transStart();

        $recuperosModel->update((int)$id, [
            'estado'           => 'ANULADO',
            'anulado_por'      => (int)session()->get('id'),
            'fecha_anulacion'  => date('Y-m-d H:i:s'),
            'motivo_anulacion' => $motivo,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al anular el recupero']);
        }

        registrar_bitacora(
            'Anular recupero',
            'Recuperos',
            "Se anuló el recupero {$recupero->numero_recupero}. Motivo: {$motivo}",
            session()->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => "Recupero {$recupero->numero_recupero} anulado. Los saldos de las facturas fueron restaurados.",
        ]);
    }

    // ─── AJAX: recuperos activos de un cliente ────────────────────────

    public function activosPorCliente($clienteId)
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $db        = \Config\Database::connect();
        $recuperos = (new RecuperosModel())->getActivosByCliente((int)$clienteId);

        foreach ($recuperos as $rec) {
            $rec->detalles = $db->query(
                "SELECT rd.factura_id, rd.monto_aplicado, fh.numero_control
                 FROM recuperos_detalle rd
                 LEFT JOIN facturas_head fh ON fh.id = rd.factura_id
                 WHERE rd.recupero_id = ?",
                [$rec->id]
            )->getResult();
        }

        return $this->response->setJSON($recuperos);
    }

    // ─── AJAX: detalle de una factura (modal) ────────────────────────

    public function detalleFactura($id)
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $db = \Config\Database::connect();

        $factura = $db->query(
            "SELECT fh.id, fh.numero_control, fh.tipo_dte, fh.fecha_emision,
                    fh.total_pagar, fh.saldo,
                    COALESCE(s.seller, '—') AS vendedor
             FROM facturas_head fh
             LEFT JOIN sellers s ON s.id = fh.vendedor_id
             WHERE fh.id = ?",
            [(int)$id]
        )->getRow();

        if (!$factura) {
            return $this->response->setJSON(['success' => false, 'message' => 'Factura no encontrada']);
        }

        $lineas = (new FacturaDetalleModel())->getByFactura((int)$id);

        return $this->response->setJSON([
            'success' => true,
            'factura' => $factura,
            'lineas'  => $lineas,
        ]);
    }

    // ─── AJAX: facturas pendientes del cliente ─────────────────────

    public function facturasPendientes($clienteId)
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $db = \Config\Database::connect();

        $facturas = $db->query(
            "SELECT fh.id,
                    fh.numero_control,
                    fh.tipo_dte,
                    fh.fecha_emision,
                    fh.total_pagar,
                    fh.saldo,
                    DATEDIFF(NOW(), fh.fecha_emision) AS dias_pendiente
             FROM facturas_head fh
             WHERE fh.receptor_id = ?
               AND fh.saldo > 0.001
               AND fh.anulada = 0
             ORDER BY fh.fecha_emision ASC",
            [(int)$clienteId]
        )->getResult();

        return $this->response->setJSON($facturas);
    }
}
