<?php

namespace App\Controllers;

use App\Models\RouteModel;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class RouteController extends BaseController
{
    protected $routeModel;

    public function __construct()
    {
        $this->routeModel = new RouteModel();
        helper(['form', 'url']);
    }

    // 🟦 1. INDEX: lista todas las sucursales
    public function index()
    {
        $data['routes'] = $this->routeModel->findAll();
        return view('routes/index', $data);
    }

    // 🟩 2. NEW: muestra el formulario de creación
    public function new()
    {
        return view('routes/create');
    }

    // 🟨 3. CREATE: procesa los datos del formulario
    public function create()
    {
        $session = session();
        $rules = [
            'route_name'       => 'required|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            // Si falla validación, vuelve al formulario con errores
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Si pasa validación, inserta el registro
        $this->routeModel->insert([
            'route_name'      => $this->request->getPost('route_name'),
            'descripction' => $this->request->getPost('description')
        ]);
        registrar_bitacora(
            'Creación de ruta',
            'Destinos',
            'Se creó la ruta: ' . $this->request->getPost('route_name') . '.',
            $session->get('user_id')
        );
        return redirect()->to('/routes')->with('success', 'Ruta creada exitosamente.');
    }

    public function delete()
    {
        $session = session();
        $id = $this->request->getPost('id');
        $routeModel = new RouteModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }
        $route = $routeModel->find($id);
        if ($routeModel->delete($id)) {
            registrar_bitacora(
                'Eliminó ruta',
                'Destinos',
                'Se eliminó la ruta: ' . esc($route->route_name) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Ruta eliminada correctamente.',
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
        public function edit($id)
    {
        // 1. Obtener la caja a editar
        $route = $this->routeModel->find($id);

        if (!$route) {
            return redirect()->to('/routes')->with('error', 'Ruta no encontrada.');
        }

        $data = [
            'routes' => $route,
        ];

        // Se asume que tienes una vista en 'seller/edit'
        return view('routes/edit', $data);
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
            'route_name' => 'required|min_length[3]|max_length[100]',
        ])) {
            // 2. Si la validación falla, redirigir de vuelta al formulario con los errores
            return redirect()->back()
                ->withInput() // Mantiene los datos que el usuario ingresó
                ->with('errors', $this->validator->getErrors()); // Envía los errores a la vista
        }

        // 3. Si la validación es exitosa, se procede a la actualización
        $data = [
            'route_name' => $this->request->getPost('route_name'),
            'description' => $this->request->getPost('description'),
        ];

        $this->routeModel->update($id, $data);
        registrar_bitacora(
            'Editó ruta',
            'Destinos',
            'Se editó la ruta con ID ' . esc($id) . '.',
            $session->get('user_id')
        );
        return redirect()->to('/routes')->with('success', 'Ruta actualizada exitosamente.');
    }
}
