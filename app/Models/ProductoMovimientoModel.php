<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoMovimientoModel extends Model
{
    protected $table            = 'productos_movimientos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'referencia_tipo',
        'referencia_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Métodos útiles
    |--------------------------------------------------------------------------
    */

    public function registrarVenta($productoId, $cantidad, $facturaId)
    {
        return $this->insert([
            'producto_id' => $productoId,
            'tipo_movimiento' => 'venta',
            'cantidad' => -abs($cantidad),
            'referencia_tipo' => 'factura',
            'referencia_id' => $facturaId
        ]);
    }

    public function registrarCompra($productoId, $cantidad, $referenciaId = null)
    {
        return $this->insert([
            'producto_id' => $productoId,
            'tipo_movimiento' => 'compra',
            'cantidad' => abs($cantidad),
            'referencia_tipo' => 'compra',
            'referencia_id' => $referenciaId
        ]);
    }

    public function registrarAjuste($productoId, $cantidad)
    {
        return $this->insert([
            'producto_id' => $productoId,
            'tipo_movimiento' => 'ajuste',
            'cantidad' => $cantidad,
            'referencia_tipo' => 'ajuste'
        ]);
    }

    public function saldoProducto($productoId)
    {
        $row = $this->selectSum('cantidad')
                    ->where('producto_id', $productoId)
                    ->first();

        return $row->cantidad ?? 0;
    }
}
