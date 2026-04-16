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

    public function getByPeriodo(int $periodoId)
    {
        return $this->select('cont_saldos_cuentas.*, cont_plan_cuentas.codigo, cont_plan_cuentas.nombre AS cuenta_nombre, cont_plan_cuentas.tipo, cont_plan_cuentas.naturaleza')
                    ->join('cont_plan_cuentas', 'cont_plan_cuentas.id = cont_saldos_cuentas.cuenta_id', 'left')
                    ->where('periodo_id', $periodoId)
                    ->orderBy('cont_plan_cuentas.codigo', 'ASC')
                    ->findAll();
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
