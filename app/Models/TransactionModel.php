<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table      = 'transactions';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $returnType    = 'object';

    protected $allowedFields = [
        'account_id',
        'tracking_id',
        'tipo',
        'monto',
        'origen',
        'referencia',
    ];

    /**
     * Registra una entrada
     */
    public function addEntrada($accountId, $monto, $origen = null, $referencia = null, $trackingId = null)
    {
        return $this->insert([
            'account_id'  => $accountId,
            'tracking_id' => $trackingId,
            'tipo'        => 'entrada',
            'monto'       => $monto,
            'origen'      => $origen,
            'referencia'  => $referencia,
        ]);
    }

    /**
     * Registra una salida
     */
    public function addSalida($accountId, $monto, $origen = null, $referencia = null, $trackingId = null)
    {
        return $this->insert([
            'account_id'  => $accountId,
            'tracking_id' => $trackingId,
            'tipo'        => 'salida',
            'monto'       => $monto,
            'origen'      => $origen,
            'referencia'  => $referencia,
        ]);
    }

    /**
     * Obtener todas las transacciones de una cuenta
     */
    public function getByAccount($accountId)
    {
        return $this->where('account_id', $accountId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
