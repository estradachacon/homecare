<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVentaGravadaToComprasDetails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('compras_detalles', [
            'venta_gravada' => [
                'type'           => 'DECIMAL',
                'constraint'     => '12,6',
                'null'           => true,
                'default'        => 0.000000,
                'after'          => 'precio_unitario',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('compras_detalles', 'venta_gravada');
    }
}