<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionPrecioModel extends Model
{
    protected $table         = 'consignaciones_precios';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'vendedor_id', 'cliente_id', 'producto_id', 'precio', 'activo',
    ];

    public function listarConNombres(array $filtros = [])
    {
        $this->select('
                consignaciones_precios.*,
                sellers.seller  AS vendedor_nombre,
                clientes.nombre AS cliente_nombre,
                productos.descripcion AS producto_nombre,
                productos.codigo AS producto_codigo
            ')
            ->join('sellers',   'sellers.id   = consignaciones_precios.vendedor_id', 'left')
            ->join('clientes',  'clientes.id  = consignaciones_precios.cliente_id',  'left')
            ->join('productos', 'productos.id = consignaciones_precios.producto_id', 'left');

        if (!empty($filtros['vendedor_id'])) {
            $this->where('consignaciones_precios.vendedor_id', $filtros['vendedor_id']);
        }

        return $this->orderBy('sellers.seller', 'ASC')
            ->orderBy('productos.descripcion', 'ASC');
    }

    public function getPrecioRecomendado(int $vendedorId, int $productoId, ?int $clienteId = null): ?float
    {
        if ($clienteId) {
            $precio = $this->where('vendedor_id', $vendedorId)
                ->where('producto_id', $productoId)
                ->where('cliente_id', $clienteId)
                ->where('activo', 1)
                ->first();

            if ($precio) return (float) $precio->precio;
        }

        $precio = $this->where('vendedor_id', $vendedorId)
            ->where('producto_id', $productoId)
            ->where('cliente_id IS NULL')
            ->where('activo', 1)
            ->first();

        return $precio ? (float) $precio->precio : null;
    }
}
