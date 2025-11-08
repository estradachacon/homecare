<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\SellerModel;

class PackageController extends BaseController
{
    protected $packageModel;

    public function __construct()
    {
        $this->packageModel = new PackageModel();
    }

    public function index()
    {
        $data['packages'] = $this->packageModel->findAll();
        return view('packages/index', $data);
    }

    public function show($id = null)
    {
        $data['package'] = $this->packageModel->find($id);
        if (!$data['package']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Paquete no encontrado");
        }
        return view('packages/show', $data);
    }

    public function new()
    {
        return view('packages/new');
    }
}
