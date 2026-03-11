<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoToProductos extends Migration
{
    public function up()
    {
        $fields = [
            'tipo' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'default'    => 0,
            ],

        ];

        $this->forge->addColumn('productos', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('productos', [
            'tipo'
        ]);
    }
}
