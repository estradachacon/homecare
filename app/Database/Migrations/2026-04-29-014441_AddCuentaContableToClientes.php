<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCuentaContableToClientes extends Migration
{
    public function up()
    {
        // 🔹 Agregar columna
        $this->forge->addColumn('clientes', [
            'cuenta_contable_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'direccion',
            ],
        ]);

        // 🔹 Agregar llave foránea
        $this->db->query("
            ALTER TABLE clientes
            ADD CONSTRAINT fk_clientes_cuenta_contable
            FOREIGN KEY (cuenta_contable_id)
            REFERENCES cont_plan_cuentas(id)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ");
    }

    public function down()
    {
        // 🔹 Eliminar FK
        $this->db->query("
            ALTER TABLE clientes
            DROP FOREIGN KEY fk_clientes_cuenta_contable
        ");

        // 🔹 Eliminar columna
        $this->forge->dropColumn('clientes', 'cuenta_contable_id');
    }
}