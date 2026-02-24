<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosHeadModel extends Model
{
    protected $table = 'pagos_head';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'numero_recupero',
        'cliente_id',
        'numero_cuenta_bancaria',
        'forma_pago',
        'total',
        'fecha_pago',
        'observaciones',
        'anulado',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    protected $returnType = 'object';
}