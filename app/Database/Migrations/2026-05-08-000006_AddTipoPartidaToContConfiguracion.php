<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoPartidaToContConfiguracion extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cont_configuracion', [
            'tipo_partida_ventas_id' => [
                'type'    => 'INT',
                'unsigned' => true,
                'null'    => true,
                'default' => null,
                'after'   => 'cuenta_ventas_servicio2_label',
            ],
            'tipo_partida_pagos_id' => [
                'type'    => 'INT',
                'unsigned' => true,
                'null'    => true,
                'default' => null,
                'after'   => 'tipo_partida_ventas_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cont_configuracion', ['tipo_partida_ventas_id', 'tipo_partida_pagos_id']);
    }
}
