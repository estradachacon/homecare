<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFotoToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'email' // puedes cambiar la posición si quieres
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'foto');
    }
}
