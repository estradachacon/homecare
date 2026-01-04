<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentImagesModel extends Model
{
    protected $table = 'content_images';
    protected $primaryKey = 'id';
    protected $allowedFields = ['group_id', 'image', 'caption', 'position', 'is_active'];
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}