<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Branchs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'branch_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'branch_direction' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
            'status' => [
                'type'       => 'TINYINT', // Más eficiente que VARCHAR para un 0/1
                'constraint' => 1,
                'default'   => 1, // Por defecto: Activa (1)
                'null'     => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('branches');
        $this->db->table('branches')->insertBatch([
            ['branch_name' => 'Casa Matriz', 'branch_direction' => 'Carretera a los Planes de Renderos, San Salvador', 'status' => 1],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('branches');
    }
}
