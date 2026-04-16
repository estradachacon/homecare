<?php

namespace App\Models;

use CodeIgniter\Model;

class ContTransaccionesHistModel extends Model
{
    protected $table         = 'cont_transacciones_hist';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'asiento_id', 'cuenta_id', 'fecha', 'descripcion',
        'debe', 'haber', 'saldo_acumulado', 'anio', 'mes',
        'tipo_asiento', 'created_at',
    ];

    public function getByCuenta(int $cuentaId, int $anio, ?int $mes = null)
    {
        $q = $this->select('cont_transacciones_hist.*, cont_asientos_head.numero_asiento, cont_asientos_head.tipo AS tipo_asiento_head')
                  ->join('cont_asientos_head', 'cont_asientos_head.id = cont_transacciones_hist.asiento_id', 'left')
                  ->where('cont_transacciones_hist.cuenta_id', $cuentaId)
                  ->where('cont_transacciones_hist.anio', $anio);
        if ($mes) {
            $q->where('cont_transacciones_hist.mes', $mes);
        }
        return $q->orderBy('fecha', 'ASC')->orderBy('id', 'ASC')->findAll();
    }

    public function getLibroMayor(int $cuentaId, string $fechaDesde, string $fechaHasta)
    {
        return $this->select('cont_transacciones_hist.*, cont_asientos_head.numero_asiento, cont_asientos_head.descripcion AS desc_asiento')
                    ->join('cont_asientos_head', 'cont_asientos_head.id = cont_transacciones_hist.asiento_id', 'left')
                    ->where('cont_transacciones_hist.cuenta_id', $cuentaId)
                    ->where('cont_transacciones_hist.fecha >=', $fechaDesde)
                    ->where('cont_transacciones_hist.fecha <=', $fechaHasta)
                    ->orderBy('cont_transacciones_hist.fecha', 'ASC')
                    ->orderBy('cont_transacciones_hist.id', 'ASC')
                    ->findAll();
    }

    public function eliminarPorAsiento(int $asientoId)
    {
        return $this->where('asiento_id', $asientoId)->delete();
    }
}
