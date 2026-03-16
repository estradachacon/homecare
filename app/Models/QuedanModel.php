<?php

namespace App\Models;

use CodeIgniter\Model;

class QuedanModel extends Model
{
    protected $table = 'quedans';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'numero_quedan',
        'cliente_id',
        'fecha_emision',
        'fecha_pago',
        'total_aplicado',
        'observaciones',
        'anulado',
        'anulado_por',
        'fecha_anulacion',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getQuedansConCliente()
    {
        return $this->select('
                quedans.*,
                clientes.nombre as cliente_nombre
            ')
            ->join('clientes', 'clientes.id = quedans.cliente_id', 'left')
            ->orderBy('quedans.id', 'DESC')
            ->findAll();
    }

    public function getQuedan($id)
    {
        return $this->select('
                quedans.*,
                clientes.nombre as cliente_nombre
            ')
            ->join('clientes', 'clientes.id = quedans.cliente_id', 'left')
            ->where('quedans.id', $id)
            ->first();
    }
}