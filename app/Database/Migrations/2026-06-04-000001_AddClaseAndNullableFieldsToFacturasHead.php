<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClaseAndNullableFieldsToFacturasHead extends Migration
{
    public function up()
    {
        // Agregar columna 'clase' para distinguir DTE de facturas tradicionales
        $this->forge->addColumn('facturas_head', [
            'clase' => [
                'type'       => "ENUM('DTE','TRADICIONAL')",
                'default'    => 'DTE',
                'null'       => false,
                'after'      => 'id',
            ],
        ]);

        // Hacer nullable los campos que no aplican a facturas tradicionales
        $this->db->query("ALTER TABLE facturas_head
            MODIFY COLUMN ambiente          VARCHAR(5)   NULL,
            MODIFY COLUMN codigo_generacion CHAR(36)     NULL,
            MODIFY COLUMN hora_emision      TIME         NULL,
            MODIFY COLUMN tipo_moneda       VARCHAR(10)  NULL DEFAULT 'USD',
            MODIFY COLUMN firma_electronica LONGTEXT     NULL
        ");

        // Backfill: todas las existentes son DTE
        $this->db->query("UPDATE facturas_head SET clase = 'DTE' WHERE clase IS NULL OR clase = ''");
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', 'clase');

        $this->db->query("ALTER TABLE facturas_head
            MODIFY COLUMN ambiente          VARCHAR(5)   NOT NULL,
            MODIFY COLUMN codigo_generacion CHAR(36)     NOT NULL,
            MODIFY COLUMN hora_emision      TIME         NOT NULL,
            MODIFY COLUMN tipo_moneda       VARCHAR(10)  NOT NULL,
            MODIFY COLUMN firma_electronica LONGTEXT     NOT NULL
        ");
    }
}
