<?php

namespace App\Models;

use CodeIgniter\Model;

class ContTiposPartidaModel extends Model
{
    protected $table         = 'cont_tipos_partida';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = ['nombre', 'descripcion', 'activo'];

    public function getActivos()
    {
        return $this->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();
    }

    public function searchAjax(string $q = ''): array
    {
        $rows = $this->where('activo', 1)
                     ->like('nombre', $q)
                     ->orderBy('nombre', 'ASC')
                     ->findAll(30);

        return array_map(fn($r) => ['id' => $r->id, 'text' => $r->nombre], $rows);
    }
}
