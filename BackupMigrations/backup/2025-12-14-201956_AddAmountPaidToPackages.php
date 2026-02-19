<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAmountPaidToPackages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('packages', [
            'amount_paid' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
                'after'      => 'monto', // ajusta según tu tabla
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('packages', 'amount_paid');
    }
}
