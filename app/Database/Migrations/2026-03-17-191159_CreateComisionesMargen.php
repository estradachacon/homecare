<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComisionesMargen extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'margen_min' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true
            ],
            'margen_max' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true
            ],
            'porcentaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2'
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('comisiones_margen');
    }

    public function down()
    {
        $this->forge->dropTable('comisiones_margen');
    }
}
