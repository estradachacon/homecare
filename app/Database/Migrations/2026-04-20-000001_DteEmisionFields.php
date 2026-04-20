<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DteEmisionFields extends Migration
{
    public function up()
    {
        // Ampliar restricciones de varchar en emisor (originalmente varchar(20) truncaba datos)
        $this->forge->modifyColumn('emisor', [
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 250,
                'null'       => false,
            ],
            'desc_actividad' => [
                'type'       => 'VARCHAR',
                'constraint' => 250,
                'null'       => false,
            ],
            'nombre_comercial' => [
                'type'       => 'VARCHAR',
                'constraint' => 250,
                'null'       => false,
            ],
            'tipo_establecimiento' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'complemento' => [
                'type'       => 'VARCHAR',
                'constraint' => 250,
                'null'       => false,
            ],
        ]);

        // Marcar facturas emitidas por el sistema vs. recibidas de proveedores/importación
        $this->forge->addColumn('facturas_head', [
            'emitido' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'after'      => 'tipo_dte',
            ],
            'estado_mh' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
                'after'      => 'emitido',
            ],
            'respuesta_mh' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'estado_mh',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('facturas_head', ['emitido', 'estado_mh', 'respuesta_mh']);
    }
}
