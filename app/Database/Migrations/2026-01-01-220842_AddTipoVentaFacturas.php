<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoVentaFacturas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('facturas_head', [
            'tipo_venta' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', ['tipo_venta']);
    }
}
