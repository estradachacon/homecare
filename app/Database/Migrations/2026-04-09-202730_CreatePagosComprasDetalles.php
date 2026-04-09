<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagosComprasDetalles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pago_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'compra_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'anulado' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'anulado_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'anulado_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('pagos_compras_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('pagos_compras_detalles');
    }
}