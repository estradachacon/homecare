<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ClienteModel;

class ClienteController extends BaseController
{
    public function index()
    {
        $clienteModel = new ClienteModel();
        $q = $this->request->getGet('q');

        $builder = $clienteModel;

        if ($q) {
            $builder->groupStart()
                ->like('nombre', $q)
                ->orLike('numero_documento', $q)
                ->orLike('nrc', $q)
                ->groupEnd();
        }

        $data = [
            'clientes' => $builder->paginate(10),
            'pager' => $builder->pager,
            'q' => $q
        ];

        return view('clientes/index', $data);
    }
    public function show($id)
    {
        $clienteModel = new ClienteModel();
        $facturaHeadModel = new \App\Models\FacturaHeadModel();

        $cliente = $clienteModel->find($id);

        if (!$cliente) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Facturas asociadas al cliente
        $facturas = $facturaHeadModel
            ->where('receptor_id', $id)
            ->orderBy('fecha_emision', 'DESC')
            ->findAll();

        return view('clientes/show', [
            'cliente'  => $cliente,
            'facturas' => $facturas
        ]);
    }
}
