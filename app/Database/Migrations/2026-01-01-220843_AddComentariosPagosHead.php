<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddComentariosPagosHead extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pagos_head', [
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'anulado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pagos_head', ['observaciones', 'anulado']);
    }
}
