<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContPlanCuentas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'auto_increment' => true],
            'codigo'             => ['type' => 'VARCHAR', 'constraint' => 20],
            'nombre'             => ['type' => 'VARCHAR', 'constraint' => 150],
            'tipo'               => ['type' => 'ENUM', 'constraint' => ['ACTIVO','PASIVO','CAPITAL','INGRESO','COSTO','GASTO']],
            'naturaleza'         => ['type' => 'ENUM', 'constraint' => ['DEUDORA','ACREEDORA']],
            'nivel'              => ['type' => 'TINYINT', 'default' => 1],
            'cuenta_padre_id'    => ['type' => 'INT', 'null' => true],
            'acepta_movimientos' => ['type' => 'TINYINT', 'default' => 0],
            'activo'             => ['type' => 'TINYINT', 'default' => 1],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('codigo');
        $this->forge->createTable('cont_plan_cuentas');
    }

    public function down()
    {
        $this->forge->dropTable('cont_plan_cuentas');
    }
}
