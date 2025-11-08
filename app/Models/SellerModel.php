<?php

namespace App\Models;

use CodeIgniter\Model;

class SellerModel extends Model
{
    protected $table = 'sellers';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'seller',
        'tel_seller',
        'created_at',
        'updated_at'
    ];
}
