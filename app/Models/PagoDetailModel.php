<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoDetailModel extends Model
{
    protected $table      = 'pagos_details';
    protected $primaryKey = 'id';

    protected $returnType = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'pago_id',
        'factura_id',
        'monto',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

}