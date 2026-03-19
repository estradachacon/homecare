<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveTipoFromComisionesReglas extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('comisiones_reglas', 'tipo');
    }

    public function down()
    {
        $this->forge->addColumn('comisiones_reglas', [
            'tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ]);
    }
}