<?php

namespace App\Models;

use CodeIgniter\Model;

class ContAsientosHeadModel extends Model
{
    protected $table         = 'cont_asientos_head';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'numero_asiento', 'fecha', 'descripcion', 'tipo', 'estado',
        'periodo_id', 'total_debe', 'total_haber', 'referencia',
        'usuario_id', 'usuario_aprueba_id', 'fecha_aprobacion', 'motivo_anulacion',
    ];

    public function getSiguienteNumero(): int
    {
        $db = \Config\Database::connect();
        $row = $db->query('SELECT COALESCE(MAX(numero_asiento),0)+1 AS siguiente FROM cont_asientos_head')->getRow();
        return (int)($row->siguiente ?? 1);
    }

    public function getConDetalle(int $id)
    {
        return $this->select('cont_asientos_head.*, cont_periodos.anio, cont_periodos.mes, users.user_name AS usuario_nombre')
                    ->join('cont_periodos', 'cont_periodos.id = cont_asientos_head.periodo_id', 'left')
                    ->join('users', 'users.id = cont_asientos_head.usuario_id', 'left')
                    ->where('cont_asientos_head.id', $id)
                    ->first();
    }

    public function getByPeriodo(int $periodoId)
    {
        return $this->where('periodo_id', $periodoId)
                    ->where('estado !=', 'ANULADO')
                    ->orderBy('numero_asiento', 'ASC')
                    ->findAll();
    }

    public function getListadoFiltrado(array $filtros, int $perPage = 25)
    {
        $q = $this->select('cont_asientos_head.*, cont_periodos.anio, cont_periodos.mes')
                  ->join('cont_periodos', 'cont_periodos.id = cont_asientos_head.periodo_id', 'left');

        if (!empty($filtros['periodo_id'])) {
            $q->where('cont_asientos_head.periodo_id', $filtros['periodo_id']);
        }
        if (!empty($filtros['tipo'])) {
            $q->where('cont_asientos_head.tipo', $filtros['tipo']);
        }
        if (!empty($filtros['estado'])) {
            $q->where('cont_asientos_head.estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $q->where('cont_asientos_head.fecha >=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $q->where('cont_asientos_head.fecha <=', $filtros['fecha_hasta']);
        }
        if (!empty($filtros['descripcion'])) {
            $q->like('cont_asientos_head.descripcion', $filtros['descripcion']);
        }

        return $q->orderBy('cont_asientos_head.fecha', 'DESC')
                 ->orderBy('cont_asientos_head.numero_asiento', 'DESC')
                 ->paginate($perPage);
    }
}
