<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsignacionHeadModel extends Model
{
    protected $table         = 'consignaciones_head';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'numero', 'vendedor_id', 'nombre', 'paciente_id', 'concepto', 'tipo_nota_id',
        'fecha', 'hora', 'fecha_generacion',
        'subtotal', 'observaciones', 'estado',
        'anulada', 'anulada_por', 'fecha_anulacion', 'created_by',
        'doctor_id', 'cliente_id',
        'aprobacion_estado', 'aprobado_por', 'aprobado_at', 'rechazo_motivo',
        'lotes_autorizados_por', 'lotes_autorizados_at',
        'origen',
    ];

    public function listar(array $filtros = [])
    {
        // Subquery: contar lotes asignados por consignación
        $lotesSub = '(SELECT COUNT(cdl.id)
                       FROM consignacion_detalle_lotes cdl
                       INNER JOIN consignaciones_detalles cd ON cd.id = cdl.detalle_id
                       WHERE cd.consignacion_id = consignaciones_head.id)';

        $this->select("consignaciones_head.*,
                        sellers.seller AS vendedor_nombre,
                        {$lotesSub} AS lotes_asignados_count")
             ->join('sellers', 'sellers.id = consignaciones_head.vendedor_id', 'left');

        if (isset($filtros['vendedor_id']) && $filtros['vendedor_id'] !== '' && $filtros['vendedor_id'] !== null) {
            $this->where('consignaciones_head.vendedor_id', (int)$filtros['vendedor_id']);
        }
        if (!empty($filtros['estado'])) {
            $this->where('consignaciones_head.estado', $filtros['estado']);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $this->where('DATE(consignaciones_head.fecha) >=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $this->where('DATE(consignaciones_head.fecha) <=', $filtros['fecha_fin']);
        }

        // Filtro por estado de lotes
        switch ($filtros['lote_estado'] ?? '') {
            case 'sin_autorizar':
                $this->where('consignaciones_head.lotes_autorizados_por IS NULL');
                $this->where('consignaciones_head.estado', 'abierta');
                break;
            case 'pendiente_lotes':
                $this->where('consignaciones_head.lotes_autorizados_por IS NOT NULL');
                $this->where("{$lotesSub} = 0");
                $this->where('consignaciones_head.estado', 'abierta');
                break;
            case 'lotes_ok':
                $this->where("{$lotesSub} > 0");
                break;
        }

        if (!empty($filtros['origen'])) {
            $this->where('consignaciones_head.origen', $filtros['origen']);
        }

        if (!empty($filtros['aprobacion'])) {
            $this->where('consignaciones_head.aprobacion_estado', $filtros['aprobacion']);
        }

        return $this->orderBy('consignaciones_head.id', 'DESC');
    }

    public function getConVendedor(int $id)
    {
        return $this->select('
                consignaciones_head.*,
                sellers.seller    AS vendedor_nombre,
                doctores.nombre   AS doctor_nombre,
                clientes.nombre   AS cliente_nombre,
                pacientes.nombre  AS paciente_nombre,
                u_auth.user_name  AS autorizador_nombre,
                tn.nombre         AS tipo_nota_nombre
            ')
            ->join('sellers',      'sellers.id = consignaciones_head.vendedor_id',              'left')
            ->join('doctores',     'doctores.id = consignaciones_head.doctor_id',               'left')
            ->join('clientes',     'clientes.id = consignaciones_head.cliente_id',              'left')
            ->join('pacientes',    'pacientes.id = consignaciones_head.paciente_id',            'left')
            ->join('users u_auth', 'u_auth.id = consignaciones_head.lotes_autorizados_por',    'left')
            ->join('tipo_notas tn','tn.id = consignaciones_head.tipo_nota_id',                 'left')
            ->where('consignaciones_head.id', $id)
            ->first();
    }

    public function siguienteNumero(): string
    {
        $db  = \Config\Database::connect();
        $row = $db->table('consignaciones_head')
            ->selectMax('id')
            ->get()->getRow();

        $next = ($row->id ?? 0) + 1;

        return 'NE-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
