<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\PermisoRolModel;
use App\Models\NotificationReadModel;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'titulo',
        'mensaje',
        'link',
        'tipo',
        'permiso'
    ];

    protected $useTimestamps = true;

    /*
    |--------------------------------------------------------------------------
    | Obtener notificaciones del usuario
    |--------------------------------------------------------------------------
    */
public function getNotificacionesUsuario($userId, $roleId, $limit = 10)
{
    $permisoModel = new PermisoRolModel();

    $permisos = $permisoModel
        ->where('role_id', $roleId)
        ->where('habilitado', 1)
        ->findAll();

    $acciones = array_column($permisos, 'nombre_accion');

    if (empty($acciones)) {
        return [];
    }

    return $this->select('*')
        ->whereIn('permiso', $acciones)
        ->where("NOT EXISTS (
            SELECT 1
            FROM notifications_read nr
            WHERE nr.notification_id = notifications.id
            AND nr.user_id = {$userId}
        )")
        ->orderBy('created_at', 'DESC')
        ->findAll($limit);
}

    /*
    |--------------------------------------------------------------------------
    | Marcar notificación como leída
    |--------------------------------------------------------------------------
    */
    public function marcarLeida($notificationId, $userId)
    {
        $readModel = new NotificationReadModel();

        return $readModel->insert([
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Contar notificaciones no leídas
    |--------------------------------------------------------------------------
    */
    public function contarNoLeidas($userId, $roleId)
    {
        $permisoModel = new PermisoRolModel();

        $permisos = $permisoModel
            ->where('role_id', $roleId)
            ->where('habilitado', 1)
            ->findAll();

        $acciones = array_column($permisos, 'nombre_accion');

        if (empty($acciones)) {
            return 0;
        }

        return $this->select('notifications.id')
            ->join(
                'notifications_read nr',
                'nr.notification_id = notifications.id AND nr.user_id = ' . (int)$userId,
                'left'
            )
            ->whereIn('notifications.permiso', $acciones)
            ->where('nr.id IS NULL')
            ->countAllResults();
    }

    /*
    |--------------------------------------------------------------------------
    | Crear notificación
    |--------------------------------------------------------------------------
    */
    public function crear($titulo, $mensaje = null, $permiso = null, $link = null, $tipo = 'info')
    {
        return $this->insert([
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'permiso' => $permiso,
            'link' => $link,
            'tipo' => $tipo
        ]);
    }
}
