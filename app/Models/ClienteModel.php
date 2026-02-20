<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'tipo_documento',
        'numero_documento',
        'nrc',
        'nombre',
        'telefono',
        'correo',
        'departamento',
        'municipio',
        'direccion',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Métodos útiles
    |--------------------------------------------------------------------------
    */

    public function buscarPorDocumento($tipo, $numero)
    {
        return $this->where('tipo_documento', $tipo)
                    ->where('numero_documento', $numero)
                    ->first();
    }

    public function buscarPorNRC($nrc)
    {
        return $this->where('nrc', $nrc)->first();
    }
}