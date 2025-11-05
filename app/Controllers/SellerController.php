<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SellerModel;

class SellerController extends BaseController
{
    protected $sellerModel;

    public function __construct()
    {
        $this->sellerModel = new SellerModel();
    }

    public function index()
    {
        $data['sellers'] = $this->sellerModel->findAll();
        return view('sellers/index', $data);
    }

    public function new()
    {
        return view('sellers/create');
    }

    public function create()
    {
        helper(['form']);
        $session = session();
        $rules = [
            'seller' => 'required|min_length[3]',
            'tel_seller' => 'permit_empty|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->sellerModel->save([
            'seller' => $this->request->getPost('seller'),
            'tel_seller' => $this->request->getPost('tel_seller')
        ]);

        registrar_bitacora(
            'Crear vendedor',
            'Vendedores',
            'Se creó un nuevo vendedor con ID ' . esc($this->sellerModel->insertID()) . '.',
            $session->get('user_id')
        );

        return redirect()->to('/sellers')->with('success', 'Vendedor creado correctamente.');
    }

    public function delete($id = null)
    {
        helper(['form']);
        $session = session();
        $sellerModel = new SellerModel();

        // ✅ Detecta si es una petición AJAX
        if ($this->request->isAJAX()) {
            if (!$id || !$sellerModel->find($id)) {
                return $this->response->setStatusCode(404)
                    ->setJSON(['status' => 'error', 'message' => 'Vendedor no encontrado.']);
            }

            $sellerModel->delete($id);
            registrar_bitacora(
                'Borrar vendedor',
                'Vendedores',
                'Borró el vendedor con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Vendedor eliminado correctamente.'
            ]);
        }

        // 🚪 Si llega por método DELETE tradicional (por formulario, no AJAX)
        $sellerModel->delete($id);
            registrar_bitacora(
                'Borrar vendedor',
                'Vendedores',
                'Borró el vendedor con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
        return redirect()->to('/sellers')->with('success', 'Vendedor eliminado correctamente.');
    }
}
