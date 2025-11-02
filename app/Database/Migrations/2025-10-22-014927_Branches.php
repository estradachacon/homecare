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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('branches');
        $this->db->table('branches')->insertBatch([
            ['branch_name' => 'Metrogalerias', 'branch_direction' => 'Frente a metrocentro', 'status' => 1],
            ['branch_name' => 'Centro Histórico', 'branch_direction' => 'Centro Comercial Las Cascadas', 'status' => 1],
            ['branch_name' => 'La Gran Vía', 'branch_direction' => 'Centro Comercial La Gran Vía', 'status' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('branches');
    }
}
