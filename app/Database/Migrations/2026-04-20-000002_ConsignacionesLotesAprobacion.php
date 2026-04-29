<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConsignacionesLotesAprobacion extends Migration
{
    public function up(): void
    {
        // Catálogo de lotes por producto
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'producto_id'      => ['type' => 'INT', 'unsigned' => true],
            'numero_lote'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'fecha_vencimiento'=> ['type' => 'DATE', 'null' => true],
            'descripcion'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'activo'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['producto_id', 'numero_lote']);
        $this->forge->createTable('consignacion_lotes', true);

        // Asignación de lotes a líneas de consignación
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'detalle_id' => ['type' => 'INT', 'unsigned' => true],
            'lote_id'    => ['type' => 'INT', 'unsigned' => true],
            'cantidad'   => ['type' => 'DECIMAL', 'constraint' => '12,4', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('detalle_id');
        $this->forge->createTable('consignacion_detalle_lotes', true);

        // Campos de aprobación en consignaciones_head
        $fields = [
            'aprobacion_estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'aprobada', 'rechazada'],
                'default'    => 'pendiente',
                'after'      => 'estado',
            ],
            'aprobado_por' => ['type' => 'INT', 'null' => true, 'after' => 'aprobacion_estado'],
            'aprobado_at'  => ['type' => 'DATETIME', 'null' => true, 'after' => 'aprobado_por'],
            'rechazo_motivo' => ['type' => 'TEXT', 'null' => true, 'after' => 'aprobado_at'],
        ];
        $this->forge->addColumn('consignaciones_head', $fields);
    }

    public function down(): void
    {
        $this->forge->dropTable('consignacion_detalle_lotes', true);
        $this->forge->dropTable('consignacion_lotes', true);
        $this->forge->dropColumn('consignaciones_head', ['aprobacion_estado', 'aprobado_por', 'aprobado_at', 'rechazo_motivo']);
    }
}
