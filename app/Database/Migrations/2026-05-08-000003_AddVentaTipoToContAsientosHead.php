<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVentaTipoToContAsientosHead extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('cont_asientos_head', [
            'tipo' => [
                'name'       => 'tipo',
                'type'       => 'ENUM',
                'constraint' => ['DIARIO', 'AJUSTE', 'CIERRE', 'APERTURA', 'VENTA'],
                'default'    => 'DIARIO',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('cont_asientos_head', [
            'tipo' => [
                'name'       => 'tipo',
                'type'       => 'ENUM',
                'constraint' => ['DIARIO', 'AJUSTE', 'CIERRE', 'APERTURA'],
                'default'    => 'DIARIO',
            ],
        ]);
    }
}
