<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionLotesCamposTexto extends Migration
{
    public function up(): void
    {
        $this->forge->modifyColumn('consignacion_lotes', [
            'fecha_vencimiento' => ['name' => 'fecha_vencimiento', 'type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'manufactura'       => ['name' => 'manufactura',       'type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('consignacion_lotes', [
            'fecha_vencimiento' => ['name' => 'fecha_vencimiento', 'type' => 'DATE', 'null' => true],
            'manufactura'       => ['name' => 'manufactura',       'type' => 'DATE', 'null' => true],
        ]);
    }
}
