<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComisionDetalles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'comision_id' => [
                'type' => 'INT',
            ],
            'factura_id' => [
                'type' => 'INT',
            ],
            'producto_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'cantidad' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'precio_sin_iva' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'total_linea' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'comision_aplicada' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'monto_comision' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'tipo_venta' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('comision_id');

        $this->forge->createTable('comision_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('comision_detalles');
    }
}