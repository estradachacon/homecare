<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionMargenModel extends Model
{
    protected $table = 'comisiones_margen';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'margen_min',
        'margen_max',
        'porcentaje'
    ];

    protected $useTimestamps = true;

    public function getPorMargen($margen)
    {
        return $this->where('margen_min <=', $margen)
                    ->groupStart()
                        ->where('margen_max >=', $margen)
                        ->orWhere('margen_max IS NULL')
                    ->groupEnd()
                    ->first();
    }
}