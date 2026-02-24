<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoVentaModel extends Model
{
    protected $table = 'tipo_venta';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'nombre_tipo_venta',
        'created_at',
        'updated_at'
    ];


public function searchTipoVentas($term)
{
    if (!$term || trim($term) === '') {
        return []; // 👈 Select2 suele pedir esto antes de escribir
    }

    return $this->like('nombre_tipo_venta', $term)
                ->select('id, nombre_tipo_venta')
                ->findAll(20);
}

}
