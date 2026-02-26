<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosDetailsModel extends Model
{
    protected $table = 'pagos_details';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'pago_id',
        'factura_id',
        'monto',
        'observaciones',
        'anulado',
        'anulado_at',
        'anulado_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    protected $returnType = 'object';
}
