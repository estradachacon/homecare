<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Cashier extends Migration
{
    public function up()
    {
        // 1. Definición de la estructura de la tabla
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'branch_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Sucursal a la que pertenece esta caja',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
                'comment' => 'Nombre de la caja (e.g., Caja Principal, TPV 1)',
            ],
            'initial_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Saldo inicial para el turno/día',
            ],
            'current_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'comment' => 'Saldo actual de la caja',
            ],
            'is_open' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0, // 0 = Cerrada, 1 = Abierta
                'comment' => 'Estado de la caja: abierta o cerrada',
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
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

        // 2. Definición de llaves
        $this->forge->addKey('id', true); // Primary Key
        $this->forge->addKey('branch_id'); // Index para búsquedas rápidas por sucursal
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('branch_id', 'branches', 'id', 'CASCADE', 'CASCADE');

        // 3. Crear la tabla
        $this->forge->createTable('cashier');

        // 4. Seeding: Usamos insertBatch() para insertar múltiples filas.
        $data = [
            [
                'branch_id'         => 1,
                'name'              => 'Caja General (Default)',
                'initial_balance'   => 0.00,
                'current_balance'   => 0.00,
                'is_open'           => 0,
                'user_id'          => 1,
                'created_at'        => date('Y-m-d H:i:s'), 
                'updated_at'        => date('Y-m-d H:i:s'), 
            ],
            [
                'branch_id'         => 1, 
                'name'              => 'Caja secundaria (Default)',
                'initial_balance'   => 1000.00, // Debe ser 1000.00, no 1,000.00
                'current_balance'   => 0.00,
                'is_open'           => 0,
                'user_id'          => 2,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ],
        ];

        // 🚨 CAMBIO CLAVE: Usamos insertBatch() para inserción múltiple.
        $this->db->table('cashier')->insertBatch($data);
    }

    public function down()
    {
        // Solo elimina la tabla.
        $this->forge->dropTable('cashier');
    }
}
