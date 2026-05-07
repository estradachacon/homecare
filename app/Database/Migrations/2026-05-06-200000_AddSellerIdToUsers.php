<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSellerIdToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'seller_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'seller_id');
    }
}
