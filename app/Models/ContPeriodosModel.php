<?php

namespace App\Models;

use CodeIgniter\Model;

class ContPeriodosModel extends Model
{
    protected $table         = 'cont_periodos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'anio', 'mes', 'estado', 'fecha_apertura',
        'fecha_cierre', 'usuario_cierre_id',
    ];

    public function getPeriodoActual()
    {
        return $this->where('estado', 'ABIERTO')
                    ->orderBy('anio', 'DESC')
                    ->orderBy('mes', 'DESC')
                    ->first();
    }

    public function getPeriodosPorAnio(int $anio)
    {
        return $this->where('anio', $anio)
                    ->orderBy('mes', 'ASC')
                    ->findAll();
    }

    public function existePeriodo(int $anio, int $mes): bool
    {
        return $this->where('anio', $anio)->where('mes', $mes)->countAllResults() > 0;
    }

    public function getPeriodoByAnioMes(int $anio, int $mes)
    {
        return $this->where('anio', $anio)->where('mes', $mes)->first();
    }

    public function getAniosDisponibles(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->query('SELECT DISTINCT anio FROM cont_periodos ORDER BY anio DESC')->getResultArray();
        return array_column($rows, 'anio');
    }
}
