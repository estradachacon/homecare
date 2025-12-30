<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table            = 'password_resets';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';

    protected $allowedFields    = [
        'user_id',
        'code',
        'expires_at',
        'created_at'
    ];

    public $useTimestamps = false;
}