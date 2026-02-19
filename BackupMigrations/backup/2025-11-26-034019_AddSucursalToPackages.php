<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSucursalToPackages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('packages', [
            'branch' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'estatus' // opcional: coloca la columna después de "estatus"
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('packages', 'branch');
    }
}
