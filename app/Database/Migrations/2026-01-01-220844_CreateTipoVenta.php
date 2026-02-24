<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTipoVenta extends Migration
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

            'nombre_tipo_venta' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false
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
        $this->forge->createTable('tipo_venta');

        // 👇 INSERT AUTOMÁTICO DEL TIPO BASE
        $this->db->table('tipo_venta')->insert([
            'id' => 1,
            'nombre_tipo_venta' => 'Privado',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tipo_venta');
    }
}
