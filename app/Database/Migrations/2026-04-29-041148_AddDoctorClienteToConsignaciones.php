<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDoctorClienteToConsignaciones extends Migration
{
    public function up()
    {
        // Tabla catálogo de doctores
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'especialidad' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('doctores', true);

        // Campos nuevos en consignaciones_head
        $this->forge->addColumn('consignaciones_head', [
            'doctor_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'vendedor_id',
            ],
            'cliente_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'doctor_id',
            ],
        ]);

        // Foreign keys
        $this->db->query("
            ALTER TABLE consignaciones_head
            ADD CONSTRAINT fk_consignaciones_doctor
            FOREIGN KEY (doctor_id)
            REFERENCES doctores(id)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ");

        $this->db->query("
            ALTER TABLE consignaciones_head
            ADD CONSTRAINT fk_consignaciones_cliente
            FOREIGN KEY (cliente_id)
            REFERENCES clientes(id)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE consignaciones_head DROP FOREIGN KEY fk_consignaciones_doctor");
        $this->db->query("ALTER TABLE consignaciones_head DROP FOREIGN KEY fk_consignaciones_cliente");

        $this->forge->dropColumn('consignaciones_head', 'doctor_id');
        $this->forge->dropColumn('consignaciones_head', 'cliente_id');

        $this->forge->dropTable('doctores', true);
    }
}