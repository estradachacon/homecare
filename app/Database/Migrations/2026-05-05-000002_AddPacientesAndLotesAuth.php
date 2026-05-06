<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPacientesAndLotesAuth extends Migration
{
    public function up()
    {
        // Tabla pacientes
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'         => ['type' => 'VARCHAR', 'constraint' => 200],
            'identificacion' => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'telefono'       => ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'correo'         => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'activo'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pacientes');

        // Tabla log de consignaciones
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'consignacion_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_nombre'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'accion'          => ['type' => 'VARCHAR', 'constraint' => 80],
            'detalle'         => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('consignaciones_log');

        // Nuevas columnas en consignaciones_head
        $this->forge->addColumn('consignaciones_head', [
            'paciente_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'nombre'],
            'lotes_autorizados_por' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'estado'],
            'lotes_autorizados_at'  => ['type' => 'DATETIME', 'null' => true, 'after' => 'lotes_autorizados_por'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('consignaciones_head', ['paciente_id', 'lotes_autorizados_por', 'lotes_autorizados_at']);
        $this->forge->dropTable('consignaciones_log', true);
        $this->forge->dropTable('pacientes', true);
    }
}
