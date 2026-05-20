<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConsignacionIdToPedidosHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pedidos_head', [
            'consignacion_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
                'after'    => 'vendedor_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pedidos_head', 'consignacion_id');
    }
}
