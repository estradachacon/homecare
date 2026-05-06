<?php

namespace App\Models;

use CodeIgniter\Model;

class ClasificacionModel extends Model
{
    protected $table         = 'clasificaciones';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['nombre', 'activo'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
