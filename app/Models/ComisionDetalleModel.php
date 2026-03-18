<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionDetalleModel extends Model
{
    protected $table      = 'comision_detalles';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'comision_id',
        'factura_id',
        'producto_id',
        'cantidad',
        'precio_sin_iva',
        'total_linea',
        'comision_aplicada',
        'monto_comision',
        'tipo_venta',
        'origen_comision'
    ];

    protected $useTimestamps = false;
}