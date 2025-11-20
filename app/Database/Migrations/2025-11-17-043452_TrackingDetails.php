<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TrackingDetails extends Migration
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
            'tracking_header_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Referencia al encabezado'
            ],
            'package_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Paquete asignado'
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'note' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
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
        $this->forge->createTable('tracking_details');
    }

    public function down()
    {
        $this->forge->dropTable('tracking_details');
    }
}
