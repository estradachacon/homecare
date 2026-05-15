<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddArchivoToRecuperos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('recuperos', [
            'archivo_ruta' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'observaciones',
            ],
            'archivo_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'archivo_ruta',
            ],
            'archivo_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'after'      => 'archivo_nombre',
            ],
            'archivo_tamano' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'archivo_tipo',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('recuperos', [
            'archivo_ruta',
            'archivo_nombre',
            'archivo_tipo',
            'archivo_tamano',
        ]);
    }
}
