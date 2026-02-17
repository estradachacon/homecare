<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsToTrackingHead extends Migration
{
    public function up()
    {
        $fields = [
            'efectivo' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'otras_cuentas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('tracking_header', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tracking_header', ['efectivo', 'otras_cuentas']);
    }
}
