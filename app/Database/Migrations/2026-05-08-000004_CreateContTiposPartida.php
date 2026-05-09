<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContTiposPartida extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'descripcion' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'activo'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cont_tipos_partida');
    }

    public function down()
    {
        $this->forge->dropTable('cont_tipos_partida');
    }
}
