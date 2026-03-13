<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProveedores extends Migration
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

            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'direccion' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'email' => [
                'type' => 'TEXT',
                'null' => true,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('nombre');

        $this->forge->createTable('proveedores');
    }

    public function down()
    {
        $this->forge->dropTable('proveedores');
    }
}
