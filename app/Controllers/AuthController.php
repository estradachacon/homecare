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

        // Verificar si es una petición POST
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Método no permitido.'
            ]);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // DEBUG: Ver qué está llegando
        log_message('debug', 'Login attempt: ' . $username);

        $user = $userModel->where('email', $username)
            ->orWhere('user_name', $username)
            ->first();

        // DEBUG: Ver si encontró usuario
        if ($user) {
            log_message('debug', 'User found: ' . $user['email']);
            log_message('debug', 'Password verify: ' . (password_verify($password, $user['user_password']) ? 'true' : 'false'));
        } else {
            log_message('debug', 'User NOT found');
        }


        // Validaciones básicas
        if (empty($username) || empty($password)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuario y contraseña son requeridos.'
            ]);
        }

        // Buscar por correo o nombre de usuario
        $user_complete = $userModel->getUserWithRoleAndBranch($user['email']);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ]);
        }

        // Verificar contraseña - IMPORTANTE: verificar el nombre de la columna
        if (!password_verify($password, $user['user_password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Contraseña incorrecta.'
            ]);
        }

        // Verificar si el usuario está activo (si tienes ese campo)
        if (isset($user['activo']) && $user['activo'] != 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuario inactivo.'
            ]);
        }

        // Cargar los permisos
        $permisos = $permisoModel->getPermisosPorRol($user['role_id']);

        // Guardar sesión - CORREGIDO: usar 'isLoggedIn' o 'logged_in' consistentemente
        $sessionData = [
            'id'        => $user_complete['id'],
            'user_name' => $user_complete['user_name'],
            'email'     => $user_complete['email'],
            'role_id'   => $user_complete['role_id'],
            'branch_id'   => $user_complete['branch_id'],
            'branch_name'   => $user_complete['branch_name'],
            'branch_direction'   => $user_complete['branch_direction'],
            'permisos'  => array_column($permisos, 'habilitado', 'nombre_accion'),
            'isLoggedIn' => true, // Cambiado para coincidir con tu filtro original
            'logged_in' => true   // O mantener este y cambiar el filtro
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
