<?php

namespace App\Controllers;

use App\Models\CashierModel;
use CodeIgniter\Controller;

class CashierController extends Controller
{
    // Carga los modelos necesarios para asegurar que se usen correctamente
    protected $cashierModel;
    protected $branchModel;
    protected $userModel;

    public function __construct()
    {
        // Inicializa los modelos para su uso en las funciones
        $this->cashierModel = new CashierModel();
        $this->branchModel = new \App\Models\BranchModel();
        $this->userModel = new \App\Models\UserModel();
    }
    
    /**
     * Muestra el listado de todas las cajas con información de sucursal y usuario.
     */
    public function index()
    {
        $cashiers = $this->cashierModel
            ->select('cashier.*, users.user_name, branches.branch_name') 
            ->join('users', 'users.id = cashier.user_id', 'left') 
            ->join('branches', 'branches.id = cashier.branch_id')
            ->findAll();

        $data = [
            'title' => 'Listado de Cajas',
            'cashiers' => $cashiers 
        ];

        return view('cashier/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva caja.
     */
    public function new()
    {
        $branches = $this->branchModel->findAll();
        $users = $this->userModel->findAll();
        
        $data = [
            'title' => 'Crear caja',
            'branches' => $branches,
            'users' => $users
        ];
        return view('cashier/new', $data);
    }

    /**
     * Guarda la información de la nueva caja en la base de datos.
     */
    public function create()
    {
        helper(['form']);
        $session = session();
        $data = [
            'name' => $this->request->getPost('name'),
            'initial_balance' => $this->request->getPost('initial_balance'),
            'branch_id' => $this->request->getPost('branch_id'),
            'user_id' => $this->request->getPost('user_id'),
        ];

        $this->cashierModel->insert($data);
        registrar_bitacora(
            'Crear caja',
            'Caja',
            'Se creó una nueva caja.',
            $session->get('user_id')
        );
        return redirect()->to('/cashiers')->with('success', 'Caja creada exitosamente.');
    }

    // --- FUNCIONES DE EDICIÓN Y ELIMINACIÓN SOLICITADAS ---

    /**
     * Muestra el formulario de edición con los datos de una caja específica.
     * @param int $id El ID de la caja a editar.
     */
    public function edit($id)
    {
        // 1. Obtener la caja a editar
        $cashier = $this->cashierModel->find($id);

        if (!$cashier) {
            return redirect()->to('/cashiers')->with('error', 'Caja no encontrada.');
        }

        // 2. Obtener la lista de ramas y usuarios (para los dropdowns)
        $branches = $this->branchModel->findAll();
        $users = $this->userModel->findAll();

        $data = [
            'cashier' => $cashier,
            'branches' => $branches,
            'users' => $users,
        ];

        // Se asume que tienes una vista en 'cashier/edit'
        return view('cashier/edit', $data);
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
        'name' => 'required|min_length[3]|max_length[100]',
        'initial_balance' => 'required|numeric',
        'branch_id' => 'required|integer',
        'user_id' => 'required|integer',
    ])) {
        // 2. Si la validación falla, redirigir de vuelta al formulario con los errores
        return redirect()->back()
            ->withInput() // Mantiene los datos que el usuario ingresó
            ->with('errors', $this->validator->getErrors()); // Envía los errores a la vista
    }

    // 3. Si la validación es exitosa, se procede a la actualización
    $data = [
        'name' => $this->request->getPost('name'),
        'initial_balance' => $this->request->getPost('initial_balance'),
        'branch_id' => $this->request->getPost('branch_id'),
        'user_id' => $this->request->getPost('user_id'),
    ];

    $this->cashierModel->update($id, $data);
    registrar_bitacora(
        'Editar caja',
        'Caja',
        'Se editó la caja con ID ' . esc($id) . '.',
        $session->get('user_id')
    );
    return redirect()->to('/cashiers')->with('success', 'Caja actualizada exitosamente.');
}

    /**
     * Elimina una caja de la base de datos.
     * @param int $id El ID de la caja a eliminar.
     */
    public function delete()
    {
        $id = $this->request->getPost('id');
        $cashierModel = new CashierModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }

        if ($cashierModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Caja eliminada correctamente.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo eliminar la caja.']);
    }
}
