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

        return $this->db->table('notifications')
            ->select('notifications.*')
            ->join(
                'notifications_read nr',
                'nr.notification_id = notifications.id AND nr.user_id = ' . (int)$userId,
                'left'
            )
            ->whereIn('notifications.permiso', $acciones)
            ->where('nr.id IS NULL')
            ->orderBy('notifications.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResult();
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


    public function getNotificacionesVisibles($userId, $roleId, $from = null, $to = null, $perPage = 10)
    {
        $this->select('notifications.*');

        $this->join(
            'notifications_read nr',
            'nr.notification_id = notifications.id AND nr.user_id = ' . $this->db->escape($userId),
            'left'
        );

        $this->join(
            'permisos_rol pr',
            'pr.nombre_accion = notifications.permiso AND pr.role_id = ' . $this->db->escape($roleId),
            'left'
        );

        $this->groupStart()

            ->where('nr.user_id IS NOT NULL')

            ->orGroupStart()
            ->where('pr.habilitado', 1)
            ->where('notifications.created_at >= pr.desde')
            ->groupEnd()

            ->orWhere('notifications.permiso IS NULL')

            ->groupEnd();

        if ($from) {
            $this->where('DATE(notifications.created_at) >=', $from);
        }

        if ($to) {
            $this->where('DATE(notifications.created_at) <=', $to);
        }

        $this->orderBy('notifications.id', 'DESC');

        return $this->paginate($perPage);
    }
    public function getNoLeidasVisibles($userId, $roleId, $limit = 5)
    {
        $this->select('notifications.*');

        $this->join(
            'notifications_read nr',
            'nr.notification_id = notifications.id AND nr.user_id = ' . $this->db->escape($userId),
            'left'
        );

        $this->join(
            'permisos_rol pr',
            'pr.nombre_accion = notifications.permiso AND pr.role_id = ' . $this->db->escape($roleId),
            'left'
        );

        $this->where('nr.id IS NULL');

        $this->groupStart()

            ->groupStart()
            ->where('pr.habilitado', 1)
            ->where('notifications.created_at >= pr.desde')
            ->groupEnd()

            ->orWhere('notifications.permiso IS NULL')

            ->groupEnd();

        $this->orderBy('notifications.id', 'DESC');

        return $this->findAll($limit);
    }
}
