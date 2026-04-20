<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesDetalles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'auto_increment' => true],
            'consignacion_id'  => ['type' => 'INT', 'null' => false],
            'producto_id'      => ['type' => 'INT', 'null' => false],
            'cantidad'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'precio_unitario'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'subtotal'         => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('consignaciones_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('consignaciones_detalles');
    }
}
