<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'titulo' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'mensaje' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'link' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'info'
            ],

            'permiso' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
