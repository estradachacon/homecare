<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReenvioToPackages extends Migration
{
    public function up()
    {
        $fields = [
            'reenvios' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('packages', $fields);

        // (Opcional pero recomendado)
        // Crear índice para búsquedas por cuenta
        $this->forge->addKey('reenvios');
    }

    public function down()
    {
        $this->forge->dropColumn('packages', 'reenvios');
    }
}
