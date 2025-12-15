<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashierReservToCajas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('accounts', [
            'cashier_reserv' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'balance',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('accounts', 'cashier_reserv');
    }
}
    