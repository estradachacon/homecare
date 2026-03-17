<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionVendedorModel extends Model
{
    protected $table = 'comisiones_vendedor';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'vendedor_id',
        'porcentaje'
    ];

    protected $useTimestamps = true;

    public function getByVendedor($vendedorId)
    {
        return $this->where('vendedor_id', $vendedorId)->first();
    }
}