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

        return view('remuneration/new');
    }
}