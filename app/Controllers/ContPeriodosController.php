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
        $anioSel = $this->request->getGet('anio') ?? (int)date('Y');

        $periodos = $model->getPeriodosPorAnio((int)$anioSel);

        return view('contabilidad/periodos/index', [
            'periodos' => $periodos,
            'anios'    => $anios,
            'anioSel'  => $anioSel,
            'meses'    => $this->meses,
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

        $model->insert([
            'anio'           => $anio,
            'mes'            => $mes,
            'estado'         => 'ABIERTO',
            'fecha_apertura' => date('Y-m-d'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período creado correctamente']);
    }

    public function cerrar($id)
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

        // Verificar asientos en borrador
        $asientoModel = new ContAsientosHeadModel();
        $borradores = $asientoModel->where('periodo_id', $id)->where('estado', 'BORRADOR')->countAllResults();
        if ($borradores > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Existen $borradores asiento(s) en borrador. Deben aprobarse o anularse antes de cerrar el período."
            ]);
        }

        $model->update($id, [
            'estado'            => 'CERRADO',
            'fecha_cierre'      => date('Y-m-d'),
            'usuario_cierre_id' => session()->get('id'),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Período cerrado correctamente']);
    }

    public function reabrir($id)
    {
        $chk = requerirPermiso('cerrar_periodo_contable');
        if ($chk !== true) return $chk;

        $model   = new ContPeriodosModel();
        $periodo = $model->find($id);

        if (!$periodo || $periodo->estado === 'ABIERTO') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede reabrir']);
        }

        $model->update($id, ['estado' => 'ABIERTO', 'fecha_cierre' => null, 'usuario_cierre_id' => null]);
        return $this->response->setJSON(['success' => true, 'message' => 'Período reabierto']);
    }
}
