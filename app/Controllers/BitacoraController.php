<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BitacoraSistemaModel;

class BitacoraController extends BaseController
{
    public function index()
    {
        $bitacoraModel = new BitacoraSistemaModel();

        // ✅ Selector de cantidad por página (por defecto 10)
        $perPage = $this->request->getGet('per_page') ?? 10;

        // ✅ Traer registros con JOIN para mostrar nombre de usuario
        $data['bitacoras'] = $bitacoraModel
            ->select('bitacora_sistema.*, users.user_name as usuario')
            ->join('users', 'users.id = bitacora_sistema.user_id', 'left')
            ->orderBy('bitacora_sistema.created_at', 'DESC')
            ->paginate($perPage);

        // ✅ Pasar datos a la vista
        $data['pager'] = $bitacoraModel->pager;
        $data['perPage'] = $perPage;
        $data['title'] = 'Bitácora del Sistema';

        return view('bitacora/index', $data);
    }
}
