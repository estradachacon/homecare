<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTipoNotasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => false, 'auto_increment' => true],
            'nombre'     => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => false],
            'activo'     => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 1],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tipo_notas', true);
    }

    public function down()
    {
        $this->forge->dropTable('tipo_notas', true);
    }
}
