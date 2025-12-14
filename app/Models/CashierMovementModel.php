<?php

namespace App\Models;

use CodeIgniter\Model;

class CashierMovementModel extends Model
{
    protected $table            = 'cashier_movements';
    protected $primaryKey       = 'id';

    protected $allowedFields = [
        'cashier_id',
        'cashier_session_id',
        'user_id',
        'branch_id',
        'type',
        'amount',
        'balance_after',
        'concept',
        'reference_type',
        'reference_id',
        'created_at',
    ];

    protected $returnType = 'array';

    // 🕒 timestamps manuales (porque created_at se setea desde lógica de negocio)
    protected $useTimestamps = false;

    // 🛡️ Reglas básicas de validación (opcional pero recomendado)
    protected $validationRules = [
        'cashier_id'         => 'required|is_natural_no_zero',
        'cashier_session_id' => 'required|is_natural_no_zero',
        'user_id'            => 'required|is_natural_no_zero',
        'branch_id'          => 'required|is_natural_no_zero',
        'type'               => 'required|in_list[in,out]',
        'amount'             => 'required|decimal',
        'balance_after'      => 'required|decimal',
        'concept'            => 'required|string|max_length[255]',
    ];

    protected $validationMessages = [
        'type' => [
            'in_list' => 'El tipo de movimiento debe ser entrada (in) o salida (out).',
        ],
    ];
}