<?php

namespace App\Models;

use CodeIgniter\Model;

class CompraHeadModel extends Model
{
    protected $table            = 'compras_head';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [

        // Identificación
        'numero_control',
        'codigo_generacion',
        'fecha_emision',
        'sello_recibido',
        'tipo_dte',

        // Relación
        'proveedor_id',

        // Totales
        'total_gravada',
        'sub_total',
        'total_iva',
        'monto_total_operacion',
        'total_pagar',

        // Condiciones
        'condicion_operacion',
        'plazo_credito',

        // Impuestos
        'iva_rete1',

        // Estado
        'saldo',
        'anulada',
        'anulada_por',
        'fecha_anulacion',

        // Relación NC
        'codigo_generacion_relacionado',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = false;

    /*
    |------------------------------------------------------------------
    | Métodos útiles
    |------------------------------------------------------------------
    */

    public function getByProveedor($proveedorId)
    {
        return $this->where('proveedor_id', $proveedorId)
            ->orderBy('fecha_emision', 'DESC')
            ->findAll();
    }

    public function getByCodigoGeneracion($codigo)
    {
        return $this->where('codigo_generacion', $codigo)->first();
    }

    public function existePorNumeroControl($numero)
    {
        return $this->where('numero_control', $numero)->first();
    }

    public function pendientesPago()
    {
        return $this->where('saldo >', 0)
            ->where('anulada', 0)
            ->findAll();
    }
}
