<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionLoteModel extends Model
{
    protected $table         = 'consignacion_lotes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'producto_id', 'numero_lote', 'fecha_vencimiento', 'manufactura', 'descripcion', 'activo',
    ];

    public function getPorProducto(int $productoId, string $q = ''): array
    {
        $builder = $this->where('producto_id', $productoId)->where('activo', 1);

        if ($q !== '') {
            $builder->like('numero_lote', $q, 'both');
        }

        return $builder->orderBy('fecha_vencimiento', 'ASC')->findAll();
    }

    public function listarConProducto(array $filtros = [])
    {
        $this->select('consignacion_lotes.*, productos.descripcion as producto_nombre, productos.codigo as producto_codigo')
            ->join('productos', 'productos.id = consignacion_lotes.producto_id', 'left');

        if (!empty($filtros['producto_id'])) {
            $this->where('consignacion_lotes.producto_id', $filtros['producto_id']);
        }
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $this->where('consignacion_lotes.activo', $filtros['activo']);
        }

        return $this->orderBy('consignacion_lotes.id', 'DESC');
    }
}
