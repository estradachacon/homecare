<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingHeaderModel extends Model
{
    protected $table      = 'tracking_header';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true; // habilita created_at y updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'route_id',
        'date',
        'status'
    ];

    /**
     * Obtiene el header junto con el motorista y la ruta
     */
    public function getHeaderWithRelations($id = null)
    {
        $builder = $this->select('tracking_header.*, users.user_name AS motorista_name, routes.route_name AS route_name')
                        ->join('users', 'users.id = tracking_header.user_id', 'left')
                        ->join('routes', 'routes.id = tracking_header.route_id', 'left');

        if ($id) {
            return $builder->where('tracking_header.id', $id)->first();
        }

        return $builder->orderBy('tracking_header.date', 'DESC')->findAll();
    }
}
