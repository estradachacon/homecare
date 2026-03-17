<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionReglaModel extends Model
{
    protected $table = 'comisiones_reglas';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'tipo',
        'valor',
        'porcentaje'
    ];

    protected $useTimestamps = true;

    public function getOrdenadas()
    {
        return $this->orderBy('prioridad', 'ASC')->findAll();
    }
}