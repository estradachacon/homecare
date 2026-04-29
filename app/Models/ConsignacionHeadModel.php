<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionHeadModel extends Model
{
    protected $table         = 'consignaciones_head';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'numero', 'vendedor_id', 'nombre', 'concepto',
        'fecha', 'hora', 'fecha_generacion',
        'subtotal', 'observaciones', 'estado',
        'anulada', 'anulada_por', 'fecha_anulacion', 'created_by',
        'doctor_id',
        'cliente_id',
    ];

    public function listar(array $filtros = [])
    {
        $this->select('consignaciones_head.*, sellers.seller as vendedor_nombre')
            ->join('sellers', 'sellers.id = consignaciones_head.vendedor_id', 'left');

        if (!empty($filtros['vendedor_id'])) {
            $this->where('consignaciones_head.vendedor_id', $filtros['vendedor_id']);
        }
        if (!empty($filtros['estado'])) {
            $this->where('consignaciones_head.estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $this->where('DATE(consignaciones_head.fecha) >=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $this->where('DATE(consignaciones_head.fecha) <=', $filtros['fecha_fin']);
        }

        return $this->orderBy('consignaciones_head.id', 'DESC');
    }

    public function getConVendedor(int $id)
    {
        return $this->select('consignaciones_head.*, sellers.seller as vendedor_nombre')
            ->join('sellers', 'sellers.id = consignaciones_head.vendedor_id', 'left')
            ->where('consignaciones_head.id', $id)
            ->first();
    }

    public function siguienteNumero(): string
    {
        $db  = \Config\Database::connect();
        $row = $db->table('consignaciones_head')
            ->selectMax('id')
            ->get()->getRow();

        $next = ($row->id ?? 0) + 1;

        return 'NE-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
