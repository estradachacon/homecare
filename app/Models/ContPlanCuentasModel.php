<?php

namespace App\Models;

use CodeIgniter\Model;

class ContPlanCuentasModel extends Model
{
    protected $table         = 'cont_plan_cuentas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'codigo', 'nombre', 'tipo', 'naturaleza',
        'nivel', 'cuenta_padre_id', 'acepta_movimientos', 'activo',
    ];

    // Árbol completo ordenado por código
    public function getArbol()
    {
        return $this->orderBy('codigo', 'ASC')->findAll();
    }

    // Solo cuentas que aceptan movimientos (hojas)
    public function getCuentasMovimiento()
    {
        return $this->where('acepta_movimientos', 1)
                    ->where('activo', 1)
                    ->orderBy('codigo', 'ASC')
                    ->findAll();
    }

    // Cuentas por tipo
    public function getCuentasPorTipo(string $tipo)
    {
        return $this->where('tipo', $tipo)
                    ->where('acepta_movimientos', 1)
                    ->where('activo', 1)
                    ->orderBy('codigo', 'ASC')
                    ->findAll();
    }

    // Select2 AJAX
    public function buscarParaSelect2(string $q)
    {
        return $this->select('id, codigo, nombre')
                    ->where('acepta_movimientos', 1)
                    ->where('activo', 1)
                    ->groupStart()
                        ->like('codigo', $q)
                        ->orLike('nombre', $q)
                    ->groupEnd()
                    ->orderBy('codigo', 'ASC')
                    ->findAll(30);
    }

    // Verificar si una cuenta tiene hijos
    public function tieneHijos(int $id): bool
    {
        return $this->where('cuenta_padre_id', $id)->countAllResults() > 0;
    }

    // Construir árbol jerárquico en PHP
    public function construirArbol(array $cuentas, ?int $padreId = null): array
    {
        $rama = [];
        foreach ($cuentas as $c) {
            if ($c->cuenta_padre_id == $padreId) {
                $c->hijos = $this->construirArbol($cuentas, $c->id);
                $rama[] = $c;
            }
        }
        return $rama;
    }
}
