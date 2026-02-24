<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TipoVentaModel;

class TipoVentaController extends BaseController
{
    protected $tipoVentaModel;

    public function __construct()
    {
        $this->tipoVentaModel = new TipoVentaModel();
    }

    public function index()
    {
        $chk = requerirPermiso('ver_tipo_venta');
        if ($chk !== true) return $chk;

        $q = trim($this->request->getGet('q') ?? '');
        $alpha = trim($this->request->getGet('alpha') ?? '');
        $perPage = intval($this->request->getGet('perPage') ?? 10);

        $builder = $this->tipoVentaModel;

        // BÚSQUEDA GENERAL
        if ($q !== '') {
            $builder = $builder
                ->groupStart()
                ->like('nombre_tipo_venta', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        // FILTRO ALFABÉTICO
        if ($alpha !== '') {
            $builder = $builder->like('nombre_tipo_venta', $alpha, 'after');
        }

        $data = [
            'q' => $q,
            'alpha' => $alpha,
            'perPage' => $perPage,
            'tipo_ventas' => $builder->paginate($perPage),
            'pager' => $builder->pager,
        ];

        return view('tipo_venta/index', $data);
    }

    public function new()
    {
        $chk = requerirPermiso('crear_tipo_venta');
        if ($chk !== true) return $chk;

        return view('tipo_venta/create');
    }

    public function create()
    {
        helper(['form']);
        $session = session();
        $rules = [
            'nombre_tipo_venta' => 'required|min_length[3]',
            'descripcion' => 'permit_empty|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->tipoVentaModel->save([
            'nombre_tipo_venta' => $this->request->getPost('nombre_tipo_venta'),
            'descripcion' => $this->request->getPost('descripcion')
        ]);

        registrar_bitacora(
            'Crear tipo de venta',
            'Tipos de venta',
            'Se creó un nuevo tipo de venta con ID ' . esc($this->tipoVentaModel->insertID()) . '.',
            $session->get('user_id')
        );

        return redirect()->to('/tipo_venta')->with('success', 'Tipo de venta creado correctamente.');
    }

    public function edit($id)
    {
        $chk = requerirPermiso('editar_tipo_venta');
        if ($chk !== true) return $chk;

        // 1. Obtener la caja a editar
        $tipoVenta = $this->tipoVentaModel->find($id);

        if (!$tipoVenta) {
            return redirect()->to('/tipo_venta')->with('error', 'Tipo de venta no encontrado.');
        }

        $data = [
            'tipo_venta' => $tipoVenta,
        ];

        // Se asume que tienes una vista en 'tipo_venta/edit'
        return view('tipo_venta/edit', $data);
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
                'nombre_tipo_venta' => 'required|min_length[3]|max_length[100]',
                'descripcion' => 'permit_empty|min_length[8]'
            ])
        ) {
            // 2. Si la validación falla, redirigir de vuelta al formulario con los errores
            return redirect()->back()
                ->withInput() // Mantiene los datos que el usuario ingresó
                ->with('errors', $this->validator->getErrors()); // Envía los errores a la vista
        }

        // 3. Si la validación es exitosa, se procede a la actualización
        $data = [
            'nombre_tipo_venta' => $this->request->getPost('nombre_tipo_venta'),
            'descripcion' => $this->request->getPost('descripcion'),
        ];

        $this->tipoVentaModel->update($id, $data);
        registrar_bitacora(
            'Se editó tipo de venta',
            'Tipos de venta',
            'Se editó el tipo de venta con ID ' . esc($id) . '.',
            $session->get('user_id')
        );
        return redirect()->to('/tipo_venta')->with('success', 'Tipo de venta actualizado exitosamente.');
    }

    public function delete()
    {
        helper(['form']);
        $session = session();
        $id = $this->request->getPost('id');
        $tipoVentaModel = new TipoVentaModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }

        if ($tipoVentaModel->delete($id)) {
            registrar_bitacora(
                'Eliminó tipo de venta',
                'Tipos de venta',
                'Se eliminó el tipo de venta con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON(['status' => 'success', 'message' => 'Registro de tipo de venta eliminado correctamente.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo eliminar el tipo de venta.']);
    }
    public function search()
    {
        $q = trim($this->request->getGet('q') ?? '');
        $perPage = intval($this->request->getGet('perPage') ?? 10);

        $builder = $this->tipoVentaModel;

        if ($q !== '') {
            $builder = $builder
                ->groupStart()
                ->like('nombre_tipo_venta', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        $data = [
            'tipo_ventas' => $builder->paginate($perPage),
            'pager'       => $builder->pager,
            'q'           => $q,
            'perPage'     => $perPage
        ];

        return view('tipo_venta/_tipo_venta_table', $data);
    }

    public function createAjax()
    {
        $tipoVentaModel = new TipoVentaModel();
        $session = session();
        $data = [
            'tipo_venta' => $this->request->getPost('tipo_venta'),
            'descripcion' => $this->request->getPost('descripcion'),
        ];

        if (empty($data['tipo_venta'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El tipo de venta es obligatorio.'
            ]);
        }

        try {
            $id = $tipoVentaModel->insert($data);

            if (!$id) {
                throw new \Exception('No se pudo guardar el tipo de venta.');
            }
            registrar_bitacora(
                'Creación de tipo de venta',
                'Paquetería',
                'Se creó el tipo de venta ' . esc($data['tipo_venta']) . ' en el registro de paquete.',
                $session->get('user_id')
            );

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id' => $id,
                    'text' => $data['tipo_venta']
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

        // SOLO SELECT2
        if ($this->request->getGet('select2')) {

            $tipoVentas = $this->tipoVentaModel->searchTipoVentas($term);

            $results = [];

            foreach ($tipoVentas as $t) {
                $results[] = [
                    'id'   => $t->id,
                    'text' => $t->nombre_tipo_venta
                ];
            }

            return $this->response->setJSON([
                'results' => $results
            ]);
        }

        // si entra aquí es porque llamaste sin select2
        return $this->response->setJSON(['results' => []]);
    }
}
