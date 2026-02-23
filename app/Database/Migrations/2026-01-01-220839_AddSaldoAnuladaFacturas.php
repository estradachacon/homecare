<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSaldoAnuladaFacturas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('facturas_head', [

            'saldo' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'after'      => 'total_pagar'
            ],

            'anulada' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '0=activa,1=anulada',
                'after'      => 'saldo'
            ],

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', ['saldo', 'anulada']);
    }
}
