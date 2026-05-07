<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoLogModel extends Model
{
    protected $table         = 'pedidos_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'pedido_id', 'user_id', 'user_nombre',
        'accion', 'detalle', 'created_at',
    ];
}
