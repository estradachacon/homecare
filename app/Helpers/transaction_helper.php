<?php

use App\Models\TransactionModel;

use App\Models\AccountModel;

function actualizarSaldoCuenta($accountId)
{
    $accountModel = new AccountModel();
    $transactionModel = new TransactionModel();

    // SUMA ENTRADAS
    $entradasData = $transactionModel
        ->where('account_id', $accountId)
        ->where('tipo', 'entrada')
        ->selectSum('monto')
        ->first();

    $entradas = $entradasData->monto ?? 0;

    // SUMA SALIDAS
    $salidasData = $transactionModel
        ->where('account_id', $accountId)
        ->where('tipo', 'salida')
        ->selectSum('monto')
        ->first();

    $salidas = $salidasData->monto ?? 0;

    // CÁLCULO
    $nuevoSaldo = floatval($entradas) - floatval($salidas);

    // GUARDAR EN LA COLUMNA CORRECTA
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
