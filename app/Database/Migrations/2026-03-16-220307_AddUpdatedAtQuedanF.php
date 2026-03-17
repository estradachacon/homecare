<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUpdatedAtQuedanF extends Migration
{
    public function up()
    {
        $fields = [

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ];

        $this->forge->addColumn('quedan_facturas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('updated_at', [
            'updated_at'
        ]);
    }
}
