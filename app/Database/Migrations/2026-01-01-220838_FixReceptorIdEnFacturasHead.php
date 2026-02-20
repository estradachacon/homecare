<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class modificacionClientes extends Migration
{
    public function up()
    {
        // Cambiar receptor_id a BIGINT UNSIGNED NULL
        $this->forge->modifyColumn('facturas_head', [
            'receptor_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);
    }

    public function down()
    {
        // Revertir a VARCHAR(10) NOT NULL
        $this->forge->modifyColumn('facturas_head', [
            'receptor_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
        ]);
    }
}
