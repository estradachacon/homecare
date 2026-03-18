<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTareasSistemaFecha extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tareas_sistema', [
            'ultima_ejecucion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('tareas_sistema', [
            'ultima_ejecucion' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
    }
}