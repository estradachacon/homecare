<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrigenComision extends Migration
{
    public function up()
    {
        $this->forge->addColumn('comision_detalles', [
            'origen_comision' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'comision_aplicada'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('comision_detalles', 'origen_comision');
    }
}
