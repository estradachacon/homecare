<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecuperosDetalleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'recupero_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'factura_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'monto_aplicado' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => false],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('recupero_id');
        $this->forge->addKey('factura_id');
        $this->forge->createTable('recuperos_detalle');
    }

    public function down()
    {
        $this->forge->dropTable('recuperos_detalle');
    }
}
