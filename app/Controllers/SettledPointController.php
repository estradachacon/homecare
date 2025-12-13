<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SettledPointModel;
use App\Models\RouteModel;

class SettledPointController extends BaseController
{
    protected $settledPointModel;
    protected $routeModel;

    public function __construct()
    {
        $this->settledPointModel = new SettledPointModel();
        $this->routeModel = new RouteModel();
    }

    public function index()
    {
        $chk = requerirPermiso('ver_puntosfijos');
        if ($chk !== true) return $chk;

        $settledPointsModel = new SettledPointModel();

        $settledPoints = $settledPointsModel
            ->select('settled_points.*, routes.route_name AS route_name')
            ->join('routes', 'routes.id = settled_points.ruta_id')
            ->findAll();

        $data = [
            'settledPoints' => $settledPoints,
        ];
        return view('settledpoint/index', $data);
    }

    public function new()
    {
        $chk = requerirPermiso('crear_puntofijo');
        if ($chk !== true) return $chk;

        $routes = $this->routeModel->findAll();

        $data = [
            'title' => 'Crear caja',
            'rutas' => $routes,
        ];
        return view('settledpoint/create', $data);
    }


    public function create()
    {
        helper(['form']);
        $session = session();
        $rules = [
            'point_name' => 'required|min_length[3]',
            'ruta_id' => 'required|integer',
            'mon' => 'required|integer',
            'tus' => 'required|integer',
            'wen' => 'required|integer',
            'thu' => 'required|integer',
            'fri' => 'required|integer',
            'sat' => 'required|integer',
            'sun' => 'required|integer',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // --- CORRECCIÓN DE LOS DÍAS ---
        $checkedDays = $this->request->getPost('days_configuration') ?? [];
        $days_configuration = [];

        for ($i = 0; $i < 7; $i++) {
            $days_configuration[$i] = (isset($checkedDays[$i]) && $checkedDays[$i] == '1') ? 1 : 0;
        }

        $this->settledPointModel->save([
            'point_name' => $this->request->getPost('point_name'),
            'ruta_id' => $this->request->getPost('ruta_id'),
            'mon' => $this->request->getPost('mon'),
            'tus' => $this->request->getPost('tus'),
            'wen' => $this->request->getPost('wen'),
            'thu' => $this->request->getPost('thu'),
            'fri' => $this->request->getPost('fri'),
            'sat' => $this->request->getPost('sat'),
            'sun' => $this->request->getPost('sun'),
            'hora_inicio' => $this->request->getPost('hora_inicio'),
            'hora_fin' => $this->request->getPost('hora_fin'),
        ]);

        registrar_bitacora(
            'Crear Punto Fijo',
            'Destinos',
            'Se creó un nuevo punto fijo con ID ' . esc($this->settledPointModel->insertID()) . '.',
            $session->get('user_id')
        );

        return redirect()->to('/settledpoint')->with('success', 'Punto fijo creado correctamente.');
    }

    public function edit($id)
    {
        $chk = requerirPermiso('editar_puntofijo');
        if ($chk !== true) return $chk;

        $routes = $this->routeModel->findAll();
        // 1. Obtener la caja a editar
        $settledPoint = $this->settledPointModel->find($id);

        if (!$settledPoint) {
            return redirect()->to('/settledPoint')->with('error', 'Punto fijo no encontrado.');
        }

        $data = [
            'rutas' => $routes,
            'settledPoint' => $settledPoint,
        ];

        // Se asume que tienes una vista en 'seller/edit'
        return view('settledpoint/edit', $data);
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
        if (
            !$this->validate([
                'point_name' => 'required|min_length[3]',
                'ruta_id' => 'required|integer',
                'mon' => 'required|integer',
                'tus' => 'required|integer',
                'wen' => 'required|integer',
                'thu' => 'required|integer',
                'fri' => 'required|integer',
                'sat' => 'required|integer',
                'sun' => 'required|integer',
                'hora_inicio' => 'required',
                'hora_fin' => 'required',
            ])
        ) {
            // 2. Si la validación falla, redirigir de vuelta al formulario con los errores
            return redirect()->back()
                ->withInput() // Mantiene los datos que el usuario ingresó
                ->with('errors', $this->validator->getErrors()); // Envía los errores a la vista
        }

        // 3. Si la validación es exitosa, se procede a la actualización
        $data = [
            'point_name' => $this->request->getPost('point_name'),
            'ruta_id' => $this->request->getPost('ruta_id'),
            'mon' => $this->request->getPost('mon'),
            'tus' => $this->request->getPost('tus'),
            'wen' => $this->request->getPost('wen'),
            'thu' => $this->request->getPost('thu'),
            'fri' => $this->request->getPost('fri'),
            'sat' => $this->request->getPost('sat'),
            'sun' => $this->request->getPost('sun'),
            'hora_inicio' => $this->request->getPost('hora_inicio'),
            'hora_fin' => $this->request->getPost('hora_fin'),
        ];

        $this->settledPointModel->update($id, $data);

        registrar_bitacora(
            'Se editó Punto Fijo',
            'Destinos',
            'Se editó el punto fijo con ID ' . esc($id) . '.',
            $session->get('user_id')
        );
        return redirect()->to('/settledpoint')->with('success', 'Punto fijo actualizado exitosamente.');
    }

    public function delete()
    {
        helper(['form']);
        $session = session();
        $id = $this->request->getPost('id');
        $settledPointModel = new SettledPointModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }

        if ($settledPointModel->delete($id)) {
            registrar_bitacora(
                'Eliminó punto fijo',
                'Destinos',
                'Se eliminó el punto fijo con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON(['status' => 'success', 'message' => 'Registro de punto fijo eliminado correctamente.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo eliminar el punto fijo.']);
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
    public function getList()
    {
        if ($this->request->isAJAX()) {
            $term = $this->request->getGet('q');

            $builder = $this->settledPointModel->select('id, point_name')
                ->orderBy('point_name', 'ASC');

            if (!empty($term)) {
                $builder->like('point_name', $term);
            }

            $points = $builder->findAll();

            return $this->response->setJSON($points);
        }

        return redirect()->to('/');
    }
    public function getAvailableDays($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Petición inválida']);
        }

        $settledPoint = $this->settledPointModel->find($id);
        if (!$settledPoint) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Punto fijo no encontrado']);
        }

        // Días de la semana disponibles
        $days = [
            'mon' => (bool) $settledPoint->mon,
            'tus' => (bool) $settledPoint->tus,
            'wen' => (bool) $settledPoint->wen,
            'thu' => (bool) $settledPoint->thu,
            'fri' => (bool) $settledPoint->fri,
            'sat' => (bool) $settledPoint->sat,
            'sun' => (bool) $settledPoint->sun,
        ];

        return $this->response->setJSON($days);
    }

}
