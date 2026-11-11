<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Permite fijar explícitamente de qué nota de envío sale una línea de NP,
 * para las líneas que se reconstruyen desde la herramienta de "corregir
 * vinculación NE" (pedidos/editar-vinculacion). Las NP creadas normalmente
 * en /pedidos/crear no llenan este campo y siguen resolviéndose con el
 * cálculo FIFO existente (_consumoLotesNPPorProducto).
 */
class AddConsignacionIdToPedidosDetalles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pedidos_detalles', [
            'consignacion_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'producto_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pedidos_detalles', 'consignacion_id');
    }
}
