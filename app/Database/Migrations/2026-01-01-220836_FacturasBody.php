<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FacturasBody extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'factura_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'num_item' => [
                'type' => 'INT',
                'constraint' => 5,
            ],

            'tipo_item' => [
                'type' => 'INT',
                'constraint' => 5,
            ],

            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],

            'descripcion' => [
                'type' => 'TEXT',
            ],

            'cantidad' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],

            'unidad_medida' => [
                'type' => 'INT',
                'constraint' => 5,
                'null' => true,
            ],

            'precio_unitario' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],

            'monto_descuento' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],

            'venta_no_sujeta' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],

            'venta_exenta' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],

            'venta_gravada' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],

            'iva_item' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],

            'created_at DATETIME default CURRENT_TIMESTAMP',
            'updated_at DATETIME default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',

        ]);

        $this->forge->addKey('id', true);

        // Índice para búsquedas rápidas
        $this->forge->addKey('factura_id');

        // Foreign key
        $this->forge->addForeignKey(
            'factura_id',
            'facturas',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('factura_detalles');
    }

    public function down()
    {
        $this->forge->dropTable('factura_detalles');
    }
}
