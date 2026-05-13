<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterRecuperosAddAplicado extends Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE recuperos
             MODIFY COLUMN estado ENUM('ACTIVO','ANULADO','APLICADO') NOT NULL DEFAULT 'ACTIVO',
             ADD COLUMN pago_id INT NULL DEFAULT NULL AFTER motivo_anulacion"
        );
    }

    public function down()
    {
        $this->db->query("ALTER TABLE recuperos DROP COLUMN pago_id");
        $this->db->query(
            "ALTER TABLE recuperos
             MODIFY COLUMN estado ENUM('ACTIVO','ANULADO') NOT NULL DEFAULT 'ACTIVO'"
        );
    }
}
