<?php

namespace App\Models;

use CodeIgniter\Model;

class PermisoRolModel extends Model
{
    protected $table = 'permisos_rol';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_id', 'nombre_accion', 'habilitado', 'desde'];
    protected $returnType = 'array';

    public function getPermisosPorRol($role_id)
    {
        return $this->where('role_id', $role_id)->findAll();
    }
}