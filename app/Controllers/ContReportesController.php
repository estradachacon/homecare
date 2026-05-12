<?php

namespace App\Controllers;

use App\Models\ContPeriodosModel;
use App\Models\ContPlanCuentasModel;
use App\Models\ContAsientosHeadModel;
use App\Models\ContAsientosDetalleModel;
use App\Models\ContTransaccionesHistModel;
use App\Models\ContSaldosCuentasModel;
use App\Models\ContSaldosHistoricosModel;

class ContReportesController extends BaseController
{
    // ─── LISTADOS ────────────────────────────────────────────────

    public function relacionCuentas()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $model   = new ContPlanCuentasModel();
        $cuentas = $model->orderBy('codigo','ASC')->findAll();

        return view('contabilidad/listados/relacion_cuentas', ['cuentas' => $cuentas]);
    }

    public function costos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $saldosModel   = new ContSaldosCuentasModel();

        $periodos  = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId = $this->request->getGet('periodo_id');
        $filas     = [];

        if ($periodoId) {
            $db   = \Config\Database::connect();
            $filas = $db->query(
                'SELECT pc.codigo, pc.nombre, sc.total_debe, sc.total_haber, sc.saldo_final
                 FROM cont_saldos_cuentas sc
                 INNER JOIN cont_plan_cuentas pc ON pc.id = sc.cuenta_id
                 WHERE sc.periodo_id = ? AND pc.tipo = "COSTO"
                 ORDER BY pc.codigo ASC',
                [$periodoId]
            )->getResult();
        }

        return view('contabilidad/listados/costos', [
            'periodos'  => $periodos,
            'periodoId' => $periodoId,
            'filas'     => $filas,
        ]);
    }

    public function gastos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $periodos  = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId = $this->request->getGet('periodo_id');
        $filas     = [];

        if ($periodoId) {
            $db   = \Config\Database::connect();
            $filas = $db->query(
                'SELECT pc.codigo, pc.nombre, sc.total_debe, sc.total_haber, sc.saldo_final
                 FROM cont_saldos_cuentas sc
                 INNER JOIN cont_plan_cuentas pc ON pc.id = sc.cuenta_id
                 WHERE sc.periodo_id = ? AND pc.tipo = "GASTO"
                 ORDER BY pc.codigo ASC',
                [$periodoId]
            )->getResult();
        }

        return view('contabilidad/listados/gastos', [
            'periodos'  => $periodos,
            'periodoId' => $periodoId,
            'filas'     => $filas,
        ]);
    }

    public function comparativos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $histModel = new ContSaldosHistoricosModel();
        $anio1     = $this->request->getGet('anio1') ?? (int)date('Y') - 1;
        $anio2     = $this->request->getGet('anio2') ?? (int)date('Y');
        $filas     = [];

        if ($anio1 && $anio2) {
            $filas = $histModel->getComparativo((int)$anio1, (int)$anio2);
        }

        $db   = \Config\Database::connect();
        $anios = $db->query('SELECT DISTINCT anio FROM cont_saldos_historicos ORDER BY anio DESC')->getResultArray();
        $anios = array_column($anios, 'anio');

        return view('contabilidad/listados/comparativos', [
            'filas' => $filas,
            'anio1' => $anio1,
            'anio2' => $anio2,
            'anios' => $anios,
        ]);
    }

    public function catalogos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $model   = new ContPlanCuentasModel();
        $tipo    = $this->request->getGet('tipo') ?? '';
        $nivel   = $this->request->getGet('nivel') ?? '';

        $q = $model->orderBy('codigo','ASC');
        if ($tipo) $q->where('tipo', $tipo);
        if ($nivel) $q->where('nivel', $nivel);

        $cuentas = $q->findAll();

        return view('contabilidad/listados/catalogos', [
            'cuentas' => $cuentas,
            'tipo'    => $tipo,
            'nivel'   => $nivel,
        ]);
    }

    // ─── REPORTES ────────────────────────────────────────────────

    public function diario()
    {
        $chk = requerirPermiso('ver_reportes_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $headModel     = new ContAsientosHeadModel();
        $detalleModel  = new ContAsientosDetalleModel();

        $periodos     = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId    = $this->request->getGet('periodo_id');
        $fechaDesde   = $this->request->getGet('fecha_desde');
        $fechaHasta   = $this->request->getGet('fecha_hasta');
        $asientosData = [];

        if ($periodoId || ($fechaDesde && $fechaHasta)) {
            $filtros = ['periodo_id' => $periodoId, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta, 'estado' => 'APROBADO'];
            $asientosHead = $headModel->getListadoFiltrado($filtros, 99999);
            foreach ($asientosHead as $a) {
                $a->lineas = $detalleModel->getByAsiento($a->id);
                $asientosData[] = $a;
            }
        }

        return view('contabilidad/reportes/diario', [
            'periodos'   => $periodos,
            'periodoId'  => $periodoId,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'asientos'   => $asientosData,
        ]);
    }

    public function mayor()
    {
        $chk = requerirPermiso('ver_reportes_contables');
        if ($chk !== true) return $chk;

        $cuentasModel  = new ContPlanCuentasModel();
        $histModel     = new ContTransaccionesHistModel();
        $periodosModel = new ContPeriodosModel();

        $periodos   = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $cuentaId   = $this->request->getGet('cuenta_id');
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-01-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $movimientos = [];
        $cuenta      = null;

        if ($cuentaId) {
            $cuenta      = $cuentasModel->find($cuentaId);
            $movimientos = $histModel->getLibroMayor($cuentaId, $fechaDesde, $fechaHasta);
        }

        return view('contabilidad/reportes/mayor', [
            'periodos'   => $periodos,
            'cuentaId'   => $cuentaId,
            'cuenta'     => $cuenta,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'movimientos'=> $movimientos,
        ]);
    }

    public function auxiliar()
    {
        $chk = requerirPermiso('ver_reportes_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $planModel     = new ContPlanCuentasModel();
        $db            = \Config\Database::connect();

        $periodos = $periodosModel->orderBy('anio', 'DESC')->orderBy('mes', 'DESC')->findAll();
        $cuentas  = $planModel->where('acepta_movimientos', 1)->orderBy('codigo', 'ASC')->findAll();

        $periodoId   = $this->request->getGet('periodo_id');
        $fechaDesde  = $this->request->getGet('fecha_desde');
        $fechaHasta  = $this->request->getGet('fecha_hasta');
        $cuentaId    = $this->request->getGet('cuenta_id');
        $subtotalDia = $this->request->getGet('subtotal_dia') === '1';
        $subtotalMes = $this->request->getGet('subtotal_mes') === '1';
        $formato     = $this->request->getGet('formato') ?: 'contable';

        $movimientos   = [];
        $saldosPrevios = [];
        $filtrado      = $periodoId || $fechaDesde || $fechaHasta || $cuentaId;

        if ($filtrado) {
            // Individual movements query
            $sql    = "SELECT d.id, d.asiento_id, d.cuenta_id,
                              d.descripcion AS linea_desc, d.debe, d.haber, d.orden,
                              h.fecha, h.numero_asiento,
                              h.descripcion AS asiento_desc, h.referencia, h.periodo_id,
                              cp.codigo, cp.nombre AS cuenta_nombre, cp.tipo, cp.naturaleza
                       FROM   cont_asientos_detalle d
                       JOIN   cont_asientos_head   h  ON h.id  = d.asiento_id
                       JOIN   cont_plan_cuentas    cp ON cp.id = d.cuenta_id
                       WHERE  h.estado = 'APROBADO'";
            $params = [];

            if ($periodoId) { $sql .= ' AND h.periodo_id = ?';  $params[] = $periodoId; }
            if ($fechaDesde) { $sql .= ' AND h.fecha >= ?';     $params[] = $fechaDesde; }
            if ($fechaHasta) { $sql .= ' AND h.fecha <= ?';     $params[] = $fechaHasta; }
            if ($cuentaId)   { $sql .= ' AND d.cuenta_id = ?';  $params[] = $cuentaId; }

            $sql .= ' ORDER BY cp.codigo, h.fecha, h.numero_asiento, d.orden';
            $movimientos = $db->query($sql, $params)->getResult();

            // Saldo previo per account (movements before the filtered range)
            if ($periodoId || $fechaDesde) {
                $sqlPrev    = "SELECT d.cuenta_id,
                                      COALESCE(SUM(d.debe - d.haber), 0) AS saldo_previo
                               FROM   cont_asientos_detalle d
                               JOIN   cont_asientos_head   h  ON h.id  = d.asiento_id
                               JOIN   cont_periodos        pp ON pp.id = h.periodo_id
                               WHERE  h.estado = 'APROBADO'";
                $paramsPrev = [];

                if ($periodoId) {
                    $po          = $periodosModel->find($periodoId);
                    $sqlPrev    .= ' AND (pp.anio < ? OR (pp.anio = ? AND pp.mes < ?))';
                    $paramsPrev[] = $po->anio;
                    $paramsPrev[] = $po->anio;
                    $paramsPrev[] = $po->mes;
                } elseif ($fechaDesde) {
                    $sqlPrev    .= ' AND h.fecha < ?';
                    $paramsPrev[] = $fechaDesde;
                }

                if ($cuentaId) { $sqlPrev .= ' AND d.cuenta_id = ?'; $paramsPrev[] = $cuentaId; }

                $sqlPrev .= ' GROUP BY d.cuenta_id';
                foreach ($db->query($sqlPrev, $paramsPrev)->getResult() as $row) {
                    $saldosPrevios[(int)$row->cuenta_id] = (float)$row->saldo_previo;
                }
            }
        }

        return view('contabilidad/reportes/auxiliar', [
            'periodos'      => $periodos,
            'cuentas'       => $cuentas,
            'periodoId'     => $periodoId,
            'fechaDesde'    => $fechaDesde,
            'fechaHasta'    => $fechaHasta,
            'cuentaId'      => $cuentaId,
            'subtotalDia'   => $subtotalDia,
            'subtotalMes'   => $subtotalMes,
            'formato'       => $formato,
            'movimientos'   => $movimientos,
            'saldosPrevios' => $saldosPrevios,
            'filtrado'      => $filtrado,
        ]);
    }

    // ─── MANTENIMIENTOS ─────────────────────────────────────────

    public function acumuladosActuales()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $saldosModel   = new ContSaldosCuentasModel();

        $periodos  = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId = $this->request->getGet('periodo_id');
        $filas     = [];

        if ($periodoId) {
            $filas = $saldosModel->getByPeriodo($periodoId);
        }

        return view('contabilidad/mantenimientos/acumulados_actuales', [
            'periodos'  => $periodos,
            'periodoId' => $periodoId,
            'filas'     => $filas,
        ]);
    }

    public function acumuladosHistoricos()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $histModel = new ContSaldosHistoricosModel();
        $db        = \Config\Database::connect();
        $anios     = $db->query('SELECT DISTINCT anio FROM cont_saldos_historicos ORDER BY anio DESC')->getResultArray();
        $anios     = array_column($anios, 'anio');
        $anioSel   = $this->request->getGet('anio') ?? (int)date('Y');
        $filas     = $anioSel ? $histModel->getByAnio((int)$anioSel) : [];

        return view('contabilidad/mantenimientos/acumulados_historicos', [
            'anios'   => $anios,
            'anioSel' => $anioSel,
            'filas'   => $filas,
        ]);
    }

    public function transaccionesHistoricas()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $histModel    = new ContTransaccionesHistModel();
        $cuentasModel = new ContPlanCuentasModel();
        $db           = \Config\Database::connect();

        $anios    = $db->query('SELECT DISTINCT anio FROM cont_transacciones_hist ORDER BY anio DESC')->getResultArray();
        $anios    = array_column($anios, 'anio');
        $cuentaId = $this->request->getGet('cuenta_id');
        $anioSel  = (int)($this->request->getGet('anio') ?: date('Y'));
        $mesSel   = $this->request->getGet('mes') ?: null;
        $filas    = [];
        $cuenta   = null;

        // Asegurar que el año actual esté en la lista aunque la tabla esté vacía
        if (!in_array((string)$anioSel, $anios)) {
            array_unshift($anios, (string)$anioSel);
        }

        if ($cuentaId) {
            $cuenta = $cuentasModel->find($cuentaId);
            $filas  = $histModel->getByCuenta((int)$cuentaId, $anioSel, $mesSel ? (int)$mesSel : null);
        }

        return view('contabilidad/mantenimientos/transacciones_historicas', [
            'anios'    => $anios,
            'anioSel'  => $anioSel,
            'mesSel'   => $mesSel,
            'cuentaId' => $cuentaId,
            'cuenta'   => $cuenta,
            'filas'    => $filas,
        ]);
    }
}
