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
        'total', 'observaciones', 'archivo_ruta', 'archivo_nombre', 'archivo_tipo',
        'archivo_tamano', 'estado', 'anulado_por', 'fecha_anulacion',
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
        $this->select(
                'recuperos.*,
                 clientes.nombre AS cliente_nombre,
                 (SELECT u.user_name FROM users u WHERE u.id = recuperos.usuario_id LIMIT 1) AS usuario_nombre,
                 (SELECT GROUP_CONCAT(DISTINCT s.seller ORDER BY s.seller SEPARATOR ", ")
                  FROM recuperos_detalle rd_v
                  INNER JOIN facturas_head fh_v ON fh_v.id = rd_v.factura_id
                  LEFT JOIN sellers s ON s.id = fh_v.vendedor_id
                  WHERE rd_v.recupero_id = recuperos.id) AS vendedor_nombre'
            )
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
        if (!empty($filtros['seller_id'])) {
            $sellerId = (int)$filtros['seller_id'];
            $this->where(
                "EXISTS (
                    SELECT 1
                    FROM recuperos_detalle rd_scope
                    INNER JOIN facturas_head fh_scope ON fh_scope.id = rd_scope.factura_id
                    WHERE rd_scope.recupero_id = recuperos.id
                      AND fh_scope.vendedor_id = {$sellerId}
                )",
                null,
                false
            );
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
        return $this->select(
                        'recuperos.*,
                         clientes.nombre AS cliente_nombre,
                         clientes.numero_documento,
                         (SELECT u.user_name FROM users u WHERE u.id = recuperos.usuario_id LIMIT 1) AS usuario_nombre,
                         (SELECT GROUP_CONCAT(DISTINCT s.seller ORDER BY s.seller SEPARATOR ", ")
                          FROM recuperos_detalle rd_v
                          INNER JOIN facturas_head fh_v ON fh_v.id = rd_v.factura_id
                          LEFT JOIN sellers s ON s.id = fh_v.vendedor_id
                          WHERE rd_v.recupero_id = recuperos.id) AS vendedor_nombre'
                    )
                    ->join('clientes', 'clientes.id = recuperos.cliente_id', 'left')
                    ->where('recuperos.id', $id)
                    ->first();
    }

    public function perteneceAVendedor(int $recuperoId, int $sellerId): bool
    {
        $db = \Config\Database::connect();

        return (bool)$db->table('recuperos_detalle rd')
            ->join('facturas_head fh', 'fh.id = rd.factura_id', 'inner')
            ->where('rd.recupero_id', $recuperoId)
            ->where('fh.vendedor_id', $sellerId)
            ->countAllResults();
    }
}
