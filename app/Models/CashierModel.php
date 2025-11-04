<?php

namespace App\Models;

use CodeIgniter\Model;

class CashierModel extends Model
{
    protected $table            = 'cashier';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = ['branch_id', 'name', 'initial_balance', 'current_balance', 'is_open', 'user_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

}
