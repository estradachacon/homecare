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


public function searchSellers($term)
{
    if (!$term || trim($term) === '') {
        return []; // 👈 Select2 suele pedir esto antes de escribir
    }

    return $this->like('seller', $term)
                ->select('id, seller')
                ->findAll(20);
}

}
