<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAllowInvoiceWithoutStockToSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('settings', [
            'allow_invoice_without_stock' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'favicon'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('settings', 'allow_invoice_without_stock');
    }
}