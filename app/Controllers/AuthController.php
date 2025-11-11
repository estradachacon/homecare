<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PermisoRolModel;

class AuthController extends BaseController
{
    public function login()
{
    helper(['form']);
    $session = session();
    $userModel = new UserModel();
    $permisoModel = new PermisoRolModel();

    if (!$this->request->is('post')) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Método no permitido.'
        ]);
    }

    $username = trim($this->request->getPost('username'));
    $password = trim($this->request->getPost('password'));

    if (empty($username) || empty($password)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Usuario y contraseña son requeridos.'
        ]);
    }

    $user = $userModel
        ->groupStart()
        ->where('email', $username)
        ->orWhere('user_name', $username)
        ->groupEnd()
        ->first();

    if (!$user) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Usuario no encontrado.'
        ]);
    }

    if (!password_verify($password, $user['user_password'])) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Contraseña incorrecta.'
        ]);
    }

    if (isset($user['activo']) && $user['activo'] != 1) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Usuario inactivo.'
        ]);
    }

    // ✅ Ahora sí, aquí ya estamos seguros que $user existe
    $user_complete = $userModel->getUserWithRoleAndBranch($user['email']);
    $permisos = $permisoModel->getPermisosPorRol($user['role_id']);

    $sessionData = [
        'id' => $user_complete['id'],
        'user_name' => $user_complete['user_name'],
        'email' => $user_complete['email'],
        'role_id' => $user_complete['role_id'],
        'branch_id' => $user_complete['branch_id'],
        'branch_name' => $user_complete['branch_name'],
        'branch_direction' => $user_complete['branch_direction'],
        'permisos' => array_column($permisos, 'habilitado', 'nombre_accion'),
        'isLoggedIn' => true,
        'logged_in' => true
    ];

    $session->set($sessionData);

    registrar_bitacora(
        'Iniciar sesión',
        'Autenticacion',
        'Inició sesión.',
        $user['id']
    );

    return $this->response->setJSON([
        'success' => true,
        'message' => 'Inicio de sesión correcto',
        'redirect' => base_url('dashboard')
    ]);
}


    public function logout()
    {
        $session = session();
        $session->destroy();

        $user_id = session('user_id');
        $user_name = session('user_name');

        registrar_bitacora(
            'Cerrar sesión',
            'Autenticacion',
            'Cerró sesión.',
            $user_id
        );
        return redirect()->to('/');
    }

    // Método para mostrar el formulario de login
    public function showLogin()
    {
        return view('auth/login');
    }
}
