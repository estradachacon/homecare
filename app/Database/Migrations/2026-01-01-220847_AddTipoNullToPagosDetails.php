<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoNullToPagosDetails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pagos_details', [
            'anulacion_motivo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'anulado_by',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pagos_details', [
            'anulacion_motivo'
        ]);
    }
}
