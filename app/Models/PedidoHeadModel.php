<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoHeadModel extends Model
{
    protected $table         = 'pedidos_head';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'numero', 'anio', 'secuencia',
        'cliente_id', 'vendedor_id',
        'tipo_documento', 'tipo_pago', 'dias_credito',
        'notas', 'subtotal', 'iva', 'total',
        'estado', 'factura_id',
        'anulada', 'anulada_por', 'fecha_anulacion',
        'created_by',
    ];

    public function listar(array $filtros = [])
    {
        $this->select('
                pedidos_head.*,
                clientes.nombre AS cliente_nombre,
                sellers.seller  AS vendedor_nombre
            ')
            ->join('clientes', 'clientes.id = pedidos_head.cliente_id', 'left')
            ->join('sellers',  'sellers.id  = pedidos_head.vendedor_id', 'left');

        if (!empty($filtros['vendedor_id'])) {
            $this->where('pedidos_head.vendedor_id', $filtros['vendedor_id']);
        }
        if (!empty($filtros['estado'])) {
            $this->where('pedidos_head.estado', $filtros['estado']);
        }
        if (!empty($filtros['tipo_documento'])) {
            $this->where('pedidos_head.tipo_documento', $filtros['tipo_documento']);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $this->where('DATE(pedidos_head.created_at) >=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $this->where('DATE(pedidos_head.created_at) <=', $filtros['fecha_fin']);
        }
        if (!empty($filtros['q'])) {
            $this->groupStart()
                ->like('pedidos_head.numero', $filtros['q'])
                ->orLike('clientes.nombre',   $filtros['q'])
                ->groupEnd();
        }

        return $this->orderBy('pedidos_head.id', 'DESC');
    }

    public function getConRelaciones(int $id)
    {
        return $this->select('
                pedidos_head.*,
                clientes.nombre            AS cliente_nombre,
                clientes.tipo_documento    AS cliente_tipo_doc,
                clientes.numero_documento  AS cliente_num_doc,
                clientes.nrc               AS cliente_nrc,
                clientes.telefono          AS cliente_telefono,
                clientes.correo            AS cliente_correo,
                clientes.direccion         AS cliente_direccion,
                sellers.seller             AS vendedor_nombre,
                fh.numero_control          AS factura_numero
            ')
            ->join('clientes', 'clientes.id = pedidos_head.cliente_id', 'left')
            ->join('sellers',  'sellers.id  = pedidos_head.vendedor_id', 'left')
            ->join('facturas_head fh', 'fh.id = pedidos_head.factura_id', 'left')
            ->where('pedidos_head.id', $id)
            ->first();
    }

    public function siguienteNumero(): string
    {
        $anio = (int) date('Y');
        $db   = \Config\Database::connect();

        $row = $db->table('pedidos_head')
            ->selectMax('secuencia')
            ->where('anio', $anio)
            ->get()->getRow();

        $seq  = (int)($row->secuencia ?? 0) + 1;
        $num  = 'NP-' . $anio . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);

        return $num;
    }

    public function siguienteSecuencia(): int
    {
        $anio = (int) date('Y');
        $db   = \Config\Database::connect();

        $row = $db->table('pedidos_head')
            ->selectMax('secuencia')
            ->where('anio', $anio)
            ->get()->getRow();

        return (int)($row->secuencia ?? 0) + 1;
    }
}
