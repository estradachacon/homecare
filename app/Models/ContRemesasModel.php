<?php

namespace App\Models;

use CodeIgniter\Model;

class ContRemesasModel extends Model
{
    protected $table         = 'cont_remesas_head';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'numero_remesa', 'fecha', 'descripcion', 'tipo_partida_id',
        'total', 'estado', 'observaciones', 'usuario_id',
        'anulado_por', 'fecha_anulacion', 'motivo_anulacion',
    ];

    public function getSiguienteNumero(): string
    {
        $row  = \Config\Database::connect()
                    ->query("SELECT MAX(id) AS max_id FROM cont_remesas_head")
                    ->getRow();
        $next = ($row->max_id ?? 0) + 1;
        return 'REM-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function getListado(array $filtros = [], int $perPage = 20): array
    {
        $this->select('cont_remesas_head.*,
                       tp.nombre AS tipo_partida_nombre,
                       u.user_name AS usuario_nombre,
                       (SELECT COUNT(*) FROM cont_remesas_detalle WHERE remesa_id = cont_remesas_head.id) AS num_asientos')
             ->join('cont_tipos_partida tp', 'tp.id = cont_remesas_head.tipo_partida_id', 'left')
             ->join('users u', 'u.id = cont_remesas_head.usuario_id', 'left');

        if (!empty($filtros['estado'])) {
            $this->where('cont_remesas_head.estado', $filtros['estado']);
        }
        if (!empty($filtros['tipo_partida_id'])) {
            $this->where('cont_remesas_head.tipo_partida_id', (int)$filtros['tipo_partida_id']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $this->where('cont_remesas_head.fecha >=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $this->where('cont_remesas_head.fecha <=', $filtros['fecha_hasta']);
        }

        return $this->orderBy('cont_remesas_head.id', 'DESC')->paginate($perPage);
    }

    public function getConDetalle(int $id): ?object
    {
        return $this->select('cont_remesas_head.*, tp.nombre AS tipo_partida_nombre, u.user_name AS usuario_nombre')
                    ->join('cont_tipos_partida tp', 'tp.id = cont_remesas_head.tipo_partida_id', 'left')
                    ->join('users u', 'u.id = cont_remesas_head.usuario_id', 'left')
                    ->find($id);
    }
}
