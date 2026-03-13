<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProveedorModel;

class ProveedorController extends BaseController
{
    protected $proveedorModel;

    public function __construct()
    {
        $this->proveedorModel = new ProveedorModel();
    }

    public function index()
    {
        $chk = requerirPermiso('ver_proveedores');
        if ($chk !== true) return $chk;

        $q = trim($this->request->getGet('q') ?? '');
        $perPage = intval($this->request->getGet('perPage') ?? 10);

        $builder = $this->proveedorModel;

        if ($q !== '') {
            $builder = $builder
                ->groupStart()
                ->like('nombre', $q)
                ->orLike('telefono', $q)
                ->orLike('email', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        $data = [
            'q' => $q,
            'perPage' => $perPage,
            'proveedores' => $builder->paginate($perPage),
            'pager' => $builder->pager,
        ];

        return view('proveedores/index', $data);
    }

    public function new()
    {
        $chk = requerirPermiso('crear_proveedor');
        if ($chk !== true) return $chk;

        return view('proveedores/create');
    }

    public function create()
    {
        helper(['form']);
        $session = session();

        $rules = [
            'nombre' => 'required|min_length[3]',
            'telefono' => 'permit_empty|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->proveedorModel->save([
            'nombre' => $this->request->getPost('nombre'),
            'telefono' => $this->request->getPost('telefono'),
            'email' => $this->request->getPost('email'),
            'direccion' => $this->request->getPost('direccion')
        ]);

        registrar_bitacora(
            'Crear proveedor',
            'Proveedores',
            'Se creó un nuevo proveedor con ID ' . esc($this->proveedorModel->insertID()) . '.',
            $session->get('user_id')
        );

        return redirect()->to('/proveedores')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit($id)
    {
        $chk = requerirPermiso('editar_proveedor');
        if ($chk !== true) return $chk;

        $proveedor = $this->proveedorModel->find($id);

        if (!$proveedor) {
            return redirect()->to('/proveedores')
                ->with('error', 'Proveedor no encontrado.');
        }

        return view('proveedores/edit', [
            'proveedor' => $proveedor
        ]);
    }

    public function update($id)
    {
        helper(['form']);
        $session = session();

        if (
            !$this->validate([
                'nombre' => 'required|min_length[3]|max_length[150]',
                'telefono' => 'permit_empty|min_length[8]',
                'email' => 'permit_empty|valid_email'
            ])
        ) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'telefono' => $this->request->getPost('telefono'),
            'email' => $this->request->getPost('email'),
            'direccion' => $this->request->getPost('direccion')
        ];

        $this->proveedorModel->update($id, $data);

        registrar_bitacora(
            'Editar proveedor',
            'Proveedores',
            'Se editó el proveedor con ID ' . esc($id) . '.',
            $session->get('user_id')
        );

        return redirect()->to('/proveedores')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function delete()
    {
        helper(['form']);
        $session = session();

        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID inválido.'
            ]);
        }

        if ($this->proveedorModel->delete($id)) {

            registrar_bitacora(
                'Eliminar proveedor',
                'Proveedores',
                'Se eliminó el proveedor con ID ' . esc($id) . '.',
                $session->get('user_id')
            );

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Proveedor eliminado correctamente.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No se pudo eliminar el proveedor.'
        ]);
    }

    public function search()
    {
        $q = $this->request->getGet('q');
        $perPage = $this->request->getGet('perPage') ?? 10;

        $builder = $this->proveedorModel;

        if ($q) {
            $builder->groupStart()
                ->like('nombre', $q)
                ->orLike('telefono', $q)
                ->orLike('email', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        $data = [
            'proveedores' => $builder->paginate($perPage),
            'pager' => $builder->pager,
            'q' => $q,
            'perPage' => $perPage
        ];

        return view('proveedores/_proveedor_table', $data);
    }

    public function createAjax()
    {
        $session = session();

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'telefono' => $this->request->getPost('telefono'),
            'email' => $this->request->getPost('email'),
            'direccion' => $this->request->getPost('direccion')
        ];

        if (empty($data['nombre'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El nombre del proveedor es obligatorio.'
            ]);
        }

        try {

            $id = $this->proveedorModel->insert($data);

            if (!$id) {
                throw new \Exception('No se pudo guardar el proveedor.');
            }

            registrar_bitacora(
                'Creación proveedor',
                'Proveedores',
                'Se creó el proveedor ' . esc($data['nombre']) . '.',
                $session->get('user_id')
            );

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id' => $id,
                    'text' => $data['nombre']
                ]
            ]);
        } catch (\Throwable $e) {

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function searchAjax()
    {
        $term = $this->request->getGet('q');

        if ($this->request->getGet('select2')) {

            $proveedores = $this->proveedorModel
                ->like('nombre', $term)
                ->findAll(10);

            $results = [];

            foreach ($proveedores as $p) {
                $results[] = [
                    'id' => $p->id,
                    'text' => $p->nombre
                ];
            }

            return $this->response->setJSON([
                'results' => $results
            ]);
        }

        return $this->response->setJSON(['results' => []]);
    }
}
