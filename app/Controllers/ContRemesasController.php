<?php

namespace App\Controllers;

use App\Models\ContRemesasModel;
use App\Models\ContRemesasDetalleModel;
use App\Models\ContConfiguracionModel;
use App\Models\ContTiposPartidaModel;

class ContRemesasController extends BaseController
{
    // ─── LISTADO ──────────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('ver_remesas_contables');
        if ($chk !== true) return $chk;

        $model  = new ContRemesasModel();
        $config = (new ContConfiguracionModel())->getConfig();

        $filtros = [
            'estado'          => $this->request->getGet('estado'),
            'tipo_partida_id' => $this->request->getGet('tipo_partida_id'),
            'fecha_desde'     => $this->request->getGet('fecha_desde'),
            'fecha_hasta'     => $this->request->getGet('fecha_hasta'),
        ];

        $remesas      = $model->getListado($filtros, 20);
        $pager        = $model->pager;
        $tiposPartida = (new ContTiposPartidaModel())->getActivos();

        return view('contabilidad/remesas/index', [
            'remesas'      => $remesas,
            'pager'        => $pager,
            'tiposPartida' => $tiposPartida,
            'filtros'      => $filtros,
            'config'       => $config,
        ]);
    }

    // ─── FORMULARIO NUEVO ─────────────────────────────────────────

    public function nuevo()
    {
        $chk = requerirPermiso('crear_remesa_contable');
        if ($chk !== true) return $chk;

        $config       = (new ContConfiguracionModel())->getConfig();
        $tiposPartida = (new ContTiposPartidaModel())->getActivos();

        return view('contabilidad/remesas/nuevo', [
            'config'       => $config,
            'tiposPartida' => $tiposPartida,
        ]);
    }

    // ─── GUARDAR ──────────────────────────────────────────────────

    public function store()
    {
        $chk = requerirPermiso('crear_remesa_contable');
        if ($chk !== true) return $chk;

        $data = $this->request->getJSON(true);

        if (empty($data['fecha']) || empty($data['descripcion']) || empty($data['asientos'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Se requiere fecha, descripción y al menos un asiento.']);
        }

        if (count($data['asientos']) < 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selecciona al menos un asiento para incluir en la remesa.']);
        }

        $remesasModel = new ContRemesasModel();
        $detalleModel = new ContRemesasDetalleModel();
        $db           = \Config\Database::connect();

        // Validar asientos antes de la transacción
        $total     = 0;
        $asientoIds = [];
        foreach ($data['asientos'] as $aid) {
            $aid     = (int)$aid;
            $asiento = $db->query(
                "SELECT id, total_debe, estado FROM cont_asientos_head WHERE id = ?",
                [$aid]
            )->getRow();

            if (!$asiento) {
                return $this->response->setJSON(['success' => false, 'message' => "Asiento ID {$aid} no encontrado."]);
            }
            if ($asiento->estado !== 'APROBADO') {
                return $this->response->setJSON(['success' => false, 'message' => "El asiento ID {$aid} no está aprobado."]);
            }

            $yaRemesado = $db->query(
                "SELECT rd.id FROM cont_remesas_detalle rd
                 JOIN cont_remesas_head rh ON rh.id = rd.remesa_id
                 WHERE rd.asiento_id = ? AND rh.estado != 'ANULADO'",
                [$aid]
            )->getRow();

            if ($yaRemesado) {
                return $this->response->setJSON(['success' => false, 'message' => "El asiento ID {$aid} ya está incluido en otra remesa activa."]);
            }

            $total       += (float)$asiento->total_debe;
            $asientoIds[] = ['id' => $aid, 'monto' => (float)$asiento->total_debe];
        }

        $db->transStart();

        $numero   = $remesasModel->getSiguienteNumero();
        $remesaId = $remesasModel->insert([
            'numero_remesa'   => $numero,
            'fecha'           => $data['fecha'],
            'descripcion'     => $data['descripcion'],
            'tipo_partida_id' => !empty($data['tipo_partida_id']) ? (int)$data['tipo_partida_id'] : null,
            'total'           => round($total, 2),
            'estado'          => 'ACTIVO',
            'observaciones'   => $data['observaciones'] ?? null,
            'usuario_id'      => (int)session()->get('id'),
        ]);

        foreach ($asientoIds as $a) {
            $detalleModel->insert([
                'remesa_id'  => $remesaId,
                'asiento_id' => $a['id'],
                'monto'      => round($a['monto'], 2),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error de base de datos al guardar la remesa.']);
        }

        registrar_bitacora(
            'Crear remesa contable',
            'ContRemesas',
            "Se creó la remesa {$numero} con " . count($asientoIds) . " asiento(s) por $" . number_format($total, 2),
            session()->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => "Remesa {$numero} creada correctamente.",
            'id'      => $remesaId,
            'numero'  => $numero,
        ]);
    }

    // ─── DETALLE ──────────────────────────────────────────────────

    public function show($id)
    {
        $chk = requerirPermiso('ver_remesas_contables');
        if ($chk !== true) return $chk;

        $remesa = (new ContRemesasModel())->getConDetalle((int)$id);
        if (!$remesa) {
            return redirect()->to(base_url('contabilidad/remesas'))->with('error', 'Remesa no encontrada.');
        }

        $detalles = (new ContRemesasDetalleModel())->getByRemesa((int)$id);

        return view('contabilidad/remesas/show', [
            'remesa'   => $remesa,
            'detalles' => $detalles,
        ]);
    }

    // ─── ANULAR ───────────────────────────────────────────────────

    public function anular($id)
    {
        $chk = requerirPermiso('anular_remesa_contable');
        if ($chk !== true) return $chk;

        $motivo = trim($this->request->getPost('motivo') ?? '');
        if (!$motivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'El motivo de anulación es obligatorio.']);
        }

        $remesasModel = new ContRemesasModel();
        $remesa       = $remesasModel->find((int)$id);

        if (!$remesa) {
            return $this->response->setJSON(['success' => false, 'message' => 'Remesa no encontrada.']);
        }
        if ($remesa->estado === 'ANULADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'Esta remesa ya está anulada.']);
        }
        if ($remesa->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'Esta remesa está cerrada y no puede anularse directamente.']);
        }

        $remesasModel->update((int)$id, [
            'estado'           => 'ANULADO',
            'anulado_por'      => (int)session()->get('id'),
            'fecha_anulacion'  => date('Y-m-d H:i:s'),
            'motivo_anulacion' => $motivo,
        ]);

        registrar_bitacora(
            'Anular remesa contable',
            'ContRemesas',
            "Se anuló la remesa {$remesa->numero_remesa}. Motivo: {$motivo}",
            session()->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => "Remesa {$remesa->numero_remesa} anulada correctamente.",
        ]);
    }

    // ─── AJAX: asientos disponibles ───────────────────────────────

    public function asientosDisponibles()
    {
        $chk = requerirPermiso('ver_remesas_contables');
        if ($chk !== true) return $chk;

        $db            = \Config\Database::connect();
        $tipoPartidaId = (int)($this->request->getGet('tipo_partida_id') ?: 0);
        $fechaDesde    = $this->request->getGet('fecha_desde') ?: null;
        $fechaHasta    = $this->request->getGet('fecha_hasta') ?: null;

        $where  = "ah.estado = 'APROBADO'
                   AND NOT EXISTS (
                       SELECT 1 FROM cont_remesas_detalle rd
                       JOIN cont_remesas_head rh ON rh.id = rd.remesa_id
                       WHERE rd.asiento_id = ah.id AND rh.estado != 'ANULADO'
                   )";
        $params = [];

        if ($tipoPartidaId) {
            $where   .= " AND ah.tipo_partida_id = ?";
            $params[] = $tipoPartidaId;
        }
        if ($fechaDesde) {
            $where   .= " AND ah.fecha >= ?";
            $params[] = $fechaDesde;
        }
        if ($fechaHasta) {
            $where   .= " AND ah.fecha <= ?";
            $params[] = $fechaHasta;
        }

        $asientos = $db->query(
            "SELECT ah.id, ah.numero_asiento, ah.fecha, ah.descripcion,
                    ah.total_debe, ah.referencia, ah.documento_tipo, ah.documento_id,
                    tp.nombre AS tipo_partida_nombre,
                    CONCAT(p.anio, '-', LPAD(p.mes, 2, '0')) AS periodo
             FROM cont_asientos_head ah
             LEFT JOIN cont_tipos_partida tp ON tp.id = ah.tipo_partida_id
             LEFT JOIN cont_periodos p ON p.id = ah.periodo_id
             WHERE {$where}
             ORDER BY ah.fecha ASC, ah.numero_asiento ASC",
            $params
        )->getResult();

        return $this->response->setJSON($asientos);
    }
}
