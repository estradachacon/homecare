<?php

namespace App\Models;

use CodeIgniter\Model;

class RecuperosDetalleModel extends Model
{
    protected $table         = 'recuperos_detalle';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'recupero_id', 'factura_id', 'monto_aplicado',
    ];

    public function getByRecupero(int $recuperoId): array
    {
        return $this->select(
                'recuperos_detalle.*,
                 facturas_head.numero_control,
                 facturas_head.tipo_dte,
                 facturas_head.fecha_emision,
                 facturas_head.total_pagar,
                 facturas_head.saldo AS saldo_actual'
            )
            ->join('facturas_head', 'facturas_head.id = recuperos_detalle.factura_id', 'left')
            ->where('recupero_id', $recuperoId)
            ->orderBy('recuperos_detalle.id', 'ASC')
            ->findAll();
    }
}
