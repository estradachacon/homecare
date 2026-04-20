<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionDetalleModel extends Model
{
    protected $table         = 'consignaciones_detalles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'consignacion_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal',
    ];

    public function getPorConsignacion(int $consignacionId): array
    {
        return $this->select('consignaciones_detalles.*, productos.descripcion as producto_nombre, productos.codigo as producto_codigo')
            ->join('productos', 'productos.id = consignaciones_detalles.producto_id', 'left')
            ->where('consignacion_id', $consignacionId)
            ->findAll();
    }
}
