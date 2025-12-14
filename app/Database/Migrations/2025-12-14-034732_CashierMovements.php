<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CashierMovements extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'cashier_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'cashier_session_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'branch_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['in', 'out'],
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
            ],
            'balance_after' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
            ],
            'concept' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'reference_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'reference_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['cashier_id']);
        $this->forge->addKey(['cashier_session_id']);
        $this->forge->addKey(['branch_id']);

        $this->forge->createTable('cashier_movements');
    }

    public function down()
    {
        $this->forge->dropTable('cashier_movements');
    }
}
