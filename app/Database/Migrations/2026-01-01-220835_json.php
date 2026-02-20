<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Json extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'factura_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'json_original' => [
                'type' => 'LONGTEXT',
            ],

            'created_at DATETIME default CURRENT_TIMESTAMP',

        ]);

        $this->forge->addKey('id', true);

        // Relación 1 a 1
        $this->forge->addUniqueKey('factura_id');

        $this->forge->createTable('facturas_json');
    }

    public function down()
    {
        $this->forge->dropTable('facturas_json');
    }
}
