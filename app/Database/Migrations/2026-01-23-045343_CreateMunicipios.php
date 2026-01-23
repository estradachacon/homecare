<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMunicipios extends Migration
{
public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'departamento_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
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
        $this->forge->addKey('departamento_id');

        $this->forge->createTable('municipios');
    }

    public function down()
    {
        $this->forge->dropTable('municipios');
    }
}
