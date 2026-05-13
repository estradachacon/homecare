<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoPartidaRemesasToContConfiguracion extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cont_configuracion', [
            'tipo_partida_remesas_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'tipo_partida_pagos_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cont_configuracion', 'tipo_partida_remesas_id');
    }
}
