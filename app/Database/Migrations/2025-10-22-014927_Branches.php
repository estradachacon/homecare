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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('branches');
        $this->db->table('branches')->insertBatch([
            ['branch_name' => 'Metrogalerias', 'branch_direction' => 'Frente a metrocentro'],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('branches');
    }
}
