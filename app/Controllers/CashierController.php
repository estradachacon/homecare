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

        // 🏆 ESTA ES LA LÓGICA CORRECTA PARA CARGAR EL LISTADO 🏆
        $cashiers = $cashierModel
            // 1. Seleccionamos campos: Todos de cashier y los nombres de las tablas unidas
            ->select('cashier.*, users.user_name, branches.branch_name') 
            // 2. Unimos con users (LEFT JOIN para permitir cajas sin user_id)
            ->join('users', 'users.id = cashier.user_id', 'left') 
            // 3. Unimos con branches
            ->join('branches', 'branches.id = cashier.branch_id')
            ->findAll();

        $data = [
            'title' => 'Listado de Cajas',
            'cashiers' => $cashiers // Pasamos la colección completa y enriquecida a la vista
        ];

        // Retorna la vista de la tabla de listado
        return view('cashier/index', $data);
    }

    public function new()
    {
        $branchModel = new \App\Models\BranchModel();
        $userModel = new \App\Models\UserModel();
        $branches = $branchModel->findAll();
        $users = $userModel->findAll();
        $data = [
            'title' => 'Crear caja',
            'branches' => $branches,
            'users' => $users
        ];
        return view('cashier/new', $data);
    }

    public function create()
    {
        $cashierModel = new CashierModel();

        $data = [
            'name' => $this->request->getPost('name'),
            'initial_balance' => $this->request->getPost('initial_balance'),
            'branch_id' => $this->request->getPost('branch_id'),
            'user_id' => $this->request->getPost('user_id'),
        ];

        $cashierModel->insert($data);

        return redirect()->to('/cashiers')->with('success', 'Caja creada exitosamente.');
    }
}
