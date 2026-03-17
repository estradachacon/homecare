<?php

namespace App\Models;

use CodeIgniter\Model;

class ComisionModel extends Model
{
    protected $table      = 'comisiones';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'vendedor_id',
        'fecha_inicio',
        'fecha_fin',
        'total_ventas',
        'total_comision',
        'porcentaje_promedio',
        'estado'
    ];

    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}