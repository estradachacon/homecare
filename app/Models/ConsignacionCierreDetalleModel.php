<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionCierreDetalleModel extends Model
{
    protected $table         = 'consignaciones_cierres_detalles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'cierre_id', 'detalle_id', 'producto_id',
        'cantidad_facturada', 'cantidad_devuelta', 'cantidad_stock_vendedor',
        'doc_devolucion', 'foto_devolucion', 'comentario_devolucion',
    ];

    public function getPorCierre(int $cierreId): array
    {
        return $this->select('consignaciones_cierres_detalles.*, productos.descripcion as producto_nombre')
            ->join('productos', 'productos.id = consignaciones_cierres_detalles.producto_id', 'left')
            ->where('cierre_id', $cierreId)
            ->findAll();
    }
}
