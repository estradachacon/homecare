<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionDetalleLoteModel extends Model
{
    protected $table         = 'consignacion_detalle_lotes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'detalle_id', 'lote_id', 'cantidad', 'created_at',
    ];

    public function getPorDetalle(int $detalleId): array
    {
        return $this->select('consignacion_detalle_lotes.*, consignacion_lotes.numero_lote, consignacion_lotes.fecha_vencimiento, consignacion_lotes.manufactura')
            ->join('consignacion_lotes', 'consignacion_lotes.id = consignacion_detalle_lotes.lote_id', 'left')
            ->where('detalle_id', $detalleId)
            ->findAll();
    }

    public function reemplazarPorDetalle(int $detalleId, array $lotes): void
    {
        $this->where('detalle_id', $detalleId)->delete();
        foreach ($lotes as $l) {
            if (empty($l['lote_id']) || empty($l['cantidad'])) continue;
            $this->insert([
                'detalle_id' => $detalleId,
                'lote_id'    => (int)$l['lote_id'],
                'cantidad'   => (float)$l['cantidad'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
