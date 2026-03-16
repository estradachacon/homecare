<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use App\Models\NotificationReadModel;

class Notifications extends BaseController
{
    public function ultimas()
    {
        $session = session();

        $userId = $session->get('id');
        $roleId = $session->get('role_id');

        $model = new NotificationModel();

        $notificaciones = $model->getNotificacionesUsuario($userId, $roleId);

        return $this->response->setJSON($notificaciones);
    }

    public function marcarLeida($notificationId)
    {
        $userId = session('id');

        $readModel = new NotificationReadModel();

        $readModel->insert([
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'read_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['success' => true]);
    }
    public function contar()
    {
        $session = session();

        $model = new NotificationModel();

        $total = $model->contarNoLeidas(
            $session->get('user_id'),
            $session->get('role_id')
        );

        return $this->response->setJSON([
            'total' => $total
        ]);
    }
}
