<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCodActividadToClientes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('clientes', [
            'cod_actividad' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'after'      => 'nrc',
            ],
            'desc_actividad' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
                'after'      => 'cod_actividad',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('clientes', ['cod_actividad', 'desc_actividad']);
    }
}
