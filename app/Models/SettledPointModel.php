<?php

namespace App\Models;

use CodeIgniter\Model;

class SettledPointModel extends Model
{
    protected $table            = 'settled_points';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = ['point_name', 'ruta_id', 'days_configuration', 'hora_inicio', 'hora_fin'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
