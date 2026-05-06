<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionLogModel extends Model
{
    protected $table            = 'consignaciones_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'consignacion_id',
        'user_id',
        'user_nombre',
        'accion',
        'detalle',
        'created_at',
    ];
}
