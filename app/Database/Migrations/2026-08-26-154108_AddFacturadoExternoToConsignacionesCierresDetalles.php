<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Nuevo "bucket" en el cierre de una nota de envío: registra cuando un
 * producto se facturó en OTRA empresa, en un sistema totalmente aparte
 * (p.ej. "CCF 00500 SD"). Se distribuye exactamente igual que devolución
 * (documento, lote, fecha, foto, comentario) y entra en la validación de
 * que la suma de todo cuadre con la cantidad original de la línea.
 */
class AddFacturadoExternoToConsignacionesCierresDetalles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('consignaciones_cierres_detalles', [
            'cantidad_facturada_externa' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'cantidad_stock_vendedor',
            ],
            'doc_factura_externa' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'cantidad_facturada_externa',
            ],
            'lote_factura_externa' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'doc_factura_externa',
            ],
            'fecha_factura_externa' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'lote_factura_externa',
            ],
            'foto_factura_externa' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'fecha_factura_externa',
            ],
            'comentario_factura_externa' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'foto_factura_externa',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('consignaciones_cierres_detalles', [
            'cantidad_facturada_externa',
            'doc_factura_externa',
            'lote_factura_externa',
            'fecha_factura_externa',
            'foto_factura_externa',
            'comentario_factura_externa',
        ]);
    }
}
