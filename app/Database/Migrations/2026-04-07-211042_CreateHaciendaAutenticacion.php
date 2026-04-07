<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHaciendaAutenticacion extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ambiente' => [
                'type'       => 'ENUM',
                'constraint' => ['test', 'prod'],
            ],
            'nit' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'token' => [
                'type' => 'LONGTEXT',
                'null' => false,
            ],
            'token_expira_en' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'ultimo_login' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'expirado', 'error'],
                'default'    => 'activo',
            ],
            'http_code' => [
                'type' => 'INT',
                'null' => true,
            ],
            'error_mensaje' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'respuesta_raw' => [
                'type' => 'LONGTEXT',
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

        $this->forge->addKey('id', true);

        // 🔥 Este índice es CLAVE para rendimiento
        $this->forge->addKey(['ambiente', 'nit']);

        $this->forge->createTable('hacienda_autenticacion');
    }

    public function down()
    {
        $this->forge->dropTable('hacienda_autenticacion');
    }
}