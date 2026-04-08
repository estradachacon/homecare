<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DteController extends BaseController
{
    public function new()
    {
        return view('emisiondte/create');
    }
}
