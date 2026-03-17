<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComisionesReglas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'tipo' => [
                'type' => 'ENUM',
                'constraint' => ['producto', 'categoria']
            ],
            'valor' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'porcentaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2'
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('comisiones_reglas');
    }

    public function down()
    {
        $this->forge->dropTable('comisiones_reglas');
    }
}
