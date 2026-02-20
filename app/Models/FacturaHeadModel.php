<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaHeadModel extends Model
{
    protected $table            = 'facturas_head';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        // Identificación
        'ambiente',
        'tipo_dte',
        'numero_control',
        'codigo_generacion',
        'fecha_emision',
        'hora_emision',
        'tipo_moneda',
        'sello_recibido',

        // Relaciones
        'receptor_id',
        'vendedor_id',

        // Resumen
        'total_gravada',
        'sub_total',
        'total_iva',
        'monto_total_operacion',
        'total_pagar',
        'condicion_operacion',

        // Firma
        'firma_electronica',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = false;
}