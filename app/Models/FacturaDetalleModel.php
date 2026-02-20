<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaDetalleModel extends Model
{
    protected $table            = 'factura_detalles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'factura_id',
        'num_item',
        'tipo_item',
        'codigo',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'precio_unitario',
        'monto_descuento',
        'venta_no_sujeta',
        'venta_exenta',
        'venta_gravada',
        'iva_item',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Métodos útiles
    |--------------------------------------------------------------------------
    */

    public function getByFactura($facturaId)
    {
        return $this->where('factura_id', $facturaId)
                    ->orderBy('num_item', 'ASC')
                    ->findAll();
    }

    public function eliminarPorFactura($facturaId)
    {
        return $this->where('factura_id', $facturaId)->delete();
    }
}