<?php

namespace App\Models;

use CodeIgniter\Model;

class CashierSessionsModel extends Model
{
    protected $table            = 'cashier_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $protectFields    = true;
    protected $allowedFields    = ['cashier_id', 'user_id', 'branch_id', 'initial_amount', 'closing_amount', 'status', 'open_time', 'close_time'];

    // Dates
    protected $useTimestamps = false;
    public function getCashierSummary(int $sessionId): array
    {
        $movementModel = new \App\Models\CashierMovementModel();

        $session = $this->find($sessionId);
        if (!$session) {
            throw new \RuntimeException('Sesión no encontrada');
        }

        $totalIn = $movementModel
            ->selectSum('amount')
            ->where('cashier_session_id', $sessionId)
            ->where('type', 'in')
            ->get()
            ->getRow()
            ->amount ?? 0;

        $totalOut = $movementModel
            ->selectSum('amount')
            ->where('cashier_session_id', $sessionId)
            ->where('type', 'out')
            ->get()
            ->getRow()
            ->amount ?? 0;

        $expected = $session->initial_amount + $totalIn - $totalOut;

        return [
            'session'   => $session,
            'total_in'  => (float)$totalIn,
            'total_out' => (float)$totalOut,
            'expected'  => (float)$expected,
        ];
    }
}
