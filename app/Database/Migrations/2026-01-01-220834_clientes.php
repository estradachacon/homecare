<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Clientes extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'tipo_documento' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],

            'numero_documento' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],

            'nrc' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],

            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],

            'telefono' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],

            'correo' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],

            'departamento' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'null' => true,
            ],

            'municipio' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'null' => true,
            ],

            'direccion' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'created_at DATETIME default CURRENT_TIMESTAMP',
            'updated_at DATETIME default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);

        // Evita duplicados por documento
        $this->forge->addUniqueKey(['tipo_documento', 'numero_documento']);

        // NRC también puede repetirse si no es null, pero lo dejamos indexado
        $this->forge->addKey('nrc');

        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
