<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;

class PaymentController extends BaseController
{
    public function packagesBySeller($sellerId)
    {
        $packageModel = new PackageModel();
        return $this->response->setJSON(
            $packageModel->getPackagesPendingPaymentBySeller((int)$sellerId)
        );
    }
    public function paySeller()
    {
        helper(['form']);
        $session = session();

        $db = db_connect();
        $data = $this->request->getJSON(true);

        // 🔹 Validar datos mínimos
        if (!$data || empty($data['seller_id']) || empty($data['packages'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }

        $sellerId = (int) $data['seller_id'];
        $packages = $data['packages'];

        $db->transStart();

        // 🔎 Obtener sesión de caja abierta del usuario
        $cashierSession = $db->table('cashier_sessions')
            ->where('status', 'open')
            ->where('user_id', session()->get('id'))
            ->get()
            ->getRowArray();

        if (!$cashierSession) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay una sesión de caja abierta'
            ]);
        }

        // 🔎 Obtener caja
        $cashier = $db->table('cashier')
            ->where('id', $cashierSession['cashier_id'])
            ->get()
            ->getRowArray();

        if (!$cashier) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Caja no encontrada'
            ]);
        }

        // 🔹 Calcular total a pagar sumando los paquetes
        $totalPay = 0;

        foreach ($packages as $pkg) {
            $package = $db->table('packages')
                ->where('id', $pkg['id'])
                ->where('vendedor', $sellerId)
                ->get()
                ->getRowArray();

            if (!$package) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Paquete inválido: #' . $pkg['id']
                ]);
            }

            $monto = (float) $package['monto'];
            $pendiente = (float) ($package['flete_pendiente'] ?? 0);
            $netAmount = $monto - $pendiente;

            if ($netAmount < 0) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Monto inválido en paquete #' . $package['id']
                ]);
            }

            $totalPay += $netAmount;

            // 🔹 Actualizar paquete: marcar parcialmente pagado
            $db->table('packages')
                ->where('id', $package['id'])
                ->update([
                    'amount_paid'     => $netAmount,
                    'flete_pendiente' => 0,
                    'estatus'         => 'finalizado',
                    'estatus2'        => 'remunerado',
                ]);
        }

        // ❌ Saldo insuficiente en caja
        if ((float)$cashier['current_balance'] < $totalPay) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Saldo insuficiente en caja'
            ]);
        }

        // 🔹 Registrar movimiento en caja usando el modelo
        $cashierMovementModel = new \App\Models\CashierMovementModel();

        $newBalance = $cashier['current_balance'] - $totalPay;

        $cashierMovementModel->insert([
            'cashier_id'         => $cashier['id'],
            'cashier_session_id' => $cashierSession['id'],
            'user_id'            => session()->get('id'),
            'branch_id'          => session()->get('branch_id'),
            'type'               => 'out',
            'amount'             => $totalPay,
            'balance_after'      => $newBalance,
            'concept'            => 'Pago a vendedor #' . $sellerId,
            'reference_type'     => 'packages',
            'reference_id'       => null,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        // 🔹 Actualizar saldo de la caja
        $db->table('cashier')
            ->where('id', $cashier['id'])
            ->update([
                'current_balance' => $newBalance
            ]);

        // 🔹 Obtener cuenta efectivo (ID = 1)
        $account = $db->table('accounts')
            ->where('id', 1)
            ->get()
            ->getRowArray();

        if (!$account) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cuenta de efectivo no encontrada'
            ]);
        }

        // ❌ Validar que exista reserva suficiente en cajas
        if ((float)$account['cashier_reserv'] < $totalPay) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La reserva de efectivo en cajas es insuficiente'
            ]);
        }

        // 🔻 Actualizar reserva de efectivo (sale dinero de caja)
        $db->table('accounts')
            ->where('id', 1)
            ->update([
                'cashier_reserv' => $account['cashier_reserv'] - $totalPay,
            ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al procesar el pago'
            ]);
        }
        registrar_bitacora(
            'Pago a vendedor ID ' . esc($sellerId),
            'Remuneraciones',
            'Se pagó un total de $' . number_format($totalPay, 2) . ' al vendedor con ID ' . esc($sellerId) . '.',
            $session->get('id')
        );

        return $this->response->setJSON([
            'success'    => true,
            'total_paid' => $totalPay,
            'new_balance' => $newBalance
        ]);
    }
}
