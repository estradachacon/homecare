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
}