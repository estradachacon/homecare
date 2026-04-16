<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContAsientosDetalle extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'asiento_id'  => ['type' => 'INT'],
            'cuenta_id'   => ['type' => 'INT'],
            'descripcion' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'debe'        => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'haber'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'orden'       => ['type' => 'SMALLINT', 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('asiento_id');
        $this->forge->addKey('cuenta_id');
        $this->forge->createTable('cont_asientos_detalle');
    }

    public function down()
    {
        $this->forge->dropTable('cont_asientos_detalle');
    }
}
