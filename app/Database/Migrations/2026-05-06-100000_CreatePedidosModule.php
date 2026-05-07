<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePedidosModule extends Migration
{
    public function up()
    {
        // ── pedidos_head ─────────────────────────────────────────────────────
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'numero'           => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => false],
            'anio'             => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false],
            'secuencia'        => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'cliente_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'vendedor_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'tipo_documento'   => ['type' => 'ENUM', 'constraint' => ['factura', 'credito_fiscal', 'nota_remision'], 'null' => false],
            'tipo_pago'        => ['type' => 'ENUM', 'constraint' => ['contado', 'credito'], 'default' => 'contado', 'null' => false],
            'dias_credito'     => ['type' => 'SMALLINT', 'null' => true, 'default' => null],
            'notas'            => ['type' => 'TEXT', 'null' => true],
            'subtotal'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'iva'              => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'total'            => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'estado'           => ['type' => 'ENUM', 'constraint' => ['pendiente', 'facturada', 'anulada'], 'default' => 'pendiente'],
            'factura_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'anulada'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'anulada_por'      => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'fecha_anulacion'  => ['type' => 'DATETIME', 'null' => true, 'default' => null],
            'created_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('numero');
        $this->forge->addKey(['anio', 'secuencia']);
        $this->forge->createTable('pedidos_head', true);

        // ── pedidos_detalles ─────────────────────────────────────────────────
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pedido_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'producto_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'cantidad'         => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
            'precio_unitario'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => false],
            'precio_minimo'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'subtotal'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => false],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pedido_id');
        $this->forge->createTable('pedidos_detalles', true);

        // ── pedidos_log ──────────────────────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pedido_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'user_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_nombre' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'accion'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'detalle'     => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pedido_id');
        $this->forge->createTable('pedidos_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('pedidos_log',     true);
        $this->forge->dropTable('pedidos_detalles', true);
        $this->forge->dropTable('pedidos_head',    true);
    }
}
