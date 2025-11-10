<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SettledPoints extends Migration
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
            'point_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'ruta_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false
            ],
            'days_configuration' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'hora_inicio' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'hora_fin' => [
                'type' => 'TIME',
                'null' => true,
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
        $this->forge->addForeignKey('ruta_id', 'routes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('settled_points');
    }

    public function down()
    {
        $this->forge->dropTable('settled_points');
    }
}
