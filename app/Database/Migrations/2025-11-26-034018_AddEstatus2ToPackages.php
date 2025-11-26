<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEstatus2ToPackages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('packages', [
            'estatus2' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'estatus' // opcional: coloca la columna después de "estatus"
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('packages', 'estatus2');
    }
}
