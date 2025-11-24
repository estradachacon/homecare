<?php

namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table      = 'accounts';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $returnType    = 'object';

    protected $allowedFields = [
        'name',
        'type',
        'description',
    ];

    /**
     * Obtener saldo de la cuenta
     * SUMA entradas - SUMA salidas
     */
    public function getBalance($accountId)
    {
        $db = \Config\Database::connect();

        $query = $db->table('transactions')
            ->select("
                SUM(CASE WHEN tipo = 'entrada' THEN monto ELSE 0 END) -
                SUM(CASE WHEN tipo = 'salida'  THEN monto ELSE 0 END) AS balance
            ")
            ->where('account_id', $accountId)
            ->get()
            ->getRow();

        return $query ? $query->balance : 0;
    }

}
