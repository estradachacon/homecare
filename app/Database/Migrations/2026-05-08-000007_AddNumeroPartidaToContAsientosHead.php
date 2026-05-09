<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNumeroPartidaToContAsientosHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cont_asientos_head', [
            'numero_partida' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'tipo_partida_id',
                'comment'    => 'Correlativo secuencial por tipo_partida y año',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cont_asientos_head', 'numero_partida');
    }
}
