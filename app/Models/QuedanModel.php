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
            clientes.nombre as cliente_nombre,
            u.user_name as usuario_anulo
        ')
            ->join('clientes', 'clientes.id = quedans.cliente_id', 'left')
            ->join('users u', 'u.id = quedans.anulado_por', 'left')
            ->where('quedans.id', $id)
            ->first();
    }

    public function getVencimientos()
    {
        return $this->select("
        SUM(CASE WHEN fecha_pago = CURDATE() THEN 1 ELSE 0 END) AS vencen_hoy,
        SUM(CASE WHEN fecha_pago BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS vencen_semana
    ")
            ->where('anulado', 0)
            ->first();
    }

    public function getReporteQuedans($desde = null, $hasta = null, $clienteId = null)
    {
        $builder = $this->select('
        quedans.*,
        clientes.nombre as cliente_nombre
    ')
            ->join('clientes', 'clientes.id = quedans.cliente_id', 'left');

        if ($desde) {
            $builder->where('DATE(quedans.fecha_emision) >=', $desde);
        }

        if ($hasta) {
            $builder->where('DATE(quedans.fecha_emision) <=', $hasta);
        }

        if ($clienteId) {
            $builder->where('quedans.cliente_id', $clienteId);
        }

        return $builder->orderBy('quedans.fecha_emision', 'DESC')->findAll();
    }
    public function getFacturasPorQuedan($quedanId)
    {
        return $this->db->table('quedan_facturas qf')
            ->select('
            qf.monto_aplicado,
            fh.numero_control,
            fh.fecha_emision,
            fh.total_pagar,
            fh.saldo,
            fh.tipo_dte
        ')
            ->join('facturas_head fh', 'fh.id = qf.factura_id')
            ->where('qf.quedan_id', $quedanId)
            ->get()
            ->getResult();
    }
}
