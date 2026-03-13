<?php

namespace App\Models;

use CodeIgniter\Model;

class ProveedorModel extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'nombre',
        'telefono',
        'email',
        'direccion'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
