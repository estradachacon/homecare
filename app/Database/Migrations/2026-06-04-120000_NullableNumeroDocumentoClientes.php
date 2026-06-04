<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NullableNumeroDocumentoClientes extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE clientes MODIFY COLUMN numero_documento VARCHAR(50) NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE clientes MODIFY COLUMN tipo_documento  VARCHAR(20) NULL DEFAULT NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE clientes MODIFY COLUMN numero_documento VARCHAR(50) NOT NULL");
        $this->db->query("ALTER TABLE clientes MODIFY COLUMN tipo_documento  VARCHAR(20) NOT NULL");
    }
}
