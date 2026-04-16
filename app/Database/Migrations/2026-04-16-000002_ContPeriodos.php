<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContPeriodos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'auto_increment' => true],
            'anio'              => ['type' => 'SMALLINT'],
            'mes'               => ['type' => 'TINYINT'],
            'estado'            => ['type' => 'ENUM', 'constraint' => ['ABIERTO','CERRADO'], 'default' => 'ABIERTO'],
            'fecha_apertura'    => ['type' => 'DATE', 'null' => true],
            'fecha_cierre'      => ['type' => 'DATE', 'null' => true],
            'usuario_cierre_id' => ['type' => 'INT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['anio', 'mes']);
        $this->forge->createTable('cont_periodos');
    }

    public function down()
    {
        $this->forge->dropTable('cont_periodos');
    }
}
