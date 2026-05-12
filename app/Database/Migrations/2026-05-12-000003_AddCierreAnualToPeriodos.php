<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCierreAnualToPeriodos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cont_periodos', [
            'cierre_anual' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'usuario_cierre_id',
            ],
            'fecha_cierre_anual' => [
                'type'    => 'DATE',
                'null'    => true,
                'default' => null,
                'after'   => 'cierre_anual',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cont_periodos', ['cierre_anual', 'fecha_cierre_anual']);
    }
}
