<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoVentaAndVendedorToComisionesReglas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('comisiones_reglas', [
            'tipo_venta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'id', // opcional, orden de columnas
            ],
            'vendedor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'tipo_venta_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('comisiones_reglas', ['tipo_venta_id', 'vendedor_id']);
    }
}