<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoNotaIdToConsignacionesHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('consignaciones_head', [
            'tipo_nota_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'concepto',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('consignaciones_head', 'tipo_nota_id');
    }
}
