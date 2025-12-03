<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDataToAccounts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('accounts', [
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1=Activa, 0=Inactiva',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('accounts', ['code', 'is_active']);

        $this->forge->modifyColumn('accounts', [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => null, // Volver al estado anterior
            ],
        ]);
    }
}
