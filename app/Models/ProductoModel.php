<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table            = 'productos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'codigo',
        'descripcion',
        'activo',
        'costo_promedio',
        'costo_promedio_actual',
        'tipo'
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

    public function buscarPorCodigo($codigo)
    {
        if (!$codigo) return null;

        return $this->where('codigo', $codigo)->first();
    }

    public function buscarPorDescripcion($descripcion)
    {
        if (!$descripcion) return null;

        return $this->where('descripcion', $descripcion)->first();
    }

    public function buscarOCrear($codigo, $descripcion)
    {
        $producto = null;

        if ($codigo) {
            $producto = $this->buscarPorCodigo($codigo);
        }

        if (!$producto) {
            $producto = $this->buscarPorDescripcion($descripcion);
        }

        if (!$producto) {

            $id = $this->insert([
                'codigo' => $codigo,
                'descripcion' => $descripcion
            ]);

            return $this->find($id);
        }

        return $producto;
    }

    public function conStock()
    {
        return $this->select('
        productos.*,
        COALESCE(mov.stock,0) AS stock
    ')
            ->join(
                '(SELECT producto_id, SUM(cantidad) as stock 
              FROM productos_movimientos 
              GROUP BY producto_id) mov',
                'mov.producto_id = productos.id',
                'left'
            );
    }
}
