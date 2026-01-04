<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentGroupModel extends Model
{
    protected $table = 'content_groups';
    protected $primaryKey = 'id';
    protected $allowedFields = ['slug', 'title', 'description', 'type', 'is_active'];
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
