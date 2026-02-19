<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SaldoResumen extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'fecha' => ['type' => 'DATE', 'null' => false],
            'total_saldo' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('saldo_resumen');
    }

    public function down()
    {
        //
    }
}
