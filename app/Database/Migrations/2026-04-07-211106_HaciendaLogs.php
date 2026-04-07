<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHaciendaLogs extends Migration
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
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['auth', 'envio_dte', 'consulta', 'evento'],
            ],
            'endpoint' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'request_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'response_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'http_code' => [
                'type' => 'INT',
                'null' => true,
            ],
            'exito' => [
                'type' => 'BOOLEAN',
                'default' => 1,
            ],
            'mensaje_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fecha' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);

        // 🔥 Índices útiles para consultas
        $this->forge->addKey(['nit', 'ambiente']);
        $this->forge->addKey('tipo');
        $this->forge->addKey('fecha');

        $this->forge->createTable('hacienda_logs');
    }

    public function down()
    {
        $this->forge->dropTable('hacienda_logs');
    }
}