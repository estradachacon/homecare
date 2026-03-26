<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComprasDetalles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],

            'compra_id' => ['type' => 'INT'],

            'num_item' => ['type' => 'INT', 'null' => true],
            'tipo_item' => ['type' => 'INT', 'null' => true],

            'codigo' => ['type' => 'VARCHAR', 'constraint' => 50],
            'descripcion' => ['type' => 'TEXT', 'null' => true],

            'cantidad' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'unidad_medida' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],

            'precio_unitario' => ['type' => 'DECIMAL', 'constraint' => '12,6', 'default' => 0],
            'monto_descuento' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],

            'iva_item' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],

            'producto_id' => ['type' => 'INT', 'null' => true],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('compras_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('compras_detalles');
    }
}