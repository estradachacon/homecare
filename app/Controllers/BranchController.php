<?php

namespace App\Controllers;

use App\Models\BranchModel;
use CodeIgniter\Controller;

class BranchController extends Controller
{
    protected $branchModel;

    public function __construct()
    {
        $this->branchModel = new BranchModel();
        helper(['form', 'url']);
    }

    // 🟦 1. INDEX: lista todas las sucursales
    public function index()
    {
        $data['branches'] = $this->branchModel->findAll();
        return view('sucursales/index', $data);
    }

    // 🟩 2. NEW: muestra el formulario de creación
    public function new()
    {
        return view('sucursales/new');
    }

    // 🟨 3. CREATE: procesa los datos del formulario
    public function create()
    {
        $session = session();
        $rules = [
            'branch_name'       => 'required|min_length[3]',
            'branch_direction'  => 'required|min_length[5]',
            'status'            => 'required|in_list[active,inactive]'
        ];

        if (! $this->validate($rules)) {
            // Si falla validación, vuelve al formulario con errores
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Si pasa validación, inserta el registro
        $this->branchModel->insert([
            'branch_name'      => $this->request->getPost('branch_name'),
            'branch_direction' => $this->request->getPost('branch_direction'),
            'status'           => $this->request->getPost('status')
        ]);
        registrar_bitacora(
            'Creación de sucursal',
            'Sucursales',
            'Se creó la sucursal: ' . $this->request->getPost('branch_name') . '.',
            $session->get('user_id')
        );
        return redirect()->to('/branches')->with('success', 'Sucursal creada exitosamente.');
    }

    public function delete()
    {
        $session = session();
        $id = $this->request->getPost('id');
        $branchModel = new branchModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }
        $branch = $branchModel->find($id);
        if ($branchModel->delete($id)) {
            registrar_bitacora(
                'Eliminó sucursal',
                'Sucursales',
                'Se eliminó la sucursal: ' . esc($branch->branch_name) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Sucursal eliminada correctamente.',
                'csrf'    => [
                    'token'  => csrf_hash(),
                    'header' => csrf_header(),
                ]
            ]);
        }

        return $this->response->setJSON(['status' => 'error', 
        'message' => 'No se pudo eliminar la sucursal.',                 
        'csrf'    => [
            'token'  => csrf_hash(),
            'header' => csrf_header(),
        ]]);
    }
}
