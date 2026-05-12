<?php

namespace App\Models;

use CodeIgniter\Model;

class ContSaldosCuentasModel extends Model
{
    protected $table         = 'cont_saldos_cuentas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'cuenta_id', 'periodo_id', 'saldo_inicial',
        'total_debe', 'total_haber', 'saldo_final',
    ];

    public function getByCuentaPeriodo(int $cuentaId, int $periodoId)
    {
        return $this->where('cuenta_id', $cuentaId)->where('periodo_id', $periodoId)->first();
    }

    /**
     * Retorna saldos del período con saldo_inicial y saldo_final calculados
     * dinámicamente desde los movimientos reales, sin depender del valor almacenado.
     *
     * - ACTIVO / PASIVO / CAPITAL: acumulan desde el inicio de todos los períodos anteriores.
     * - INGRESO / COSTO / GASTO: acumulan solo desde el inicio del mismo año (se resetean en cierre anual).
     */
    public function getByPeriodo(int $periodoId)
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT
                sc.id,
                sc.cuenta_id,
                sc.periodo_id,
                cp.codigo,
                cp.nombre          AS cuenta_nombre,
                cp.tipo,
                cp.naturaleza,
                sc.total_debe,
                sc.total_haber,

                /* ── Saldo inicial dinámico ───────────────────────────── */
                CASE
                    WHEN cp.tipo IN ('ACTIVO','PASIVO','CAPITAL') THEN
                        COALESCE((
                            SELECT SUM(sc2.total_debe - sc2.total_haber)
                            FROM   cont_saldos_cuentas sc2
                            JOIN   cont_periodos        p2 ON p2.id = sc2.periodo_id
                            WHERE  sc2.cuenta_id = sc.cuenta_id
                              AND  (p2.anio < p.anio OR (p2.anio = p.anio AND p2.mes < p.mes))
                        ), 0)
                    ELSE
                        COALESCE((
                            SELECT SUM(sc2.total_debe - sc2.total_haber)
                            FROM   cont_saldos_cuentas sc2
                            JOIN   cont_periodos        p2 ON p2.id = sc2.periodo_id
                            WHERE  sc2.cuenta_id = sc.cuenta_id
                              AND  p2.anio = p.anio
                              AND  p2.mes  < p.mes
                        ), 0)
                END AS saldo_inicial,

                /* ── Saldo final = saldo_inicial + movimientos del período ── */
                CASE
                    WHEN cp.tipo IN ('ACTIVO','PASIVO','CAPITAL') THEN
                        COALESCE((
                            SELECT SUM(sc2.total_debe - sc2.total_haber)
                            FROM   cont_saldos_cuentas sc2
                            JOIN   cont_periodos        p2 ON p2.id = sc2.periodo_id
                            WHERE  sc2.cuenta_id = sc.cuenta_id
                              AND  (p2.anio < p.anio OR (p2.anio = p.anio AND p2.mes < p.mes))
                        ), 0) + sc.total_debe - sc.total_haber
                    ELSE
                        COALESCE((
                            SELECT SUM(sc2.total_debe - sc2.total_haber)
                            FROM   cont_saldos_cuentas sc2
                            JOIN   cont_periodos        p2 ON p2.id = sc2.periodo_id
                            WHERE  sc2.cuenta_id = sc.cuenta_id
                              AND  p2.anio = p.anio
                              AND  p2.mes  < p.mes
                        ), 0) + sc.total_debe - sc.total_haber
                END AS saldo_final

            FROM  cont_saldos_cuentas sc
            JOIN  cont_periodos       p  ON p.id  = sc.periodo_id
            JOIN  cont_plan_cuentas   cp ON cp.id = sc.cuenta_id
            WHERE sc.periodo_id = ?
            ORDER BY cp.codigo ASC
        ", [$periodoId])->getResult();
    }

    /**
     * Saldo inicial dinámico para una cuenta en un período puntual.
     * Útil cuando se necesita el valor en código PHP (ej. cierre de mes).
     */
    public function getSaldoInicialDinamico(int $cuentaId, int $periodoId): float
    {
        $db = \Config\Database::connect();

        $meta = $db->query(
            'SELECT cp.tipo, p.anio, p.mes
             FROM cont_plan_cuentas cp, cont_periodos p
             WHERE cp.id = ? AND p.id = ?',
            [$cuentaId, $periodoId]
        )->getRow();

        if (!$meta) return 0.0;

        if (in_array($meta->tipo, ['ACTIVO', 'PASIVO', 'CAPITAL'])) {
            $row = $db->query(
                'SELECT COALESCE(SUM(sc.total_debe - sc.total_haber), 0) AS si
                 FROM cont_saldos_cuentas sc
                 JOIN cont_periodos p ON p.id = sc.periodo_id
                 WHERE sc.cuenta_id = ?
                   AND (p.anio < ? OR (p.anio = ? AND p.mes < ?))',
                [$cuentaId, $meta->anio, $meta->anio, $meta->mes]
            )->getRow();
        } else {
            $row = $db->query(
                'SELECT COALESCE(SUM(sc.total_debe - sc.total_haber), 0) AS si
                 FROM cont_saldos_cuentas sc
                 JOIN cont_periodos p ON p.id = sc.periodo_id
                 WHERE sc.cuenta_id = ?
                   AND p.anio = ? AND p.mes < ?',
                [$cuentaId, $meta->anio, $meta->mes]
            )->getRow();
        }

        return (float)($row->si ?? 0.0);
    }

    public function upsert(int $cuentaId, int $periodoId, float $debe, float $haber)
    {
        $existing = $this->getByCuentaPeriodo($cuentaId, $periodoId);
        if ($existing) {
            $this->update($existing->id, [
                'total_debe'  => (float)$existing->total_debe  + $debe,
                'total_haber' => (float)$existing->total_haber + $haber,
                'saldo_final' => (float)$existing->saldo_inicial + (float)$existing->total_debe + $debe - ((float)$existing->total_haber + $haber),
            ]);
        } else {
            $this->insert([
                'cuenta_id'     => $cuentaId,
                'periodo_id'    => $periodoId,
                'saldo_inicial' => 0,
                'total_debe'    => $debe,
                'total_haber'   => $haber,
                'saldo_final'   => $debe - $haber,
            ]);
        }
    }
}
