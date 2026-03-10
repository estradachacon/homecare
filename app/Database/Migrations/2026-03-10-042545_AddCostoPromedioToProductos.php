<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCostoPromedioToProductos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('productos', [
            'costo_promedio' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,4',
                'default'    => 0,
                'after'      => 'descripcion',
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('productos', 'costo_promedio');
    }
}
