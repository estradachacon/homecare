<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingDetailsModel extends Model
{
    protected $table      = 'tracking_details';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

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
        return $this->select('tracking_details.*, packages.cliente, packages.tipo_servicio, packages.monto')
                    ->join('packages', 'packages.id = tracking_details.package_id', 'left')
                    ->where('tracking_header_id', $trackingHeaderId)
                    ->orderBy('tracking_details.id', 'ASC')
                    ->findAll();
    }
}
