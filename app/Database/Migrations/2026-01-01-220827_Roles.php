<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Roles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => true, 
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
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

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('roles');

        // OPCIONAL: Seedear/Insertar los roles iniciales (Gerente, Pagador, etc.)
        // Esto es muy útil para que al migrar la tabla ya esté lista para usarse.
        $this->db->table('roles')->insertBatch([
            ['nombre' => 'Gerente'],
            ['nombre' => 'Pagador'],
            ['nombre' => 'Digitador'],
            ['nombre' => 'Motorista'],
            ['nombre' => 'Cliente'],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('roles');
    }
}
