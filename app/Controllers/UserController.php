<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected $cashierModel;
    protected $branchModel;
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        // Inicializa los modelos para su uso en las funciones
        $this->cashierModel = new \App\Models\CashierModel();
        $this->branchModel = new \App\Models\BranchModel();
        $this->userModel = new UserModel();
        $this->roleModel = new \App\Models\RoleModel();
    }
    public function index()
    {
        $chk = requerirPermiso('ver_usuarios');
        if ($chk !== true) return $chk;

        // 1. Instanciamos el modelo de usuario.
        $userModel = new UserModel();

        $users = $userModel
            ->select('users.id, users.user_name, users.email, roles.nombre AS role_name, branches.branch_name AS branch_name')
            ->join('roles', 'roles.id = users.role_id')
            ->join('branches', 'branches.id = users.branch_id')
            ->findAll();

        // 3. Preparamos los datos para la vista.
        $data = [
            'users' => $users,
            'title' => 'Lista de Usuarios' // Título para la página/layout
        ];

        // 4. Cargamos la vista. Asegúrate de que esta ruta sea correcta para tu proyecto.
        return view('users/index', $data);
    }
    public function new()
    {
        $chk = requerirPermiso('crear_usuarios');
        if ($chk !== true) return $chk;

        $branches = $this->branchModel->findAll();
        $roles = $this->roleModel->findAll();
        $users = $this->userModel->findAll();

        $data = [
            'title' => 'Crear usuario',
            'branches' => $branches,
            'roles' => $roles,
            'users' => $users
        ];
        return view('users/new', $data);
    }
    public function create()
    {
        $chk = requerirPermiso('crear_usuarios');
        if ($chk !== true) return $chk;

        helper(['form']);
        $session = session();

        $password = $this->request->getPost('user_password');

        // Hashear la contraseña antes de guardar
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $data = [
            'user_name' => $this->request->getPost('user_name'),
            'email' => $this->request->getPost('email'),
            'user_password' => $hashedPassword,
            'role_id' => $this->request->getPost('role_id'),
            'branch_id' => $this->request->getPost('branch_id'),
        ];

        $this->userModel->insert($data);

        registrar_bitacora(
            'Crear usuario',
            'Usuarios',
            'Se creó un nuevo usuario.',
            $session->get('user_id')
        );

        return redirect()->to('/users')->with('success', 'Usuario creado exitosamente.');
    }
    public function edit($id)
    {
        // 1. Obtener la caja a editar
        $users = $this->userModel->find($id);

        if (!$users) {
            return redirect()->to('/users')->with('error', 'Usuario no encontrado.');
        }

        // 2. Obtener la lista de ramas y usuarios (para los dropdowns)
        $branches = $this->branchModel->findAll();
        $roles = $this->roleModel->findAll();

        $data = [
            'user' => $users,
            'branches' => $branches,
            'roles' => $roles,
        ];

        // Se asume que tienes una vista en 'users/edit'
        return view('users/edit', $data);
    }

    public function update($id)
    {
        helper(['form']);
        $session = session();

        if (
            !$this->validate([
                'user_name' => 'required|min_length[3]|max_length[100]',
                'email' => 'required|min_length[3]|max_length[100]',
                'branch_id' => 'required|integer',
                'role_id' => 'required|integer',
            ])
        ) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'user_name' => $this->request->getPost('user_name'),
            'email' => $this->request->getPost('email'),
            'branch_id' => $this->request->getPost('branch_id'),
            'role_id' => $this->request->getPost('role_id'),
        ];

        $password = $this->request->getPost('user_password');
        if (!empty($password)) {
            $data['user_password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);

        registrar_bitacora(
            'Editar usuario',
            'Usuarios',
            'Se editó el usuario con ID ' . esc($id) . '.',
            $session->get('user_id')
        );

        return redirect()->to('/users')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function delete()
    {

        helper(['form']);
        $session = session();
        $id = $this->request->getPost('id');
        $userModel = new UserModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }

        if ($userModel->delete($id)) {
            registrar_bitacora(
                'Eliminó usuario',
                'Usuarios',
                'Se eliminó el usuario con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo eliminar el usuario.']);
    }
}
