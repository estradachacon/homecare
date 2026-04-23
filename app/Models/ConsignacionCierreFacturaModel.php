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
        'cierre_id',
        'detalle_id',
        'factura_id',
        'created_at',
    ];

    public function getPorDetalle(int $detalleId): array
    {
        return $this->select('consignaciones_cierres_facturas.*, facturas_head.*')
            ->join('facturas_head', 'facturas_head.id = consignaciones_cierres_facturas.factura_id', 'left')
            ->where('detalle_id', $detalleId)
            ->findAll();
    }

    public function getPorDetalleYCierre(int $detalleId, int $cierreId): array
    {
        return $this->select([
            'facturas_head.numero_control as factura_numero',
            'consignaciones_cierres_facturas.factura_id'
        ])
            ->join(
                'consignaciones_cierres_detalles ccd',
                'ccd.detalle_id = consignaciones_cierres_facturas.detalle_id 
             AND ccd.cierre_id = consignaciones_cierres_facturas.cierre_id'
            )
            ->join(
                'facturas_head',
                'facturas_head.id = consignaciones_cierres_facturas.factura_id',
                'left'
            )
            ->where([
                'ccd.detalle_id' => $detalleId,
                'ccd.cierre_id'  => $cierreId,
            ])
            ->findAll();
    }
}
