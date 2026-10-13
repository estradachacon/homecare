<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionCierreModel extends Model
{
    protected $table         = 'consignaciones_cierres';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'consignacion_id', 'nueva_consignacion_id', 'observaciones', 'created_by',
    ];

    public function getPorConsignacion(int $consignacionId)
    {
        // Puede haber más de un cierre por NE (reapertura automática por NC/
        // anulación); sin ORDER BY, first() no garantiza traer el más reciente.
        return $this->where('consignacion_id', $consignacionId)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
