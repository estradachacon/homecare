<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColoniaIDToPackages extends Migration
{
    public function up()
    {
        $fields = [
            'colonia_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('packages', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('packages', 'colonia_id');
    }
}
