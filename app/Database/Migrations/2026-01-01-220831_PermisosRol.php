<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PermisosRol extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
            ],
            'nombre_accion' => [
                'type' => 'VARCHAR',
                'constraint' => '100', // Estructura == 'modulo/accion', remuneraciones/pago
            ],
            'habilitado' => [
                'type' => 'TINYINT', 
                'constraint' => 1,
                'default' => 0, // Por defecto, deshabilitaré el permiso
            ],
        ]);
        $this->forge->addPrimaryKey('id');

        // 
        // Aquí defino la Clave Foránea a la tabla 'roles' Si se borra un Rol, todas sus definiciones de permiso se irán ('CASCADE')
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');

        // Creamos un índice único compuesto: un Rol solo puede tener una configuración por Acción
        $this->forge->addUniqueKey(['role_id', 'nombre_accion']);

        $this->forge->createTable('permisos_rol');
    }

    public function down()
    {
        $this->forge->dropTable('permisos_rol');
    }
}
