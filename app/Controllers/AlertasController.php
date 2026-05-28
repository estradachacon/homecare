<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AlertasController extends BaseController
{
    public function conteos()
    {
        $db     = \Config\Database::connect();
        $result = [];

        // NPs pendientes de facturar
        if (tienePermiso('ver_alertas_np_pendientes')) {
            $count = $db->table('pedidos_head')
                ->where('estado', 'pendiente')
                ->countAllResults();

            $result[] = [
                'tipo'  => 'np_pendientes',
                'label' => 'NP pendientes de facturar',
                'count' => (int)$count,
                'link'  => base_url('pedidos?estado=pendiente'),
                'icon'  => 'fa-file-invoice-dollar',
                'color' => 'warning',
            ];
        }

        // NEs sin autorización de lotes
        if (tienePermiso('ver_alertas_ne_sin_autorizar')) {
            $count = $db->table('consignaciones_head')
                ->where('estado', 'abierta')
                ->where('lotes_autorizados_por', null)
                ->countAllResults();

            $result[] = [
                'tipo'  => 'ne_sin_autorizar',
                'label' => 'NE sin autorización de lotes',
                'count' => (int)$count,
                'link'  => base_url('consignaciones?lote_estado=sin_autorizar'),
                'icon'  => 'fa-stamp',
                'color' => 'warning',
            ];
        }

        // NEs autorizadas con lotes incompletos (sin lotes o parcialmente asignados)
        if (tienePermiso('ver_alertas_ne_sin_lotes')) {
            $sql = "SELECT COUNT(DISTINCT ch.id) AS total
                    FROM consignaciones_head ch
                    WHERE ch.estado = 'abierta'
                      AND ch.lotes_autorizados_por IS NOT NULL
                      AND EXISTS (
                          SELECT 1
                          FROM consignaciones_detalles cd
                          LEFT JOIN (
                              SELECT detalle_id, SUM(cantidad) AS total_asignado
                              FROM consignacion_detalle_lotes
                              GROUP BY detalle_id
                          ) ls ON ls.detalle_id = cd.id
                          WHERE cd.consignacion_id = ch.id
                            AND ABS(COALESCE(ls.total_asignado, 0) - cd.cantidad) > 0.001
                      )";

            $count = (int)($db->query($sql)->getRow()->total ?? 0);

            $result[] = [
                'tipo'  => 'ne_sin_lotes',
                'label' => 'NE autorizadas con lotes incompletos',
                'count' => $count,
                'link'  => base_url('consignaciones?lote_estado=pendiente_lotes'),
                'icon'  => 'fa-boxes-stacked',
                'color' => 'danger',
            ];
        }

        // NEs pendientes de aprobación con lotes 100% completos
        if (tienePermiso('ver_alertas_ne_con_lotes_sin_aprobar')) {
            $sql = "SELECT COUNT(DISTINCT ch.id) AS total
                    FROM consignaciones_head ch
                    WHERE ch.estado = 'abierta'
                      AND ch.anulada = 0
                      AND (ch.aprobacion_estado IS NULL OR ch.aprobacion_estado = 'pendiente')
                      AND EXISTS (
                          SELECT 1 FROM consignaciones_detalles
                          WHERE consignacion_id = ch.id
                      )
                      AND NOT EXISTS (
                          SELECT 1
                          FROM consignaciones_detalles cd
                          LEFT JOIN (
                              SELECT detalle_id, SUM(cantidad) AS total_asignado
                              FROM consignacion_detalle_lotes
                              GROUP BY detalle_id
                          ) ls ON ls.detalle_id = cd.id
                          WHERE cd.consignacion_id = ch.id
                            AND ABS(COALESCE(ls.total_asignado, 0) - cd.cantidad) > 0.001
                      )";

            $count = (int)($db->query($sql)->getRow()->total ?? 0);

            $result[] = [
                'tipo'  => 'ne_con_lotes_sin_aprobar',
                'label' => 'NE con lotes completos sin aprobar',
                'count' => $count,
                'link'  => base_url('consignaciones?estado=abierta&aprobacion=pendiente'),
                'icon'  => 'fa-circle-check',
                'color' => 'warning',
            ];
        }

        $total = array_sum(array_column($result, 'count'));

        return $this->response->setJSON([
            'alertas' => $result,
            'total'   => $total,
        ]);
    }
}
