<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddComentariosPagosDetails extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pagos_details', [
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pagos_details', ['observaciones']);
    }
}
