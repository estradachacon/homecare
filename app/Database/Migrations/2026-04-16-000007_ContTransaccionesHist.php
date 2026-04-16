<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContTransaccionesHist extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'auto_increment' => true],
            'asiento_id'      => ['type' => 'INT'],
            'cuenta_id'       => ['type' => 'INT'],
            'fecha'           => ['type' => 'DATE'],
            'descripcion'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'debe'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'haber'           => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'saldo_acumulado' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'anio'            => ['type' => 'SMALLINT'],
            'mes'             => ['type' => 'TINYINT'],
            'tipo_asiento'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('cuenta_id');
        $this->forge->addKey(['anio', 'mes']);
        $this->forge->createTable('cont_transacciones_hist');
    }

    public function down()
    {
        $this->forge->dropTable('cont_transacciones_hist');
    }
}
