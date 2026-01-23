<?php

namespace App\Models;

use CodeIgniter\Model;

class MunicipioModel extends Model
{
    protected $table         = 'municipios';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['departamento_id', 'nombre'];
    protected $returnType    = 'array';

    /**
     * Municipios por departamento (cascada)
     */
    public function getByDepartamento(int $departamentoId)
    {
        return $this->where('departamento_id', $departamentoId)
                    ->orderBy('nombre')
                    ->findAll();
    }
}