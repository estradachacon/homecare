<?php

namespace App\Controllers;

use App\Models\ContPeriodosModel;
use App\Models\ContAsientosHeadModel;

class ContPeriodosController extends BaseController
{
    private array $meses = [
        1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
        5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
        9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
    ];

    public function index()
    {
        $chk = requerirPermiso('ver_periodos_contables');
        if ($chk !== true) return $chk;

        $model   = new ContPeriodosModel();
        $anios   = $model->getAniosDisponibles();
        $anioSel = (int)($this->request->getGet('anio') ?? date('Y'));

        $periodos = $model->getPeriodosPorAnio($anioSel);

        // Estadísticas del año seleccionado
        $totalCreados       = count($periodos);
        $totalCerrados      = 0;
        $cierreAnualEjecutado = false;
        $fechaCierreAnual   = null;

        foreach ($periodos as $p) {
            if ($p->estado === 'CERRADO') {
                $totalCerrados++;
            }
            if (!empty($p->cierre_anual) && (int)$p->cierre_anual === 1) {
                $cierreAnualEjecutado = true;
                if (!empty($p->fecha_cierre_anual)) {
                    $fechaCierreAnual = $p->fecha_cierre_anual;
                }
            }
        }

        // Conteo de asientos por período
        $asientosPorPeriodo = [];
        if (!empty($periodos)) {
            $db         = \Config\Database::connect();
            $periodoIds = array_column($periodos, 'id');
            $rows = $db->query(
                'SELECT periodo_id, COUNT(*) AS total, COALESCE(SUM(total_debe),0) AS suma_debe
                 FROM cont_asientos_head
                 WHERE periodo_id IN (' . implode(',', array_map('intval', $periodoIds)) . ')
                   AND estado != "ANULADO"
                 GROUP BY periodo_id'
            )->getResult();
            foreach ($rows as $r) {
                $asientosPorPeriodo[$r->periodo_id] = $r;
            }
        }

        return view('contabilidad/periodos/index', [
            'periodos'             => $periodos,
            'anios'                => $anios,
            'anioSel'              => $anioSel,
            'meses'                => $this->meses,
            'totalCreados'         => $totalCreados,
            'totalCerrados'        => $totalCerrados,
            'cierreAnualEjecutado' => $cierreAnualEjecutado,
            'fechaCierreAnual'     => $fechaCierreAnual,
            'asientosPorPeriodo'   => $asientosPorPeriodo,
        ]);
    }

    public function store()
    {
        $chk = requerirPermiso('crear_periodo_contable');
        if ($chk !== true) return $chk;

        $anio = (int)$this->request->getPost('anio');
        $mes  = (int)$this->request->getPost('mes');

        if ($anio < 2000 || $anio > 2100 || $mes < 1 || $mes > 12) {
            return $this->response->setJSON(['success' => false, 'message' => 'Año o mes inválido']);
        }

        $model = new ContPeriodosModel();

        if ($model->existePeriodo($anio, $mes)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El período ya existe']);
        }

        if ($model->esCierreAnualEjecutado($anio)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No se pueden crear períodos para {$anio}: el cierre anual ya fue ejecutado para ese año.",
            ]);
        }

        $model->insert([
            'anio'           => $anio,
            'mes'            => $mes,
            'estado'         => 'ABIERTO',
            'fecha_apertura' => date('Y-m-d'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período creado correctamente']);
    }

    public function cerrar(int $id)
    {
        $chk = requerirPermiso('cerrar_periodo_contable');
        if ($chk !== true) return $chk;

        $model   = new ContPeriodosModel();
        $periodo = $model->find($id);

        if (!$periodo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Período no encontrado']);
        }
        if ($periodo->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'El período ya está cerrado']);
        }

        $asientoModel = new ContAsientosHeadModel();
        $borradores   = $asientoModel->where('periodo_id', $id)->where('estado', 'BORRADOR')->countAllResults();
        if ($borradores > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Existen $borradores asiento(s) en borrador. Deben aprobarse o anularse antes de cerrar el período.",
            ]);
        }

        $model->update($id, [
            'estado'            => 'CERRADO',
            'fecha_cierre'      => date('Y-m-d'),
            'usuario_cierre_id' => session()->get('id'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período cerrado correctamente']);
    }

    public function reabrir(int $id)
    {
        $chk = requerirPermiso('cerrar_periodo_contable');
        if ($chk !== true) return $chk;

        $model   = new ContPeriodosModel();
        $periodo = $model->find($id);

        if (!$periodo || $periodo->estado === 'ABIERTO') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede reabrir este período']);
        }

        if (!empty($periodo->cierre_anual) && (int)$periodo->cierre_anual === 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "El año {$periodo->anio} tiene cierre anual ejecutado. No es posible reabrir períodos de un año cerrado anualmente.",
            ]);
        }

        $model->update($id, [
            'estado'            => 'ABIERTO',
            'fecha_cierre'      => null,
            'usuario_cierre_id' => null,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período reabierto correctamente']);
    }
}
