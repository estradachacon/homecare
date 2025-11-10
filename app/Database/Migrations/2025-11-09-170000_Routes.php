<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Routes extends Migration
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
            'route_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->createTable('routes');
                $data = [
            [
                'route_name'         => 'Occidente',
                'description'        => 'Zona occidental',
            ],
            [
                'route_name'         => 'Oriente',
                'description'        => 'Zona oriental',
            ],
            [
                'route_name'         => 'Norte',
                'description'        => 'Zona norte',
            ],
            [
                'route_name'         => 'Sur',
                'description'        => 'Zona sur',
            ],
        ];

        // 🚨 CAMBIO CLAVE: Usamos insertBatch() para inserción múltiple.
        $this->db->table('routes')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('routes');
    }
}
