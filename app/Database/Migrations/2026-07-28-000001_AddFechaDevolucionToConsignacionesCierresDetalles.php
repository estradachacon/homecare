<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFechaDevolucionToConsignacionesCierresDetalles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('consignaciones_cierres_detalles', [
            'fecha_devolucion' => [
                'type'     => 'DATE',
                'null'     => true,
                'default'  => null,
                'after'    => 'cantidad_devuelta',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('consignaciones_cierres_detalles', 'fecha_devolucion');
    }
}
