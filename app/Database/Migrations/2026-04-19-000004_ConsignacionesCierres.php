<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesCierres extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'auto_increment' => true],
            'consignacion_id'       => ['type' => 'INT', 'null' => false],
            'nueva_consignacion_id' => ['type' => 'INT', 'null' => true],
            'observaciones'         => ['type' => 'TEXT', 'null' => true],
            'created_by'            => ['type' => 'INT', 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('consignaciones_cierres');
    }

    public function down()
    {
        $this->forge->dropTable('consignaciones_cierres');
    }
}
