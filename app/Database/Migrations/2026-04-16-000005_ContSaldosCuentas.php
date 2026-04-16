<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContSaldosCuentas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'cuenta_id'     => ['type' => 'INT'],
            'periodo_id'    => ['type' => 'INT'],
            'saldo_inicial' => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_debe'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_haber'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'saldo_final'   => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['cuenta_id', 'periodo_id']);
        $this->forge->createTable('cont_saldos_cuentas');
    }

    public function down()
    {
        $this->forge->dropTable('cont_saldos_cuentas');
    }
}
