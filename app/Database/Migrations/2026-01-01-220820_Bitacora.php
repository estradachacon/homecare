<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Bitacora extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID del usuario que ejecutó la acción',
            ],
            'accion' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'comment'    => 'Tipo de acción: login, create, update, delete, apertura_caja, etc.',
            ],
            'modulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nombre del módulo o área del sistema',
            ],
            'descripcion' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Descripción detallada de la acción realizada',
            ],
            'referencia_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'ID o código de referencia del registro afectado',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
                'comment'    => 'IP del usuario',
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Navegador o dispositivo',
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('bitacora_sistema');
    }

    public function down()
    {
        $this->forge->dropTable('bitacora_sistema');
    }
}
