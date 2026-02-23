<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagosDetails extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],

            // Relación al head del pago
            'pago_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ],

            // Factura asociada
            'factura_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ],

            // Monto aplicado a esa factura
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],

        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('pago_id');
        $this->forge->addKey('factura_id');

        $this->forge->createTable('pagos_details');
    }

    public function down()
    {
        $this->forge->dropTable('pagos_details');
    }
}