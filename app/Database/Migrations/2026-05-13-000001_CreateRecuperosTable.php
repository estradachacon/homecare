<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecuperosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'numero_recupero'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'cliente_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'fecha'            => ['type' => 'DATE', 'null' => false],
            'forma_cobro'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false, 'default' => 'efectivo'],
            'referencia'       => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'total'            => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => false, 'default' => '0.00'],
            'observaciones'    => ['type' => 'TEXT', 'null' => true],
            'estado'           => ['type' => 'ENUM', 'constraint' => ['ACTIVO', 'ANULADO'], 'default' => 'ACTIVO'],
            'anulado_por'      => ['type' => 'INT', 'null' => true],
            'fecha_anulacion'  => ['type' => 'DATETIME', 'null' => true],
            'motivo_anulacion' => ['type' => 'TEXT', 'null' => true],
            'usuario_id'       => ['type' => 'INT', 'null' => false],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('numero_recupero');
        $this->forge->addKey('cliente_id');
        $this->forge->createTable('recuperos');
    }

    public function down()
    {
        $this->forge->dropTable('recuperos');
    }
}
