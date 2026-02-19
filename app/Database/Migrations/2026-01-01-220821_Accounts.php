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

            'balance' => [
                'type'           => 'DECIMAL',
                'constraint'     => '20,2',
                'default'        => 0.00,
                'comment'        => 'Saldo actual TOTAL de la cuenta (calculado a partir de transacciones)',
            ],

            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],

            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1, // 1 = Activo, 0 = Inactivo
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
        $this->db->table('accounts')->insertBatch([
            ['ID' => '1', 'name' => 'Efectivo', 'type' => 'Efectivo', 'description' => 'Cuenta de efectivo', 'balance' => 0.00],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('accounts');
    }
}
