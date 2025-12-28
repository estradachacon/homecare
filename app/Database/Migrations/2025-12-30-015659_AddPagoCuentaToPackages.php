<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPagoCuentaToPackages extends Migration
{
    public function up()
    {
        $fields = [
            'pago_cuenta' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'estatus2', // ajusta si quieres otra posición
            ],
        ];

        $this->forge->addColumn('packages', $fields);

        $this->forge->addKey('pago_cuenta');
    }

    public function down()
    {
        $this->forge->dropColumn('packages', 'pago_cuenta');
    }
}
