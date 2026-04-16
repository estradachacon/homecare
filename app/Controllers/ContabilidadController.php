<?php

namespace App\Controllers;

use App\Models\ContAsientosHeadModel;
use App\Models\ContPeriodosModel;
use App\Models\ContSaldosCuentasModel;
use App\Models\ContPlanCuentasModel;

class ContabilidadController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_contabilidad');
        if ($chk !== true) return $chk;

        $periodoModel  = new ContPeriodosModel();
        $asientoModel  = new ContAsientosHeadModel();
        $saldosModel   = new ContSaldosCuentasModel();
        $cuentasModel  = new ContPlanCuentasModel();

        $periodoActual = $periodoModel->getPeriodoActual();

        $stats = [
            'total_cuentas'   => $cuentasModel->where('acepta_movimientos', 1)->countAllResults(),
            'total_periodos'  => $periodoModel->countAllResults(),
            'periodo_actual'  => $periodoActual,
            'asientos_mes'    => 0,
            'asientos_aprobados' => 0,
            'asientos_borrador'  => 0,
        ];

        if ($periodoActual) {
            $db = \Config\Database::connect();
            $row = $db->query(
                'SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN estado="APROBADO" THEN 1 ELSE 0 END) AS aprobados,
                    SUM(CASE WHEN estado="BORRADOR" THEN 1 ELSE 0 END) AS borrador
                 FROM cont_asientos_head WHERE periodo_id = ?',
                [$periodoActual->id]
            )->getRow();

            $stats['asientos_mes']       = (int)($row->total ?? 0);
            $stats['asientos_aprobados'] = (int)($row->aprobados ?? 0);
            $stats['asientos_borrador']  = (int)($row->borrador ?? 0);
        }

        // Últimos 5 asientos del período actual
        $ultimosAsientos = [];
        if ($periodoActual) {
            $ultimosAsientos = $asientoModel
                ->where('periodo_id', $periodoActual->id)
                ->where('estado !=', 'ANULADO')
                ->orderBy('id', 'DESC')
                ->findAll(5);
        }

        // Saldos por tipo de cuenta (solo período actual)
        $saldosPorTipo = [];
        if ($periodoActual) {
            $db = \Config\Database::connect();
            $rows = $db->query(
                'SELECT pc.tipo,
                        SUM(sc.total_debe) AS total_debe,
                        SUM(sc.total_haber) AS total_haber,
                        SUM(sc.saldo_final) AS saldo_final
                 FROM cont_saldos_cuentas sc
                 INNER JOIN cont_plan_cuentas pc ON pc.id = sc.cuenta_id
                 WHERE sc.periodo_id = ?
                 GROUP BY pc.tipo
                 ORDER BY pc.tipo ASC',
                [$periodoActual->id]
            )->getResult();
            foreach ($rows as $r) {
                $saldosPorTipo[$r->tipo] = $r;
            }
        }

        return view('contabilidad/dashboard', [
            'stats'           => $stats,
            'ultimosAsientos' => $ultimosAsientos,
            'saldosPorTipo'   => $saldosPorTipo,
            'periodoActual'   => $periodoActual,
        ]);
    }
}
