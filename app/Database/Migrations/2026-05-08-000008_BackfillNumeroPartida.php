<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillNumeroPartida extends Migration
{
    public function up()
    {
        // Leer registros sin numero_partida, ordenados para asignar correlativo
        $rows = $this->db->query("
            SELECT id, tipo_partida_id, YEAR(fecha) AS anio
            FROM cont_asientos_head
            WHERE tipo_partida_id IS NOT NULL
              AND numero_partida  IS NULL
            ORDER BY tipo_partida_id, YEAR(fecha), fecha, numero_asiento
        ")->getResultArray();

        $counters = [];
        foreach ($rows as $row) {
            $key = $row['tipo_partida_id'] . '-' . $row['anio'];
            $counters[$key] = ($counters[$key] ?? 0) + 1;
            $this->db->query(
                'UPDATE cont_asientos_head SET numero_partida = ? WHERE id = ?',
                [$counters[$key], $row['id']]
            );
        }
    }

    public function down()
    {
        // No revertir: un null no aporta información, el backfill es inocuo.
    }
}
