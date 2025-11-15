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
        'retiro_paquete',
        'destino',
        'id_puntofijo',
        'direccion',
        'fecha_ingreso',
        'fecha_entrega',
        'flete_total',
        'flete_pagado',
        'flete_pendiente',
        'monto',
        'foto',
        'comentarios',
        'fragil',
        'estatus',
        'user_id',
        'created_at',
        'updated_at'
    ];
}
