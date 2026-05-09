<?php

namespace App\Models;

use CodeIgniter\Model;

class ContAsientosHeadModel extends Model
{
    protected $table         = 'cont_asientos_head';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'numero_asiento', 'numero_partida', 'fecha', 'descripcion', 'tipo', 'tipo_partida_id', 'estado',
        'periodo_id', 'total_debe', 'total_haber', 'referencia',
        'usuario_id', 'usuario_aprueba_id', 'fecha_aprobacion', 'motivo_anulacion',
    ];

    public function getSiguienteNumero(): int
    {
        $db = \Config\Database::connect();
        $row = $db->query('SELECT COALESCE(MAX(numero_asiento),0)+1 AS siguiente FROM cont_asientos_head')->getRow();
        return (int)($row->siguiente ?? 1);
    }

    public function getSiguienteNumeroPartida(int $tipoPartidaId, int $anio): int
    {
        $db  = \Config\Database::connect();
        $row = $db->query(
            'SELECT COALESCE(MAX(numero_partida), 0) + 1 AS siguiente
             FROM cont_asientos_head
             WHERE tipo_partida_id = ? AND YEAR(fecha) = ?',
            [$tipoPartidaId, $anio]
        )->getRow();
        return (int)($row->siguiente ?? 1);
    }

    public function buscarPartidaDia(int $tipoPartidaId, string $fecha): ?object
    {
        return $this->where('tipo_partida_id', $tipoPartidaId)
                    ->where('fecha', $fecha)
                    ->where('estado !=', 'ANULADO')
                    ->first();
    }

    public function getConDetalle(int $id)
    {
        return $this->select('cont_asientos_head.*, cont_periodos.anio, cont_periodos.mes, users.user_name AS usuario_nombre, tp.nombre AS tipo_partida_nombre')
                    ->join('cont_periodos', 'cont_periodos.id = cont_asientos_head.periodo_id', 'left')
                    ->join('users', 'users.id = cont_asientos_head.usuario_id', 'left')
                    ->join('cont_tipos_partida tp', 'tp.id = cont_asientos_head.tipo_partida_id', 'left')
                    ->where('cont_asientos_head.id', $id)
                    ->first();
    }

    /**
     * Registra saldos e histórico para un asiento ya insertado.
     * $lineas acepta arrays (desde JSON) u objetos (desde getByAsiento).
     */
    public function aprobarConSaldos(int $asientoId, $lineas, int $periodoId, string $fecha, string $descripcion, string $tipo, object $periodo): void
    {
        $saldosModel = new \App\Models\ContSaldosCuentasModel();
        $histModel   = new \App\Models\ContTransaccionesHistModel();
        $db          = \Config\Database::connect();

        foreach ($lineas as $l) {
            $cuentaId = (int)(is_array($l) ? $l['cuenta_id'] : $l->cuenta_id);
            $debe     = (float)(is_array($l) ? ($l['debe']  ?? 0) : $l->debe);
            $haber    = (float)(is_array($l) ? ($l['haber'] ?? 0) : $l->haber);
            $desc     = is_array($l) ? ($l['descripcion'] ?? $descripcion) : ($l->descripcion ?: $descripcion);

            $saldosModel->upsert($cuentaId, $periodoId, $debe, $haber);

            $saldoAcum = (float)($db->query(
                'SELECT COALESCE(SUM(debe)-SUM(haber),0) AS s FROM cont_transacciones_hist WHERE cuenta_id=?',
                [$cuentaId]
            )->getRow()->s ?? 0);

            $histModel->insert([
                'asiento_id'      => $asientoId,
                'cuenta_id'       => $cuentaId,
                'fecha'           => $fecha,
                'descripcion'     => $desc,
                'debe'            => $debe,
                'haber'           => $haber,
                'saldo_acumulado' => $saldoAcum + $debe - $haber,
                'anio'            => $periodo->anio,
                'mes'             => $periodo->mes,
                'tipo_asiento'    => $tipo,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function getByPeriodo(int $periodoId)
    {
        return $this->where('periodo_id', $periodoId)
                    ->where('estado !=', 'ANULADO')
                    ->orderBy('numero_asiento', 'ASC')
                    ->findAll();
    }

    public function getListadoFiltrado(array $filtros, int $perPage = 25)
    {
        $q = $this->select('cont_asientos_head.*, cont_periodos.anio, cont_periodos.mes, tp.nombre AS tipo_partida_nombre')
                  ->join('cont_periodos', 'cont_periodos.id = cont_asientos_head.periodo_id', 'left')
                  ->join('cont_tipos_partida tp', 'tp.id = cont_asientos_head.tipo_partida_id', 'left');

        if (!empty($filtros['periodo_id'])) {
            $q->where('cont_asientos_head.periodo_id', $filtros['periodo_id']);
        }
        if (!empty($filtros['tipo'])) {
            $q->where('cont_asientos_head.tipo', $filtros['tipo']);
        }
        if (!empty($filtros['estado'])) {
            $q->where('cont_asientos_head.estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $q->where('cont_asientos_head.fecha >=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $q->where('cont_asientos_head.fecha <=', $filtros['fecha_hasta']);
        }
        if (!empty($filtros['descripcion'])) {
            $q->like('cont_asientos_head.descripcion', $filtros['descripcion']);
        }

        return $q->orderBy('cont_asientos_head.numero_asiento', 'DESC')
                 ->orderBy('cont_asientos_head.id', 'DESC')
                 ->paginate($perPage);
    }
}
