<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuedanFacturasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'quedan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            'factura_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            'monto_aplicado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey('quedan_id');
        $this->forge->addKey('factura_id');

        $this->forge->createTable('quedan_facturas');
    }

    public function down()
    {
        $this->forge->dropTable('quedan_facturas');
    }
}