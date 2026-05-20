<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoNotaModel extends Model
{
    protected $table         = 'tipo_notas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'nombre',
        'activo',
    ];
}
