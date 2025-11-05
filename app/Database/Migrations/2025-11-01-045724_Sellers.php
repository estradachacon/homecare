<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Sellers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'seller' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'tel_seller' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('sellers');
    }

    public function down()
    {
        $this->forge->dropTable('sellers');
    }
}
