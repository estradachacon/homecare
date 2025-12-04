<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TransactionModel;


class TransactionsController extends BaseController
{
    public function index()
    {
        $model = new TransactionModel();
        $data['transactions'] = $model->getTransactionsWithAccountName();

        return view('transactions/index', $data);
    }
    public function addSalida()
    {
        helper(['form', 'bitacora', 'account']);
        $session = session();
        $request = service('request');

        $accountId = $request->getPost('account');
        $monto     = $request->getPost('gastoMonto');
        $origen = $request->getPost('gastoDescripcion');

        // Validación simple
        if (!$accountId || !$monto) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Datos incompletos'
            ], 400);
        }

        $montoSalida = -abs($monto);
        $balanceUpdate = updateAccountBalance($accountId, $montoSalida);
        if (!$balanceUpdate['status']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo actualizar la cuenta: ' . $balanceUpdate['message']
            ]);
        }

        $transaction = new TransactionModel();

        // Registrar SALIDA
        $transaction->insert([
            'account_id'  => $accountId,
            'tracking_id' => null,
            'tipo'        => 'salida',
            'monto'       => $monto,
            'origen'      => $origen,
        ]);

        registrar_bitacora(
            'Creación de Gasto/Salida',
            'Finanzas',
            'Se registró un gasto/salida de  $' . $monto . '  en la cuenta ID ' . $accountId,
            $session->get('user_id')
        );
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Gasto registrado',
            'newBalance' => $balanceUpdate['newBalance']
        ]);
    }
}
