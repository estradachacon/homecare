<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Accounts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],

            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],

            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('accounts');

        $this->db->table('accounts')->insert([
            'id'          => 1,
            'name'        => 'Efectivo',
            'type'        => 'Efectivo',
            'description' => 'Cuenta base',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('accounts');
    }
}
