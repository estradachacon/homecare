<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateColonias extends Migration
{
public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'municipio_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'alias' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
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
        $this->forge->addKey('municipio_id');
        $this->forge->addKey('nombre');

        // Evita duplicados malos: misma colonia en el mismo municipio
        $this->forge->addUniqueKey(['municipio_id', 'nombre']);

        $this->forge->createTable('colonias');
    }

    public function down()
    {
        $this->forge->dropTable('colonias');
    }
}
