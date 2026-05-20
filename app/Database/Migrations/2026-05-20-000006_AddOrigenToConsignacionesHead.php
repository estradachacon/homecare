<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrigenToConsignacionesHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('consignaciones_head', [
            'origen' => [
                'type'       => "ENUM('normal', 'emergencia')",
                'default'    => 'normal',
                'null'       => false,
                'after'      => 'estado',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('consignaciones_head', 'origen');
    }
}
