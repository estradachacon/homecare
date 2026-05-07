<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoDetalleModel extends Model
{
    protected $table         = 'pedidos_detalles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'pedido_id', 'producto_id',
        'cantidad', 'precio_unitario', 'precio_minimo', 'subtotal',
    ];

    public function getPorPedido(int $pedidoId): array
    {
        return $this->select('
                pedidos_detalles.*,
                productos.descripcion AS producto_nombre,
                productos.codigo      AS producto_codigo
            ')
            ->join('productos', 'productos.id = pedidos_detalles.producto_id', 'left')
            ->where('pedidos_detalles.pedido_id', $pedidoId)
            ->orderBy('pedidos_detalles.id', 'ASC')
            ->findAll();
    }
}
