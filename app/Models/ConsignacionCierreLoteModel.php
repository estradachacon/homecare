<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionCierreLoteModel extends Model
{
    protected $table         = 'consignaciones_cierres_lotes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'cierre_id',
        'cierre_detalle_id',
        'detalle_id',
        'producto_id',
        'lote_id',
        'tipo',
        'cantidad',
        'created_at',
    ];

    public function getPorCierre(int $cierreId): array
    {
        return $this->select('
                consignaciones_cierres_lotes.*,
                consignacion_lotes.numero_lote,
                consignacion_lotes.fecha_vencimiento,
                consignacion_lotes.manufactura
            ')
            ->join('consignacion_lotes', 'consignacion_lotes.id = consignaciones_cierres_lotes.lote_id', 'left')
            ->where('cierre_id', $cierreId)
            ->orderBy('detalle_id', 'ASC')
            ->orderBy('tipo', 'ASC')
            ->findAll();
    }

    public function registrarDistribucion(
        int $cierreId,
        int $cierreDetalleId,
        int $detalleId,
        int $productoId,
        string $tipo,
        array $lotes
    ): void {
        foreach ($lotes as $lote) {
            $cantidad = (float)($lote['cantidad'] ?? 0);
            if (empty($lote['lote_id']) || $cantidad <= 0) {
                continue;
            }

            $this->insert([
                'cierre_id'         => $cierreId,
                'cierre_detalle_id' => $cierreDetalleId,
                'detalle_id'        => $detalleId,
                'producto_id'       => $productoId,
                'lote_id'           => (int)$lote['lote_id'],
                'tipo'              => $tipo,
                'cantidad'          => $cantidad,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
