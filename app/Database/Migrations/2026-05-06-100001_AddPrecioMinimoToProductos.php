<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPrecioMinimoToProductos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('productos', [
            'precio_minimo' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('productos', 'precio_minimo');
    }
}
