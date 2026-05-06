<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarcaClasificacionToProductos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nombre'     => ['type' => 'VARCHAR', 'constraint' => 150],
            'activo'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('clasificaciones', true);

        $this->forge->addColumn('productos', [
            'marca' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'tipo',
            ],
            'clasificacion_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'marca',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('productos', ['marca', 'clasificacion_id']);
        $this->forge->dropTable('clasificaciones', true);
    }
}
