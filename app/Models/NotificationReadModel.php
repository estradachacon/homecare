<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationReadModel extends Model
{
    protected $table = 'notifications_read';
    protected $primaryKey = 'id';

    protected $returnType = 'object';

    protected $allowedFields = [
        'notification_id',
        'user_id',
        'read_at'
    ];

    public $useTimestamps = false;
}