<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterComisionesEstado extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('comisiones', [
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('comisiones', [
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'pagado'],
                'default'    => 'pendiente',
            ],
        ]);
    }
}
