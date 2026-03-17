<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\NotificationModel;

class RevisarVencimientos extends BaseCommand
{
    protected $group       = 'Sistema';
    protected $name        = 'sistema:revisar-vencimientos';
    protected $description = 'Revisa vencimientos de quedans y genera notificaciones';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $tarea = $db->table('tareas_sistema')
            ->where('nombre', 'notificacion_vencimiento_quedans')
            ->get()
            ->getRow();

        if (!$tarea) {
            CLI::error('No existe la tarea en tareas_sistema');
            return;
        }

        // Si ya se ejecutó hoy
        if ($tarea->ultima_ejecucion == date('Y-m-d')) {
            CLI::write('La tarea ya fue ejecutada hoy.', 'yellow');
            return;
        }

        $result = $db->query("
            SELECT
            COALESCE(SUM(CASE WHEN fecha_pago < CURDATE() THEN 1 ELSE 0 END),0) AS vencidos,
            COALESCE(SUM(CASE WHEN fecha_pago = CURDATE() THEN 1 ELSE 0 END),0) AS hoy,
            COALESCE(SUM(CASE WHEN fecha_pago BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END),0) AS semana
            FROM quedans
            WHERE anulado = 0
             ")->getRow();

        if (!$result) {
            CLI::error('No se pudo calcular vencimientos');
            return;
        }

        $mensaje = "";

        if ($result->vencidos > 0) {
            $mensaje .= $result->vencidos . " quedans vencidos. ";
        }

        if ($result->hoy > 0) {
            $mensaje .= $result->hoy . " vencen hoy. ";
        }

        if ($result->semana > 0) {
            $mensaje .= $result->semana . " vencen esta semana.";
        }

        if ($mensaje != "") {

            $notifModel = new NotificationModel();

            $notifModel->insert([
                'titulo' => 'Vencimientos de quedans',
                'mensaje' => $mensaje,
                'link' => base_url('quedans'),
                'tipo' => 'warning',
                'permiso' => ''
            ]);

            CLI::write('Notificación creada.', 'green');
        }

        $db->table('tareas_sistema')
            ->where('nombre', 'notificacion_vencimiento_quedans')
            ->update([
                'ultima_ejecucion' => date('Y-m-d')
            ]);

        CLI::write('Tarea completada.', 'green');
    }
}
