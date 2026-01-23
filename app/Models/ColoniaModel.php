<?php

namespace App\Models;

use CodeIgniter\Model;

class ColoniaModel extends Model
{
    protected $table         = 'colonias';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['municipio_id', 'nombre'];
    protected $returnType    = 'array';

    public function searchForSelect2(string $term, int $limit = 20): array
    {
        return $this->db->table('colonias c')
            ->select("
                c.id,
                CONCAT(
                    c.nombre,
                    ' – ',
                    m.nombre,
                    ', ',
                    d.nombre
                ) AS text
            ")
            ->join('municipios m', 'm.id = c.municipio_id')
            ->join('departamentos d', 'd.id = m.departamento_id')
            ->groupStart()
                ->like('c.nombre', $term)
                ->orLike('m.nombre', $term)
                ->orLike('d.nombre', $term)
            ->groupEnd()
            ->orderBy('d.nombre', 'ASC')
            ->orderBy('m.nombre', 'ASC')
            ->orderBy('c.nombre', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}