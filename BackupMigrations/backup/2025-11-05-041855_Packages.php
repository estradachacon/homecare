<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Packages extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'vendedor' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'tipo_servicio' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'destino_personalizado' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true
            ],
            'lugar_recolecta_paquete' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'id_puntofijo' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'fecha_ingreso' => [
                'type' => 'DATE',
                'null' => true
            ],
            'fecha_entrega_personalizado' => [
                'type' => 'DATE',
                'null' => true
            ],
            'fecha_entrega_puntofijo' => [
                'type' => 'DATE',
                'null' => true
            ],
            'flete_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'toggle_pago_parcial' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'flete_pagado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'null' => true
            ],
            'flete_pendiente' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'null' => true
            ],
            'nocobrar_pack_cancelado' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'monto' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
                'null' => true
            ],
            'foto' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'comentarios' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'fragil' => [
                'type' => 'BOOLEAN',
                'default' => false
            ],
            'fecha_pack_entregado' => [
                'type' => 'DATE',
                'null' => true
            ],
            'estatus' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Pendiente'
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('packages');
    }

    public function down()
    {
        $this->forge->dropTable('packages');
    }
}
