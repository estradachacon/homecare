<?php

namespace App\Models;

use CodeIgniter\Model;

class EmisorModel extends Model
{
    protected $table            = 'emisor';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nit',
        'nrc',
        'nombre',
        'cod_actividad',
        'desc_actividad',
        'nombre_comercial',
        'tipo_establecimiento',
        'telefono',
        'correo',
        'cod_estable_mh',
        'cod_estable',
        'cod_punto_venta_mh',
        'cod_punto_venta',
        'departamento',
        'municipio',
        'complemento',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = false;
}