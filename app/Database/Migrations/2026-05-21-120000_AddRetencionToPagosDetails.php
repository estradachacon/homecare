<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRetencionToPagosDetails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pagos_details', [
            'retencion_aplicada' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'observaciones',
            ],
            'retencion_monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => '0.00',
                'after'      => 'retencion_aplicada',
            ],
            'retencion_cuenta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
                'after'      => 'retencion_monto',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pagos_details', ['retencion_aplicada', 'retencion_monto', 'retencion_cuenta_id']);
    }
}
