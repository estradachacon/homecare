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
        $this->db->table('accounts')->insert([
            'id'          => 1,
            'name'        => 'Efectivo',
            'type'        => 'Efectivo',
            'description' => 'Cuenta base',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
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
