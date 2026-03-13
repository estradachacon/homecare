<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ComprasController extends BaseController
{

    public function index()
    {
        $chk = requerirPermiso('ver_compras');
        if ($chk !== true) return $chk;

        return view('compras/index');
    }

}
