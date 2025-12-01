<?php

use App\Models\TransactionModel;

function registrarEntrada($accountId, $monto, $origen = null, $referencia = null, $trackingId = null)
{
    $model = new TransactionModel();

    return $model->insert([
        'account_id'  => $accountId,
        'tracking_id' => $trackingId,
        'tipo'        => 'entrada',
        'monto'       => $monto,
        'origen'      => $origen,
        'referencia'  => $referencia,
    ]);
}

function registrarSalida($accountId, $monto, $origen = null, $referencia = null, $trackingId = null)
{
    $model = new TransactionModel();

    return $model->insert([
        'account_id'  => $accountId,
        'tracking_id' => $trackingId,
        'tipo'        => 'salida',
        'monto'       => $monto,
        'origen'      => $origen,
        'referencia'  => $referencia,
    ]);
}
