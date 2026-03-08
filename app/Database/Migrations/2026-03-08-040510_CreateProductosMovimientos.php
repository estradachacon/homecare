<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductosMovimientos extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],

            'producto_id' => [
                'type' => 'INT',
            ],

            'tipo_movimiento' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],

            'cantidad' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2'
            ],

            'referencia_tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],

            'referencia_id' => [
                'type' => 'INT',
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
        $this->forge->addKey('producto_id');

        $this->forge->createTable('productos_movimientos');
    }

    public function down()
    {
        $this->forge->dropTable('productos_movimientos');
    }
}
