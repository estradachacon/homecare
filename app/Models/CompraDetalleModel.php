<?php

namespace App\Models;

use CodeIgniter\Model;

class CompraDetalleModel extends Model
{
    protected $table            = 'compras_detalles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'compra_id',
        'num_item',
        'tipo_item',
        'codigo',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'venta_gravada',
        'precio_unitario',
        'monto_descuento',
        'iva_item',
        'producto_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = false;

    /*
    |------------------------------------------------------------------
    | Métodos útiles
    |------------------------------------------------------------------
    */

    public function getByCompra($compraId)
    {
        return $this->where('compra_id', $compraId)
                    ->orderBy('num_item', 'ASC')
                    ->findAll();
    }

    public function eliminarPorCompra($compraId)
    {
        return $this->where('compra_id', $compraId)->delete();
    }

    public function totalPorCompra($compraId)
    {
        return $this->select('SUM(cantidad * precio_unitario) as total')
                    ->where('compra_id', $compraId)
                    ->first();
    }
}