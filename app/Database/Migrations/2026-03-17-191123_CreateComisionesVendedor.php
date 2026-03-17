<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComisionesVendedor extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true
            ],
            'vendedor_id' => [
                'type' => 'INT'
            ],
            'porcentaje' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2'
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('vendedor_id'); // 👈 importante
        $this->forge->createTable('comisiones_vendedor');
    }

    public function down()
    {
        $this->forge->dropTable('comisiones_vendedor');
    }
}
