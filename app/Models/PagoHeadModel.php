<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoHeadModel extends Model
{
    protected $table      = 'pagos_head';
    protected $primaryKey = 'id';

    protected $returnType = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'numero_recupero',
        'cliente_id',
        'numero_cuenta_bancaria',
        'forma_pago',
        'total',
        'fecha_pago',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

}