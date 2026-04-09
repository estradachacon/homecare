<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosComprasHeadModel extends Model
{
    protected $table      = 'pagos_compras_head';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'numero_pago',
        'proveedor_id',
        'numero_cuenta_bancaria',
        'forma_pago',
        'total',
        'fecha_pago',
        'observaciones',
        'anulado',
        'anulado_at',
        'anulado_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}