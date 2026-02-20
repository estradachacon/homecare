<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturaJsonModel extends Model
{
    protected $table            = 'facturas_json';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'factura_id',
        'json_original',
    ];

    protected $useTimestamps = false; // Solo tienes created_at manual

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Métodos útiles
    |--------------------------------------------------------------------------
    */

    public function getByFactura($facturaId)
    {
        return $this->where('factura_id', $facturaId)->first();
    }

    public function guardarJson($facturaId, $json)
    {
        return $this->insert([
            'factura_id'    => $facturaId,
            'json_original' => $json,
        ]);
    }
}