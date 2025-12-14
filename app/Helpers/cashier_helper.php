<?php

use App\Models\CashierMovementModel;

function registerCashierMovement(array $data): bool
{
    $db = db_connect();

    // 🔒 Validaciones mínimas
    $required = ['cashier_id', 'cashier_session_id', 'type', 'amount', 'concept'];

    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Falta el campo requerido: {$field}");
        }
    }

    if (!in_array($data['type'], ['in', 'out'])) {
        throw new Exception('Tipo de movimiento inválido');
    }

    // 🔹 Obtener caja actual
    $cashier = $db->table('cashier')
        ->where('id', $data['cashier_id'])
        ->get()
        ->getRowArray();

    if (!$cashier) {
        throw new Exception('Caja no encontrada');
    }

    $currentBalance = (float) $cashier['current_balance'];
    $amount = (float) $data['amount'];

    if ($amount <= 0) {
        throw new Exception('El monto debe ser mayor a 0');
    }

    // 🔻 Salida de dinero
    if ($data['type'] === 'out' && $currentBalance < $amount) {
        throw new Exception('Saldo insuficiente en caja');
    }

    // 🔢 Calcular nuevo saldo
    $newBalance = $data['type'] === 'in'
        ? $currentBalance + $amount
        : $currentBalance - $amount;

    // 🧾 Registrar movimiento
    $movementModel = new CashierMovementModel();

    $movementModel->insert([
        'cashier_id'         => $data['cashier_id'],
        'cashier_session_id' => $data['cashier_session_id'],
        'user_id'            => session()->get('id'),
        'branch_id'          => session()->get('branch_id'),
        'type'               => $data['type'],
        'amount'             => $amount,
        'balance_after'      => $newBalance,
        'concept'            => $data['concept'],
        'reference_type'     => $data['reference_type'] ?? null,
        'reference_id'       => $data['reference_id'] ?? null,
        'created_at'         => date('Y-m-d H:i:s'),
    ]);

    // 💰 Actualizar saldo de la caja
    $db->table('cashier')
        ->where('id', $data['cashier_id'])
        ->update([
            'current_balance' => $newBalance,
        ]);

    return true;
}
