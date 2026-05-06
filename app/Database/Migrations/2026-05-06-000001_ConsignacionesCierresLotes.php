<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesCierresLotes extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'cierre_id'         => ['type' => 'INT', 'unsigned' => true],
            'cierre_detalle_id' => ['type' => 'INT', 'unsigned' => true],
            'detalle_id'        => ['type' => 'INT', 'unsigned' => true],
            'producto_id'       => ['type' => 'INT', 'unsigned' => true],
            'lote_id'           => ['type' => 'INT', 'unsigned' => true],
            'tipo'              => ['type' => 'VARCHAR', 'constraint' => 30],
            'cantidad'          => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('cierre_id');
        $this->forge->addKey('cierre_detalle_id');
        $this->forge->addKey('detalle_id');
        $this->forge->addKey(['lote_id', 'tipo']);
        $this->forge->createTable('consignaciones_cierres_lotes', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('consignaciones_cierres_lotes', true);
    }
}
