<?php

namespace App\Models;

use CodeIgniter\Model;

class ContSaldosHistoricosModel extends Model
{
    protected $table         = 'cont_saldos_historicos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'cuenta_id', 'anio', 'mes', 'saldo_inicial',
        'total_debe', 'total_haber', 'saldo_final',
    ];

    public function getByAnio(int $anio)
    {
        return $this->select('cont_saldos_historicos.*, cont_plan_cuentas.codigo, cont_plan_cuentas.nombre AS cuenta_nombre, cont_plan_cuentas.tipo, cont_plan_cuentas.naturaleza')
                    ->join('cont_plan_cuentas', 'cont_plan_cuentas.id = cont_saldos_historicos.cuenta_id', 'left')
                    ->where('anio', $anio)
                    ->orderBy('cont_plan_cuentas.codigo', 'ASC')
                    ->orderBy('mes', 'ASC')
                    ->findAll();
    }

    public function getResumenAnual(int $anio)
    {
        $db = \Config\Database::connect();
        return $db->query(
            'SELECT sh.cuenta_id, pc.codigo, pc.nombre AS cuenta_nombre, pc.tipo, pc.naturaleza,
                    SUM(sh.total_debe) AS total_debe_anual,
                    SUM(sh.total_haber) AS total_haber_anual,
                    MIN(sh.saldo_inicial) AS saldo_apertura,
                    MAX(sh.saldo_final) AS saldo_cierre
             FROM cont_saldos_historicos sh
             INNER JOIN cont_plan_cuentas pc ON pc.id = sh.cuenta_id
             WHERE sh.anio = ?
             GROUP BY sh.cuenta_id, pc.codigo, pc.nombre, pc.tipo, pc.naturaleza
             ORDER BY pc.codigo ASC',
            [$anio]
        )->getResult();
    }

    public function getComparativo(int $anio1, int $anio2)
    {
        $db = \Config\Database::connect();
        return $db->query(
            'SELECT pc.codigo, pc.nombre AS cuenta_nombre, pc.tipo,
                    COALESCE(SUM(CASE WHEN sh.anio=? THEN sh.saldo_final ELSE 0 END),0) AS saldo_anio1,
                    COALESCE(SUM(CASE WHEN sh.anio=? THEN sh.saldo_final ELSE 0 END),0) AS saldo_anio2
             FROM cont_plan_cuentas pc
             LEFT JOIN cont_saldos_historicos sh ON sh.cuenta_id = pc.id
             WHERE pc.acepta_movimientos = 1
             GROUP BY pc.id, pc.codigo, pc.nombre, pc.tipo
             ORDER BY pc.codigo ASC',
            [$anio1, $anio2]
        )->getResult();
    }
}
