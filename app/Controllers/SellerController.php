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

    public function edit($id)
    {
        // 1. Obtener la caja a editar
        $seller = $this->sellerModel->find($id);

        if (!$seller) {
            return redirect()->to('/sellers')->with('error', 'Vendedor no encontrado.');
        }

        $data = [
            'sellers' => $seller,
        ];

        // Se asume que tienes una vista en 'seller/edit'
        return view('sellers/edit', $data);
    }

    /**
     * Procesa y actualiza los datos de la caja.
     * @param int $id El ID de la caja a actualizar (viene del segmento de la URL).
     */
    public function update($id)
    {
        helper(['form']);
        $session = session();
        // 1. Definir las reglas de validación (deben coincidir con tu modelo, o definirlas aquí)
        if (!$this->validate([
            'seller' => 'required|min_length[3]|max_length[100]',
            'tel_seller' => 'required|numeric',
        ])) {
            // 2. Si la validación falla, redirigir de vuelta al formulario con los errores
            return redirect()->back()
                ->withInput() // Mantiene los datos que el usuario ingresó
                ->with('errors', $this->validator->getErrors()); // Envía los errores a la vista
        }

        // 3. Si la validación es exitosa, se procede a la actualización
        $data = [
            'seller' => $this->request->getPost('seller'),
            'tel_seller' => $this->request->getPost('tel_seller'),
        ];

        $this->sellerModel->update($id, $data);
        registrar_bitacora(
            'Se editó vendedor',
            'Vendedores',
            'Se editó el vendedor con ID ' . esc($id) . '.',
            $session->get('user_id')
        );
        return redirect()->to('/sellers')->with('success', 'Vendedor actualizado exitosamente.');
    }

    public function delete()
    {
        helper(['form']);
        $session = session();
        $id = $this->request->getPost('id');
        $cashierModel = new SellerModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }

        if ($cashierModel->delete($id)) {
            registrar_bitacora(
                'Eliminó vendedor',
                'Vendedores',
                'Se eliminó el vendedor con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON(['status' => 'success', 'message' => 'Registro de vendedor eliminado correctamente.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo eliminar el vendedor.']);
    }
    public function search()
    {
        $term = trim($this->request->getGet('q') ?? '');

        // Si el usuario no escribió nada, devolvemos un array vacío
        if ($term === '') {
            return $this->response->setJSON(['results' => []]);
        }

        $sellerModel = new SellerModel();

        $results = $sellerModel
            ->groupStart()
            ->like('seller', $term)
            ->orLike('id', $term)
            ->groupEnd()
            ->select('id, seller')
            ->limit(10)
            ->findAll();

        $data = [];
        foreach ($results as $row) {
            $data[] = [
                'id' => $row->id,
                'text' => $row->seller,
            ];
        }

        return $this->response->setJSON($data);
    }


public function createAjax()
    {
        $sellerModel = new SellerModel();
        $session = session();
        $data = [
            'seller' => $this->request->getPost('seller'),
            'telefono' => $this->request->getPost('tel_seller'),
        ];

        if (empty($data['seller'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El nombre del vendedor es obligatorio.'
            ]);
        }

        try {
            $id = $sellerModel->insert($data);

            if (!$id) {
                throw new \Exception('No se pudo guardar el vendedor.');
            }
            registrar_bitacora(
                'Creación de vendedor',
                'Paquetería',
                'Se creó el vendedor ' . esc($data['seller']) . ' en el registro de paquete.',
                $session->get('user_id')
            );

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id' => $id,
                    'text' => $data['seller']
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
