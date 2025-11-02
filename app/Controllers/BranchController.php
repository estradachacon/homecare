<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BranchModel;
use CodeIgniter\HTTP\ResponseInterface;

class BranchController extends BaseController
{
    public function index()
    {
        $branchModel = new BranchModel();
        $branches = $branchModel->findAll();
        $data = [
            'title' => 'Listado de Sucursales',
            'branches' => $branches
        ];
        return view('sucursales/index', $data);
    }
}
