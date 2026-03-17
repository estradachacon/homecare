<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionConfigModel extends Model
{
    protected $table = 'comisiones_config';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'porcentaje_default'
    ];

    protected $useTimestamps = true;
}