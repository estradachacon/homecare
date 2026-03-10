<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCostoUnitarioToProductosMovimientos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('productos_movimientos', [
            'costo_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,4',
                'null'       => true,
                'after'      => 'cantidad',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('productos_movimientos', 'costo_unitario');
    }
}
