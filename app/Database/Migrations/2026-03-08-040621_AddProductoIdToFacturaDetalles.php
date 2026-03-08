<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductoIdToFacturaDetalles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('factura_detalles', [

            'producto_id' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'factura_id'
            ]

        ]);

        $this->forge->addKey('producto_id');
    }

    public function down()
    {
        $this->forge->dropColumn('factura_detalles', 'producto_id');
    }
}
