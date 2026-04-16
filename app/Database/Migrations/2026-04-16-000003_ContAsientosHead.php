<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContAsientosHead extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'auto_increment' => true],
            'numero_asiento'       => ['type' => 'INT'],
            'fecha'                => ['type' => 'DATE'],
            'descripcion'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'tipo'                 => ['type' => 'ENUM', 'constraint' => ['DIARIO','AJUSTE','CIERRE','APERTURA'], 'default' => 'DIARIO'],
            'estado'               => ['type' => 'ENUM', 'constraint' => ['BORRADOR','APROBADO','ANULADO'], 'default' => 'BORRADOR'],
            'periodo_id'           => ['type' => 'INT'],
            'total_debe'           => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'total_haber'          => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
            'referencia'           => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'usuario_id'           => ['type' => 'INT', 'null' => true],
            'usuario_aprueba_id'   => ['type' => 'INT', 'null' => true],
            'fecha_aprobacion'     => ['type' => 'DATETIME', 'null' => true],
            'motivo_anulacion'     => ['type' => 'TEXT', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('periodo_id');
        $this->forge->addKey('fecha');
        $this->forge->createTable('cont_asientos_head');
    }

    public function down()
    {
        $this->forge->dropTable('cont_asientos_head');
    }
}
