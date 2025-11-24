<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Transactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'account_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],

            'tracking_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],

            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['entrada', 'salida'],
            ],

            'monto' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
            ],

            'origen' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],

            'referencia' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
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
        $this->forge->createTable('transactions');
    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}
