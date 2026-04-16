<?php

namespace App\Models;

use CodeIgniter\Model;

class ContAsientosDetalleModel extends Model
{
    protected $table         = 'cont_asientos_detalle';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'asiento_id', 'cuenta_id', 'descripcion', 'debe', 'haber', 'orden',
    ];

    public function getByAsiento(int $asientoId)
    {
        return $this->select('cont_asientos_detalle.*, cont_plan_cuentas.codigo, cont_plan_cuentas.nombre AS cuenta_nombre, cont_plan_cuentas.naturaleza')
                    ->join('cont_plan_cuentas', 'cont_plan_cuentas.id = cont_asientos_detalle.cuenta_id', 'left')
                    ->where('asiento_id', $asientoId)
                    ->orderBy('orden', 'ASC')
                    ->findAll();
    }

    public function eliminarPorAsiento(int $asientoId)
    {
        return $this->where('asiento_id', $asientoId)->delete();
    }

    public function getTotalesPorCuenta(int $cuentaId, int $periodoId)
    {
        $db = \Config\Database::connect();
        return $db->query(
            'SELECT SUM(d.debe) AS total_debe, SUM(d.haber) AS total_haber
             FROM cont_asientos_detalle d
             INNER JOIN cont_asientos_head h ON h.id = d.asiento_id
             WHERE d.cuenta_id = ? AND h.periodo_id = ? AND h.estado = "APROBADO"',
            [$cuentaId, $periodoId]
        )->getRow();
    }
}
