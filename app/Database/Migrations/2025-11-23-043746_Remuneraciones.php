<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Remuneraciones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'paquete_id' => ['type' => 'INT', 'null' => false],
            'vendedor_id' => ['type' => 'INT', 'null' => false],
            'monto_remuneracion' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'fecha' => ['type' => 'DATE', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('remuneraciones');
    }


    public function down()
    {
        //
    }
}
