<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsReadTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'notification_id' => [
                'type' => 'INT'
            ],
            'user_id' => [
                'type' => 'INT'
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        // Primary key
        $this->forge->addKey('id', true);

        // Índice único (evita duplicados)
        $this->forge->addUniqueKey(['notification_id', 'user_id']);

        $this->forge->createTable('notifications_read');
    }

    public function down()
    {
        $this->forge->dropTable('notifications_read');
    }
}