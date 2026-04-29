<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogosHacienda extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
        ]);

        $this->forge->addKey('codigo', true);
        $this->forge->createTable('hacienda_departamentos', true);

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'departamento_codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['departamento_codigo', 'codigo']);
        $this->forge->addForeignKey('departamento_codigo', 'hacienda_departamentos', 'codigo', 'CASCADE', 'CASCADE');
        $this->forge->createTable('hacienda_municipios', true);
    }

    public function down()
    {
        $this->forge->dropTable('hacienda_municipios', true);
        $this->forge->dropTable('hacienda_departamentos', true);
    }
}