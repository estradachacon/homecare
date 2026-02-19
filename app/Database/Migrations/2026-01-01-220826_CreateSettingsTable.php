<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'company_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'favicon' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'primary_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => '#0d6efd',
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('settings', true);

        // 🔹 Insertar configuración inicial
        $this->db->table('settings')->insert([
            'company_name'    => 'HomeCare',
            'company_address' => 'Casa Matriz, Carretera a los Planes de Renderos, San Salvador',
            'primary_color'   => '#468aef',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('settings', true);
    }
}
