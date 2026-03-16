<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAnulacionFieldsToFacturasHead extends Migration
{
    public function up()
    {
        $fields = [

            'anulada_por' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'anulada',
            ],

            'fecha_anulacion' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'anulada_por',
            ],

        ];

        $this->forge->addColumn('facturas_head', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', [
            'anulada_por',
            'fecha_anulacion'
        ]);
    }
}
