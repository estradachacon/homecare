<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class RemunerationController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_remuneraciones');
        if ($chk !== true) return $chk;
    }

    public function create()
    {
        $chk = requerirPermiso('remunerar_paquetes');
        if ($chk !== true) return $chk;

        $db = db_connect();

        // Obtener sesión de caja abierta del usuario
        $cashierSession = $db->table('cashier_sessions')
            ->where('status', 'open')
            ->where('user_id', session()->get('id'))
            ->get()
            ->getRowArray();

        $availableAmount = 0;

        if ($cashierSession) {
            // Obtener caja
            $cashier = $db->table('cashier')
                ->where('id', $cashierSession['cashier_id'])
                ->get()
                ->getRowArray();

            if ($cashier) {
                $availableAmount = (float) $cashier['current_balance'];
            }
        }

        return view('remuneration/new', [
            'availableAmount' => $availableAmount
        ]);
    }
    public function availableAmount()
    {
        $db = db_connect();

        $cashierSession = $db->table('cashier_sessions')
            ->where('status', 'open')
            ->where('user_id', session()->get('id'))
            ->get()
            ->getRowArray();

        if (!$cashierSession) {
            return $this->response->setJSON([
                'success' => false,
                'available' => 0
            ]);
        }

        $cashier = $db->table('cashier')
            ->where('id', $cashierSession['cashier_id'])
            ->get()
            ->getRowArray();

        if (!$cashier) {
            return $this->response->setJSON([
                'success' => false,
                'available' => 0
            ]);
        }

        $available = (float) $cashier['current_balance'];

        return $this->response->setJSON([
            'success'   => true,
            'available' => $available
        ]);
    }
}
