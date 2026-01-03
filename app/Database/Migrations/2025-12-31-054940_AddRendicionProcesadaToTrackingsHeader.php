<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRendicionProcesadaToTrackingsHeader extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tracking_header', [
            'rendicion_procesada' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tracking_header', 'rendicion_procesada');
    }
}
