<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingDetailsModel extends Model
{
    protected $table = 'tracking_details';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $returnType = 'object';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'tracking_header_id',
        'package_id',
        'status',
        'delivered_at',
        'note'
    ];

    /**
     * Obtiene los detalles de un header específico junto con info del paquete
     */
    public function getDetailsWithPackages($trackingHeaderId)
    {
        return $this->select('
        tracking_details.*,
        packages.cliente,
        packages.tipo_servicio,
        packages.monto,
        packages.destino_personalizado,
        packages.toggle_pago_parcial,
        packages.flete_total,
        packages.flete_pagado,
        packages.lugar_recolecta_paquete,
        packages.id_puntofijo,
        packages.pago_cuenta,
        packages.estatus AS package_status,
        packages.flete_rendido AS flete_rendido,
        settled_points.point_name AS puntofijo_nombre,
        sellers.seller AS vendedor,
        accounts.name AS cuenta_nombre
    ')
            ->join('packages', 'packages.id = tracking_details.package_id', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')

            // 🔥 ESTE ES EL JOIN NUEVO
            ->join('accounts', 'accounts.id = packages.pago_cuenta', 'left')

            ->where('tracking_details.tracking_header_id', $trackingHeaderId)
            ->orderBy('tracking_details.id', 'ASC')
            ->findAll();
    }
}
