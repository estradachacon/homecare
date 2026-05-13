<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContRemesas extends Migration
{
    public function up()
    {
        // Cabecera de remesa contable
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'numero_remesa'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'fecha'            => ['type' => 'DATE', 'null' => false],
            'descripcion'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'tipo_partida_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'total'            => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => false, 'default' => 0],
            'estado'           => [
                'type'       => 'ENUM',
                'constraint' => ['ACTIVO', 'CERRADO', 'ANULADO'],
                'null'       => false,
                'default'    => 'ACTIVO',
            ],
            'observaciones'    => ['type' => 'TEXT', 'null' => true],
            'usuario_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'anulado_por'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'fecha_anulacion'  => ['type' => 'DATETIME', 'null' => true],
            'motivo_anulacion' => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('fecha');
        $this->forge->addKey('estado');
        $this->forge->createTable('cont_remesas_head');

        // Detalle: asientos incluidos en la remesa
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'remesa_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'asiento_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'monto'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => false, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('remesa_id');
        $this->forge->addKey('asiento_id');
        $this->forge->createTable('cont_remesas_detalle');
    }

    public function down()
    {
        $this->forge->dropTable('cont_remesas_detalle', true);
        $this->forge->dropTable('cont_remesas_head', true);
    }
}
