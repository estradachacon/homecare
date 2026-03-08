<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductos extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],

            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],

            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => 300,
            ],

            'activo' => [
                'type' => 'TINYINT',
                'default' => 1
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
        $this->forge->addKey('codigo');

        $this->forge->createTable('productos');
    }

    public function down()
    {
        $this->forge->dropTable('productos');
    }
}
