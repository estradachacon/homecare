<?php

namespace App\Models;

use CodeIgniter\Model;

class PagosComprasDetallesModel extends Model
{
    protected $table      = 'pagos_compras_detalles';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'pago_id',
        'compra_id',
        'monto',
        'observaciones',
        'anulado',
        'anulado_at',
        'anulado_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
