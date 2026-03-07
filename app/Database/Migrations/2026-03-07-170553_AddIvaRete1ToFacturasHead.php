<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIvaRete1ToFacturasHead extends Migration
{
    public function up()
    {
        $fields = [

            'iva_rete1' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'total_iva'
            ],

        ];

        $this->forge->addColumn('facturas_head', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', 'iva_rete1');
    }
}
