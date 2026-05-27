<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddObservacionesToFacturasHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('facturas_head', [
            'observaciones' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Notas u observaciones de la factura',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', 'observaciones');
    }
}
