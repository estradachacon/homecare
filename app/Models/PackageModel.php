<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageModel extends Model
{
    protected $table = 'packages';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'vendedor',
        'cliente',
        'tipo_servicio',
        'destino_personalizado',
        'lugar_recolecta_paquete',
        'id_puntofijo',
        'fecha_ingreso',
        'fecha_entrega_personalizado',
        'fecha_entrega_puntofijo',
        'flete_total',
        'toggle_pago_parcial',
        'flete_pagado',
        'flete_pendiente',
        'nocobrar_pack_cancelado',
        'monto',
        'foto',
        'comentarios',
        'fragil',
        'fecha_pack_entregado',
        'estatus',
        'estatus2',
        'branch',
        'user_id'
    ];
    protected $updatedField = 'updated_at';
    protected $createdField = 'created_at';
    
    public function getFullPackage($id)
    {
        return $this->select('
            packages.*,
            puntos_fijos.nombre AS puntofijo_nombre
        ')
            ->join('puntos_fijos', 'puntos_fijos.id = packages.id_puntofijo', 'left')
            ->where('packages.id', $id)
            ->first();
    }
}
