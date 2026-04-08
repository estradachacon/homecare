<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoDteToComprasHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('compras_head', [
            'tipo_dte' => [
                'type'       => 'VARCHAR',
                'constraint' => 2,
                'null'       => true,
                'after'      => 'numero_control', // opcional, solo orden
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('compras_head', 'tipo_dte');
    }
}