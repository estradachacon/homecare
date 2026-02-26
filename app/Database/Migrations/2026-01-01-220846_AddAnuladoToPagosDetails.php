<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAnuladoToPagosDetails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pagos_details', [
            'anulado' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'observaciones',
            ],
            'anulado_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'anulado',
            ],
            'anulado_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'anulado_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pagos_details', [
            'anulado',
            'anulado_at',
            'anulado_by'
        ]);
    }
}
