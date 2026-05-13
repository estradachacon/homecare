<?php

namespace App\Models;

use CodeIgniter\Model;

class RecuperosModel extends Model
{
    protected $table         = 'recuperos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'numero_recupero', 'cliente_id', 'fecha', 'forma_cobro', 'referencia',
        'total', 'observaciones', 'estado', 'anulado_por', 'fecha_anulacion',
        'motivo_anulacion', 'usuario_id', 'pago_id',
    ];

    public function getSiguienteNumero(): string
    {
        $db  = \Config\Database::connect();
        $row = $db->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(numero_recupero, 5) AS UNSIGNED)), 0) + 1 AS sig FROM recuperos"
        )->getRow();
        return 'REC-' . str_pad((int)($row->sig ?? 1), 5, '0', STR_PAD_LEFT);
    }

    public function getListado(array $filtros = [], int $perPage = 20): array
    {
        $this->select('recuperos.*, clientes.nombre AS cliente_nombre')
             ->join('clientes', 'clientes.id = recuperos.cliente_id', 'left')
             ->orderBy('recuperos.id', 'DESC');

        if (!empty($filtros['cliente_id'])) {
            $this->where('recuperos.cliente_id', $filtros['cliente_id']);
        }
        if (!empty($filtros['estado'])) {
            $this->where('recuperos.estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $this->where('recuperos.fecha >=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $this->where('recuperos.fecha <=', $filtros['fecha_hasta']);
        }

        return $this->paginate($perPage) ?? [];
    }

    public function getActivosByCliente(int $clienteId): array
    {
        return $this->select('id, numero_recupero, fecha, forma_cobro, referencia, total')
                    ->where('cliente_id', $clienteId)
                    ->where('estado', 'ACTIVO')
                    ->orderBy('fecha', 'DESC')
                    ->findAll();
    }

    public function getConCliente(int $id): ?object
    {
        return $this->select('recuperos.*, clientes.nombre AS cliente_nombre, clientes.numero_documento')
                    ->join('clientes', 'clientes.id = recuperos.cliente_id', 'left')
                    ->where('recuperos.id', $id)
                    ->first();
    }
}
