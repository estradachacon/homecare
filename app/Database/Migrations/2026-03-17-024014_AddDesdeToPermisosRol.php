<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDesdeToPermisosRol extends Migration
{
    public function up()
    {
        $this->forge->addColumn('permisos_rol', [
            'desde' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'habilitado'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('permisos_rol', 'desde');
    }
}