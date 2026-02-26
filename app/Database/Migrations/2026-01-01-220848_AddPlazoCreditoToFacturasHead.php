<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlazoCreditoToFacturasHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('facturas_head', [
            'plazo_credito' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
                'after'      => 'condicion_operacion',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', 'plazo_credito');
    }
}
