<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTareasSistemaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],

            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],

            'ultima_ejecucion' => [
                'type' => 'DATE',
                'null' => true,
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nombre');

        $this->forge->createTable('tareas_sistema');
    }

    public function down()
    {
        $this->forge->dropTable('tareas_sistema');
    }
}