<?php

namespace App\Models;

use CodeIgniter\Model;

class BitacoraSistemaModel extends Model
{
    protected $table = 'bitacora_sistema';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'accion',
        'modulo',
        'descripcion',
        'referencia_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    public $useTimestamps = false; // Aqui ya tengo created_at con CURRENT_TIMESTAMP
}
