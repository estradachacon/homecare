<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesCierresFacturas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'cierre_id'   => ['type' => 'INT', 'null' => false],
            'detalle_id'  => ['type' => 'INT', 'null' => false],
            'factura_id'  => ['type' => 'INT', 'null' => false],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('consignaciones_cierres_facturas');
    }

    public function down()
    {
        $this->forge->dropTable('consignaciones_cierres_facturas');
    }
}
