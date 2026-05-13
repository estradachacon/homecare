<?php

namespace App\Models;

use CodeIgniter\Model;

class ContRemesasDetalleModel extends Model
{
    protected $table         = 'cont_remesas_detalle';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = ['remesa_id', 'asiento_id', 'monto'];

    public function getByRemesa(int $remesaId): array
    {
        return \Config\Database::connect()->query(
            "SELECT rd.id, rd.monto,
                    ah.id AS asiento_id, ah.numero_asiento, ah.fecha, ah.descripcion,
                    ah.total_debe, ah.total_haber, ah.estado AS asiento_estado,
                    ah.referencia, ah.documento_tipo, ah.documento_id,
                    tp.nombre AS tipo_partida_nombre,
                    u.user_name AS usuario_nombre,
                    p.anio, p.mes
             FROM cont_remesas_detalle rd
             JOIN cont_asientos_head ah ON ah.id = rd.asiento_id
             LEFT JOIN cont_tipos_partida tp ON tp.id = ah.tipo_partida_id
             LEFT JOIN users u ON u.id = ah.usuario_id
             LEFT JOIN cont_periodos p ON p.id = ah.periodo_id
             WHERE rd.remesa_id = ?
             ORDER BY ah.fecha ASC, ah.numero_asiento ASC",
            [$remesaId]
        )->getResult();
    }
}
