<?php

use App\Models\AccountModel;

function updateAccountBalance($accountId, $amount)
{
    $accountModel = new AccountModel();

    // Obtener la cuenta
    $account = $accountModel->asArray()->find($accountId);
    if (!$account) {
        return ['status' => false, 'message' => 'Cuenta no encontrada'];
    }

    // Calcular nuevo saldo
    $newBalance = $account['balance'] + $amount;

    // Guardar cambios
    $accountModel->update($accountId, ['balance' => $newBalance]);

    return ['status' => true, 'message' => 'Saldo actualizado', 'newBalance' => $newBalance];
}

