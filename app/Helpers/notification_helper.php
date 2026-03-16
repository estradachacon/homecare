<?php

use App\Models\NotificationModel;

if (!function_exists('crear_notificacion')) {

    function crear_notificacion(
        string $titulo,
        string $mensaje,
        string $permiso,
        string $link,
        string $tipo = 'info'
    ) {

        $model = new NotificationModel();

        $model->insert([
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'permiso' => $permiso,
            'link' => $link,
            'tipo' => $tipo,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
