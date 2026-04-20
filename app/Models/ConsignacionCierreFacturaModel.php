<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionCierreFacturaModel extends Model
{
    protected $table         = 'consignaciones_cierres_facturas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'cierre_id', 'detalle_id', 'factura_id', 'created_at',
    ];

    public function getPorDetalle(int $detalleId): array
    {
        return $this->select('consignaciones_cierres_facturas.*, facturas_head.numero_control')
            ->join('facturas_head', 'facturas_head.id = consignaciones_cierres_facturas.factura_id', 'left')
            ->where('detalle_id', $detalleId)
            ->findAll();
    }
}
