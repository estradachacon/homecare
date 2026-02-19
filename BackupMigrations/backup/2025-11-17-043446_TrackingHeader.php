<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TrackingHeader extends Migration
{
 public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Motorista asignado'
            ],
            'route_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Ruta opcional'
            ],
            'date' => [
                'type' => 'DATE',
                'null' => false
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->createTable('tracking_header');
    }

    public function down()
    {
        $this->forge->dropTable('tracking_header');
    }
}
