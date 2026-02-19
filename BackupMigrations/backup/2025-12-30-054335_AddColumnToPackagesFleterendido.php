<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnToPackagesFleterendido extends Migration
{
    public function up()
    {
        $this->forge->addColumn('packages', [
            'flete_rendido' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'pago_cuenta', // ajusta según tu tabla
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('packages', 'flete_rendido');
    }
}
