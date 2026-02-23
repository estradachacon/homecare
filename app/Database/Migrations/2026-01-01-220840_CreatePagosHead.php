<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagosHead extends Migration
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

            // Numero de recupero / referencia bancaria
            'numero_recupero' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],

            // Cliente que paga
            'cliente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true
            ],

            // Cuenta bancaria (si aplica)
            'numero_cuenta_bancaria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true
            ],

            // Forma de pago: efectivo, banco, cheque, etc
            'forma_pago' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true
            ],

            // Total de la operación
            'total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true
            ],

            // Fecha del pago
            'fecha_pago' => [
                'type' => 'DATETIME',
                'null' => true
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
        $this->forge->addKey('cliente_id');

        $this->forge->createTable('pagos_head');
    }

    public function down()
    {
        $this->forge->dropTable('pagos_head');
    }
}