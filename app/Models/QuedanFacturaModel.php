<?php

namespace App\Models;

use CodeIgniter\Model;

class QuedanFacturaModel extends Model
{
    protected $table = 'quedan_facturas';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'quedan_id',
        'factura_id',
        'monto_aplicado',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    public function getFacturasPorQuedan($quedanId)
    {
        return $this->select('
            quedan_facturas.*,
            facturas_head.numero_control,
            facturas_head.fecha_emision,
            facturas_head.total_pagar,
            facturas_head.saldo,
            facturas_head.id as factura_id,
            facturas_head.anulada
        ')
            ->join('facturas_head', 'facturas_head.id = quedan_facturas.factura_id')
            ->where('quedan_facturas.quedan_id', $quedanId)
            ->orderBy('facturas_head.fecha_emision', 'ASC')
            ->findAll();
    }

    public function getQuedanDeFactura($facturaId)
    {
        return $this->select('
                quedan_facturas.*,
                quedans.numero_quedan,
                quedans.fecha_pago
            ')
            ->join('quedans', 'quedans.id = quedan_facturas.quedan_id')
            ->where('quedan_facturas.factura_id', $facturaId)
            ->first();
    }
}
