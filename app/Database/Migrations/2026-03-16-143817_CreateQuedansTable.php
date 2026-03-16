<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuedansTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'numero_quedan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'cliente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            'fecha_emision' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'fecha_pago' => [
                'type' => 'DATE',
                'null' => true,
            ],
            
            'total_aplicado' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
                'after'      => 'fecha_pago'
            ],

            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'anulado' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ],

            'anulado_por' => [
                'type' => 'INT',
                'null' => true
            ],
            
            'fecha_anulacion' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('cliente_id');

        $this->forge->createTable('quedans');
    }

    public function down()
    {
        $this->forge->dropTable('quedans');
    }
}
