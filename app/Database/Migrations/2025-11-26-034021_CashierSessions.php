<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CashierSessions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cashier_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'comment'        => 'FK a la caja física o lógica (tabla cashiers)',
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'comment'        => 'FK al usuario que abrió/está operando la caja',
            ],
            'branch_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'comment'        => 'Sucursal para redundancia y búsquedas rápidas',
            ],
            'initial_amount' => [
                'type'           => 'DECIMAL',
                'constraint'     => '20,2',
                'default'        => 0.00,
                'comment'        => 'Monto con el que se abre la caja (la reserva)',
            ],
            'closing_amount' => [
                'type'           => 'DECIMAL',
                'constraint'     => '20,2',
                'null'           => true,
                'comment'        => 'Monto físico reportado al cerrar la caja',
            ],
            'status' => [
                'type'           => 'ENUM',
                'constraint'     => ['open', 'closed', 'pending_close'],
                'default'        => 'open',
                'comment'        => 'Estado de la sesión/turno',
            ],
            'open_time' => [
                'type'           => 'DATETIME',
                'comment'        => 'Fecha y hora de apertura',
            ],
            'close_time' => [
                'type'           => 'DATETIME',
                'null'           => true,
                'comment'        => 'Fecha y hora de cierre',
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('cashier_sessions');
    }

    public function down()
    {
        $this->forge->dropTable('cashier_sessions');
    }
}