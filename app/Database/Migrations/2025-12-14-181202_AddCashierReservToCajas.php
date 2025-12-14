<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCashierReservToCajas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cashier', [
            'cashier_reserv' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'current_balance', // ajusta si quieres
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cashier', 'cashier_reserv');
    }
}
