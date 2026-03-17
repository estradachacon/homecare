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

        $notificaciones = $model->getNoLeidasVisibles($userId, $roleId, 5);

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

        $userId = $session->get('id');
        $roleId = $session->get('role_id');

        $model = new NotificationModel();

        $total = count(
            $model->getNoLeidasVisibles($userId, $roleId, 1000)
        );

        return $this->response->setJSON([
            'total' => $total
        ]);
    }

    public function index()
    {
        $session = session();

        $userId = $session->get('id');
        $roleId = $session->get('role_id');

        $notifModel = new NotificationModel();

        $perPage = 10;

        $notifications = $notifModel->getNotificacionesVisibles($userId, $roleId, null, null, $perPage);

        return view('notifications/index', [
            'notifications' => $notifications,
            'pager' => $notifModel->pager,
            'perPage' => $perPage
        ]);
    }

    public function search()
    {
        $session = session();

        $userId = $session->get('id');
        $roleId = $session->get('role_id');

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $perPage = $this->request->getGet('perPage') ?? 10;

        $notifModel = new NotificationModel();

        $notifications = $notifModel->getNotificacionesVisibles(
            $userId,
            $roleId,
            $from,
            $to,
            $perPage
        );

        return view('notifications/_table', [
            'notifications' => $notifications,
            'pager' => $notifModel->pager
        ]);
    }
}
