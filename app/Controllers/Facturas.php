<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Facturas extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_facturas');
        if ($chk !== true) return $chk;

        return view('facturas/index');
    }

    public function carga()
    {
        $chk = requerirPermiso('cargar_facturas');
        if ($chk !== true) return $chk;

        return view('facturas/carga_procesado');
    }
}
