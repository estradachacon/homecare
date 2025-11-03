<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CashierModel;
use CodeIgniter\HTTP\ResponseInterface;

class CashierController extends BaseController
{
    public function index()
    {
        $cashierModel = new CashierModel();

        $cashiers = $cashierModel
            ->select('cashier.*, branches.branch_name AS branch_name')
            // Usar 'cashier.*' trae todos los campos de cashier (incluyendo branch_id)
            // y 'branches.name AS branch_name' añade el nombre de la sucursal con un alias claro.
            ->join('branches', 'branches.id = cashier.branch_id', 'left')
            ->findAll();

        $data = [
            'title' => 'Listado de cajas',
            'cashiers' => $cashiers
        ];
        return view('cashier/index', $data);
    }

    public function new()
    {
        $branchModel = new \App\Models\BranchModel();
        $branches = $branchModel->findAll();
        $data = [
            'title' => 'Crear caja',
            'branches' => $branches
        ];
        return view('cashier/new', $data);
    }
}
