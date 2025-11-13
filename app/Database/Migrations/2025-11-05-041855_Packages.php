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
            'retiro_paquete' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true
            ],
            'destino' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true
            ],
            'id_puntofijo' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'direccion' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'fecha_ingreso' => [
                'type' => 'DATE',
                'null' => true
            ],
            'fecha_entrega' => [
                'type' => 'DATE',
                'null' => true
            ],
            'flete_total' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'flete_pagado' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'flete_pendiente' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ],
            'monto' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
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
            'estatus' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'pendiente'
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
