<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_name', 'email', 'user_password', 'role_id', 'branch_id'];
    protected $returnType = 'array';

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function getUserWithRoleAndBranch($email)
    {
        return $this->select('users.*, roles.nombre AS role_name, branches.branch_name, branches.branch_direction')
                    ->join('roles', 'roles.id = users.role_id')
                    ->join('branches', 'branches.id = users.branch_id', 'left') // LEFT JOIN para ser más seguro
                    ->where('users.email', $email)
                    ->first();
    }
}