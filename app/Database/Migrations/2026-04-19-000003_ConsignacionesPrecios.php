<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesPrecios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'vendedor_id' => ['type' => 'INT', 'null' => false],
            'cliente_id'  => ['type' => 'INT', 'null' => true],
            'producto_id' => ['type' => 'INT', 'null' => false],
            'precio'      => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'activo'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('consignaciones_precios');
    }

    public function down()
    {
        $this->forge->dropTable('consignaciones_precios');
    }
}
