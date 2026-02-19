<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnToBranchesMaps extends Migration
{
    public function up()
    {
        $this->forge->addColumn('branches', [
            'latitude'  => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('branches', ['latitude', 'longitude']);
    }
}
