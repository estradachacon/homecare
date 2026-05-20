<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddConsignacionIdsToPedidosHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pedidos_head', [
            'consignacion_ids' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'consignacion_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pedidos_head', ['consignacion_ids']);
    }
}
