<?php

namespace App\Controllers;

use App\Models\ContAsientosHeadModel;
use App\Models\ContAsientosDetalleModel;
use App\Models\ContPeriodosModel;
use App\Models\ContSaldosCuentasModel;
use App\Models\ContTransaccionesHistModel;
use App\Models\ContPlanCuentasModel;
use App\Models\ContTiposPartidaModel;

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
        $tiposPartida  = (new \App\Models\ContTiposPartidaModel())->getActivos();

        return view('contabilidad/asientos/new', [
            'periodos'     => $periodos,
            'nextNumero'   => $nextNumero,
            'tiposPartida' => $tiposPartida,
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

        $nextNum       = $headModel->getSiguienteNumero();
        $tipo          = $data['tipo'] ?? 'DIARIO';
        $tipoPartidaId = !empty($data['tipo_partida_id']) ? (int)$data['tipo_partida_id'] : null;
        $anioFecha     = (int)substr($data['fecha'], 0, 4);
        $numPartida    = $tipoPartidaId ? $headModel->getSiguienteNumeroPartida($tipoPartidaId, $anioFecha) : null;

        $asientoId = $headModel->insert([
            'numero_asiento'     => $nextNum,
            'numero_partida'     => $numPartida,
            'fecha'              => $data['fecha'],
            'descripcion'        => $data['descripcion'],
            'tipo'               => $tipo,
            'tipo_partida_id'    => $tipoPartidaId,
            'estado'             => 'APROBADO',
            'periodo_id'         => (int)$data['periodo_id'],
            'total_debe'         => $totalDebe,
            'total_haber'        => $totalHaber,
            'referencia'         => $data['referencia'] ?? null,
            'usuario_id'         => session()->get('id'),
            'usuario_aprueba_id' => session()->get('id'),
            'fecha_aprobacion'   => date('Y-m-d H:i:s'),
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

        $headModel->aprobarConSaldos($asientoId, $lineas, (int)$data['periodo_id'], $data['fecha'], $data['descripcion'], $tipo, $periodo);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Asiento guardado', 'id' => $asientoId]);
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

    /**
     * GET contabilidad/asientos/plantilla-venta
     *
     * Endpoint AJAX: recibe los datos de un CCF o FAC y devuelve el JSON
     * listo para enviarse a store() y crear el asiento automáticamente.
     *
     * Parámetros GET:
     *   tipo_dte   string  'CCF' | 'FAC'
     *   monto      float   CCF → venta sin IVA  |  FAC → total con IVA
     *   retencion  float   Monto de retención (0 si no aplica)
     *   referencia string  Número del documento, ej: "CCF-00001"
     *   fecha      string  Y-m-d (default: hoy)
     *   periodo_id int     ID del período contable abierto
     *   descripcion string Glosa del asiento (opcional)
     *
     * Respuesta JSON exitosa:
     *   { success: true, desglose: {...}, asiento: { ...payload para store()... } }
     *
     * Respuesta de error:
     *   { success: false, message: '...', errores: [...] }
     */
    public function plantillaVenta()
    {
        helper('cont_ventas');

        $get = $this->request->getGet();

        $tipoDte    = strtoupper(trim($get['tipo_dte']    ?? ''));
        $monto      = (float)($get['monto']               ?? 0);
        $retencion  = (float)($get['retencion']           ?? 0);
        $referencia = trim($get['referencia']             ?? '');
        $fecha      = $get['fecha']                       ?? date('Y-m-d');
        $periodoId  = (int)($get['periodo_id']            ?? 0);
        $descripcion = trim($get['descripcion']           ?? '');

        if (!$tipoDte || $monto <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parámetros requeridos: tipo_dte (CCF|FAC) y monto > 0',
            ]);
        }

        try {
            $resultado = cont_asiento_venta_json(
                $tipoDte, $monto, $retencion,
                $referencia, $periodoId, $fecha, $descripcion
            );
        } catch (\InvalidArgumentException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        if (!$resultado['ok']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(' | ', $resultado['errores']),
                'errores' => $resultado['errores'],
            ]);
        }

        return $this->response->setJSON([
            'success'  => true,
            'desglose' => $resultado['desglose'],
            'asiento'  => $resultado['payload'],
        ]);
    }

    public function edit($id)
    {
        $chk = requerirPermiso('crear_asiento');
        if ($chk !== true) return $chk;

        $headModel    = new ContAsientosHeadModel();
        $detalleModel = new ContAsientosDetalleModel();
        $periodosModel = new ContPeriodosModel();

        $asiento = $headModel->getConDetalle($id);
        if (!$asiento) {
            return redirect()->to(base_url('contabilidad/asientos'))->with('error', 'Asiento no encontrado');
        }

        if ($asiento->estado === 'ANULADO') {
            return redirect()->to(base_url('contabilidad/asientos/' . $id))
                ->with('error', 'No se pueden editar asientos anulados');
        }

        $periodoOriginal = $periodosModel->find($asiento->periodo_id);
        if ($periodoOriginal && $periodoOriginal->estado === 'CERRADO') {
            return redirect()->to(base_url('contabilidad/asientos/' . $id))
                ->with('error', 'No se puede editar un asiento de un período cerrado');
        }

        $lineas       = $detalleModel->getByAsiento($id);
        $tiposPartida = (new ContTiposPartidaModel())->getActivos();

        $periodos = $periodosModel->where('estado', 'ABIERTO')->orderBy('anio', 'DESC')->orderBy('mes', 'DESC')->findAll();

        return view('contabilidad/asientos/edit', [
            'asiento'      => $asiento,
            'lineas'       => $lineas,
            'periodos'     => $periodos,
            'tiposPartida' => $tiposPartida,
        ]);
    }

    public function update($id)
    {
        $chk = requerirPermiso('crear_asiento');
        if ($chk !== true) return $chk;

        $headModel = new ContAsientosHeadModel();
        $asiento   = $headModel->find($id);

        if (!$asiento) {
            return $this->response->setJSON(['success' => false, 'message' => 'Asiento no encontrado']);
        }
        if ($asiento->estado === 'ANULADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pueden editar asientos anulados']);
        }

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
            return $this->response->setJSON(['success' => false, 'message' => 'El asiento no cuadra. Debe = ' . number_format($totalDebe, 2) . ' | Haber = ' . number_format($totalHaber, 2)]);
        }

        $periodosModel  = new ContPeriodosModel();
        $periodoOriginal = $periodosModel->find($asiento->periodo_id);
        if ($periodoOriginal && $periodoOriginal->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'El período está cerrado. No se puede modificar este asiento']);
        }

        $periodo = $periodosModel->find($data['periodo_id']);
        if (!$periodo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Período no encontrado']);
        }
        if ($periodo->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede mover el asiento a un período cerrado']);
        }

        $detalleModel = new ContAsientosDetalleModel();
        $saldosModel  = new ContSaldosCuentasModel();
        $histModel    = new ContTransaccionesHistModel();
        $db           = \Config\Database::connect();

        $tipo          = $data['tipo'] ?? 'DIARIO';
        $tipoPartidaId = !empty($data['tipo_partida_id']) ? (int)$data['tipo_partida_id'] : null;
        $numPartida    = isset($data['numero_partida']) && $data['numero_partida'] !== ''
                            ? (int)$data['numero_partida']
                            : null;

        $db->transStart();

        // Revertir saldos e historial del asiento original
        $oldLineas = $detalleModel->getByAsiento($id);
        foreach ($oldLineas as $l) {
            $saldosModel->upsert((int)$l->cuenta_id, (int)$asiento->periodo_id, -(float)$l->debe, -(float)$l->haber);
        }
        $histModel->eliminarPorAsiento($id);

        $headModel->update($id, [
            'fecha'              => $data['fecha'],
            'descripcion'        => $data['descripcion'],
            'tipo'               => $tipo,
            'tipo_partida_id'    => $tipoPartidaId,
            'numero_partida'     => $numPartida,
            'periodo_id'         => (int)$data['periodo_id'],
            'total_debe'         => $totalDebe,
            'total_haber'        => $totalHaber,
            'referencia'         => $data['referencia'] ?? null,
            'fecha_aprobacion'   => date('Y-m-d H:i:s'),
            'usuario_aprueba_id' => session()->get('id'),
        ]);

        $db->table('cont_asientos_detalle')->where('asiento_id', $id)->delete();

        foreach ($lineas as $i => $l) {
            $detalleModel->insert([
                'asiento_id'  => $id,
                'cuenta_id'   => (int)$l['cuenta_id'],
                'descripcion' => $l['descripcion'] ?? null,
                'debe'        => (float)($l['debe']  ?? 0),
                'haber'       => (float)($l['haber'] ?? 0),
                'orden'       => $i + 1,
            ]);
        }

        $headModel->aprobarConSaldos($id, $lineas, (int)$data['periodo_id'], $data['fecha'], $data['descripcion'], $tipo, $periodo);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error en la base de datos']);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Asiento actualizado correctamente', 'id' => $id]);
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

        $periodo = (new ContPeriodosModel())->find($asiento->periodo_id);
        if ($periodo && $periodo->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'El período está cerrado. No se puede anular este asiento']);
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
