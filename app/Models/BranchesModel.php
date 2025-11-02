<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchesModel extends Model
{
    protected $table            = 'branches';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = ['branch_name', 'branch_direction'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
