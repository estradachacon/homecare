<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionLotesManufactura extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('consignacion_lotes', [
            'manufactura' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'fecha_vencimiento',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('consignacion_lotes', 'manufactura');
    }
}
