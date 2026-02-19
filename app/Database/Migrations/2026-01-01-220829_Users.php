<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_name' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'unique' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'user_password' => [
                'type' => 'VARCHAR',
                'constraint' => '255', 
            ],
            'role_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE, 
                'default' => 5,   
            ],
            'branch_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'null'           => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users');
        $this->db->table('users')->insertBatch([
            ['user_name' => 'Gerente', 'user_password' => '$2y$10$42k/s7W2rpRZFWMwWHQmYurL0gEvsSjBAEQ69m4pkPlc9cbUjWzWW', 'email' => 'gerente@mail.com', 'role_id' => 1,'branch_id' => 1],
            ['user_name' => 'Pagador', 'user_password' => '$2y$10$e/YMVv4vInsp52XPRAi2C.Im41V6Ia.wZuhSMuAMauyIn6ERDOp82', 'email' => 'pagador@mail.com', 'role_id' => 2, 'branch_id' => 1],
            ['user_name' => 'Digitador', 'user_password' => '$2y$10$.saDgnSHz5VglyjFkS.6Jeok2U0jeTnRJIkGWI2Ltf4wseFoO8afi', 'email' => 'digitador@mail.com', 'role_id' => 3, 'branch_id' => 1],
            ['user_name' => 'Motorista', 'user_password' => '$2y$10$UO0TxUKl0pwbX5oe0tMWu.D3MM0pSaDsiCEiiO29UEiU6pMjeU2Se', 'email' => 'motorista@mail.com', 'role_id' => 4, 'branch_id' => 1],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
