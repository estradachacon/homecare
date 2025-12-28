<?php

use App\Models\TransactionModel;

use App\Models\AccountModel;

function actualizarSaldoCuenta($accountId)
{
    $accountModel = new AccountModel();

    // 🔹 ENTRADAS
    $transactionModelEntradas = new TransactionModel();
    $entradasData = $transactionModelEntradas
        ->where('account_id', $accountId)
        ->where('tipo', 'entrada')
        ->selectSum('monto')
        ->first();

    $entradas = $entradasData->monto ?? 0;

    // 🔹 SALIDAS
    $transactionModelSalidas = new TransactionModel();
    $salidasData = $transactionModelSalidas
        ->where('account_id', $accountId)
        ->where('tipo', 'salida')
        ->selectSum('monto')
        ->first();

    $salidas = $salidasData->monto ?? 0;

    $nuevoSaldo = floatval($entradas) - floatval($salidas);

    return $accountModel->update($accountId, [
        'balance' => $nuevoSaldo
    ]);
}



function registrarEntrada($accountId, $monto, $origen = null, $referencia = null, $trackingId = null)
{
    $model = new TransactionModel();

    $monto = floatval($monto);

    // ❌ No registrar montos vacíos o cero
    if ($monto <= 0) {
        return false;
    }

    $model->insert([
        'account_id' => $accountId,
        'tracking_id' => $trackingId,
        'tipo' => 'entrada',
        'monto' => $monto,
        'origen' => $origen,
        'referencia' => $referencia,
    ]);
    actualizarSaldoCuenta($accountId);
}

function registrarSalida($accountId, $monto, $origen = null, $referencia = null, $trackingId = null)
{
    $model = new TransactionModel();

    $monto = floatval($monto);

    // ❌ No registrar montos vacíos o cero
    if ($monto <= 0) {
        return false;
    }
    
    $model->insert([
        'account_id' => $accountId,
        'tracking_id' => $trackingId,
        'tipo' => 'salida',
        'monto' => $monto,
        'origen' => $origen,
        'referencia' => $referencia,
    ]);
    actualizarSaldoCuenta($accountId);
}
