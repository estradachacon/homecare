<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Quita la restricción única (tipo_documento, numero_documento) de `clientes`.
 *
 * Una misma empresa (mismo DUI/NIT) puede tener varias sucursales registradas
 * como fichas de cliente distintas para llevar por separado lo que debe cada
 * una. La app ya avisa en el modal de creación cuando el documento se repite
 * y dónde, pero el candado a nivel de base de datos igual rechazaba el INSERT
 * aunque el usuario confirmara que quería continuar.
 */
class DropUniqueDocumentoClientes extends Migration
{
    public function up()
    {
        $this->forge->dropKey('clientes', 'tipo_documento_numero_documento');
    }

    public function down()
    {
        $this->db->query(
            'ALTER TABLE clientes ADD UNIQUE KEY tipo_documento_numero_documento (tipo_documento, numero_documento)'
        );
    }
}
