<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComisionesConfig extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'porcentaje_default' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('comisiones_config');
    }

    public function down()
    {
        $this->forge->dropTable('comisiones_config');
    }
}