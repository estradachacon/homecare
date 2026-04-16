<?php

namespace App\Controllers;

use App\Models\ContAsientosHeadModel;
use App\Models\ContAsientosDetalleModel;
use App\Models\ContPeriodosModel;
use App\Models\ContSaldosCuentasModel;
use App\Models\ContTransaccionesHistModel;
use App\Models\ContPlanCuentasModel;

class ContAsientosController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_asientos');
        if ($chk !== true) return $chk;

        $headModel    = new ContAsientosHeadModel();
        $periodosModel = new ContPeriodosModel();

        $filtros  = [
            'periodo_id'  => $this->request->getGet('periodo_id'),
            'tipo'        => $this->request->getGet('tipo'),
            'estado'      => $this->request->getGet('estado'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
            'descripcion' => $this->request->getGet('descripcion'),
        ];

        $perPage  = (int)($this->request->getGet('per_page') ?? 25);
        if (!in_array($perPage, [10,15,25,50,100,99999])) $perPage = 25;

        $asientos = $headModel->getListadoFiltrado($filtros, $perPage);
        $pager    = $headModel->pager;
        $periodos = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();

        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            $tbody    = view('contabilidad/asientos/_tbody', ['asientos' => $asientos]);
            $pagerHtml = $pager->links('default', 'bootstrap_full');
            return $this->response->setJSON(['tbody' => $tbody, 'pager' => $pagerHtml]);
        }

        return view('contabilidad/asientos/index', [
            'asientos' => $asientos,
            'pager'    => $pager,
            'periodos' => $periodos,
            'filtros'  => $filtros,
        ]);
    }

    public function nuevo()
    {
        $chk = requerirPermiso('crear_asiento');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $headModel     = new ContAsientosHeadModel();

        $periodos      = $periodosModel->where('estado', 'ABIERTO')->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $nextNumero    = $headModel->getSiguienteNumero();

        return view('contabilidad/asientos/new', [
            'periodos'   => $periodos,
            'nextNumero' => $nextNumero,
        ]);
    }

    public function store()
    {
        $chk = requerirPermiso('crear_asiento');
        if ($chk !== true) return $chk;

        $data = $this->request->getJSON(true);

        if (empty($data['periodo_id']) || empty($data['fecha']) || empty($data['descripcion'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos']);
        }

        $lineas = $data['lineas'] ?? [];
        if (count($lineas) < 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'Un asiento requiere al menos 2 líneas']);
        }

        $totalDebe  = 0;
        $totalHaber = 0;
        foreach ($lineas as $l) {
            $totalDebe  += (float)($l['debe']  ?? 0);
            $totalHaber += (float)($l['haber'] ?? 0);
        }

        if (abs($totalDebe - $totalHaber) > 0.01) {
            return $this->response->setJSON(['success' => false, 'message' => 'El asiento no cuadra. Debe = ' . number_format($totalDebe,2) . ' | Haber = ' . number_format($totalHaber,2)]);
        }

        $periodosModel = new ContPeriodosModel();
        $periodo = $periodosModel->find($data['periodo_id']);
        if (!$periodo || $periodo->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'El período está cerrado o no existe']);
        }

        $headModel    = new ContAsientosHeadModel();
        $detalleModel = new ContAsientosDetalleModel();
        $db           = \Config\Database::connect();

        $db->transStart();

        $nextNum = $headModel->getSiguienteNumero();

        $asientoId = $headModel->insert([
            'numero_asiento' => $nextNum,
            'fecha'          => $data['fecha'],
            'descripcion'    => $data['descripcion'],
            'tipo'           => $data['tipo'] ?? 'DIARIO',
            'estado'         => 'BORRADOR',
            'periodo_id'     => (int)$data['periodo_id'],
            'total_debe'     => $totalDebe,
            'total_haber'    => $totalHaber,
            'referencia'     => $data['referencia'] ?? null,
            'usuario_id'     => session()->get('id'),
        ]);

        if (!$asientoId) {
            $db->transRollback();
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar el asiento']);
        }

        foreach ($lineas as $i => $l) {
            $detalleModel->insert([
                'asiento_id'  => $asientoId,
                'cuenta_id'   => (int)$l['cuenta_id'],
                'descripcion' => $l['descripcion'] ?? null,
                'debe'        => (float)($l['debe']  ?? 0),
                'haber'       => (float)($l['haber'] ?? 0),
                'orden'       => $i + 1,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Asiento guardado como borrador', 'id' => $asientoId]);
    }

    public function show($id)
    {
        $chk = requerirPermiso('ver_asientos');
        if ($chk !== true) return $chk;

        $headModel    = new ContAsientosHeadModel();
        $detalleModel = new ContAsientosDetalleModel();

        $asiento  = $headModel->getConDetalle($id);
        if (!$asiento) {
            return redirect()->to(base_url('contabilidad/asientos'))->with('error', 'Asiento no encontrado');
        }

        $lineas   = $detalleModel->getByAsiento($id);

        return view('contabilidad/asientos/show', [
            'asiento' => $asiento,
            'lineas'  => $lineas,
        ]);
    }

    public function aprobar($id)
    {
        $chk = requerirPermiso('aprobar_asiento');
        if ($chk !== true) return $chk;

        $headModel    = new ContAsientosHeadModel();
        $detalleModel = new ContAsientosDetalleModel();
        $saldosModel  = new ContSaldosCuentasModel();
        $histModel    = new ContTransaccionesHistModel();
        $periodosModel = new ContPeriodosModel();

        $asiento = $headModel->find($id);
        if (!$asiento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Asiento no encontrado']);
        }
        if ($asiento->estado !== 'BORRADOR') {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se pueden aprobar asientos en borrador']);
        }

        $periodo = $periodosModel->find($asiento->periodo_id);
        if (!$periodo || $periodo->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'El período está cerrado']);
        }

        $lineas = $detalleModel->getByAsiento($id);
        $db     = \Config\Database::connect();
        $db->transStart();

        $headModel->update($id, [
            'estado'             => 'APROBADO',
            'usuario_aprueba_id' => session()->get('id'),
            'fecha_aprobacion'   => date('Y-m-d H:i:s'),
        ]);

        // Actualizar saldos y registrar en histórico
        foreach ($lineas as $l) {
            $saldosModel->upsert((int)$l->cuenta_id, (int)$asiento->periodo_id, (float)$l->debe, (float)$l->haber);

            // Obtener saldo acumulado actual de la cuenta
            $saldoAcum = $db->query(
                'SELECT COALESCE(SUM(debe)-SUM(haber),0) AS saldo FROM cont_transacciones_hist WHERE cuenta_id=?',
                [$l->cuenta_id]
            )->getRow()->saldo ?? 0;

            $histModel->insert([
                'asiento_id'      => $id,
                'cuenta_id'       => $l->cuenta_id,
                'fecha'           => $asiento->fecha,
                'descripcion'     => $l->descripcion ?: $asiento->descripcion,
                'debe'            => $l->debe,
                'haber'           => $l->haber,
                'saldo_acumulado' => (float)$saldoAcum + (float)$l->debe - (float)$l->haber,
                'anio'            => $periodo->anio,
                'mes'             => $periodo->mes,
                'tipo_asiento'    => $asiento->tipo,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Asiento aprobado y saldos actualizados']);
    }

    public function anular($id)
    {
        $chk = requerirPermiso('anular_asiento');
        if ($chk !== true) return $chk;

        $motivo = $this->request->getPost('motivo') ?: 'Sin motivo';

        $headModel    = new ContAsientosHeadModel();
        $detalleModel = new ContAsientosDetalleModel();
        $saldosModel  = new ContSaldosCuentasModel();
        $histModel    = new ContTransaccionesHistModel();

        $asiento = $headModel->find($id);
        if (!$asiento || $asiento->estado === 'ANULADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede anular este asiento']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Si estaba aprobado, revertir saldos
        if ($asiento->estado === 'APROBADO') {
            $lineas = $detalleModel->getByAsiento($id);
            foreach ($lineas as $l) {
                $saldosModel->upsert((int)$l->cuenta_id, (int)$asiento->periodo_id, -(float)$l->debe, -(float)$l->haber);
            }
            $histModel->eliminarPorAsiento($id);
        }

        $headModel->update($id, [
            'estado'          => 'ANULADO',
            'motivo_anulacion'=> $motivo,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Asiento anulado correctamente']);
    }
}
