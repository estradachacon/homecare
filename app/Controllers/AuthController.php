<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PermisoRolModel;
use App\Models\PasswordResetModel;

class AuthController extends BaseController
{
    protected $userModel;
    protected $passwordResetModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->passwordResetModel = new PasswordResetModel();
    }
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
        $user_complete = $userModel->getUserWithRoleAndBranch($user['email']);
        $permisos = $permisoModel->getPermisosPorRol($user['role_id']);

        $sessionData = [
            'id' => $user_complete['id'],
            'user_name' => $user_complete['user_name'],
            'email' => $user_complete['email'],
            'foto' => $user_complete['foto'] ?? null,
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

        // 1️⃣ Guardar datos antes de destruir la sesión
        $user_id   = $session->get('user_id');
        $user_name = $session->get('user_name');

        // 2️⃣ Registrar bitácora
        registrar_bitacora(
            'Cerrar sesión',
            'Autenticacion',
            'Cerró sesión.',
            $user_id
        );

        // 3️⃣ Destruir sesión COMPLETA
        $session->destroy();

        return redirect()->to('/');
    }

    // Método para mostrar el formulario de login
    public function showLogin()
    {
        return view('auth/login');
    }

    public function sendResetCode()
    {
        $email = trim($this->request->getPost('email'));

        if (empty($email)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Correo requerido'
            ]);
        }

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Correo no registrado'
            ]);
        }

        $code = random_int(100000, 999999);

        $this->passwordResetModel
            ->where('user_id', $user['id'])
            ->delete();

        $this->passwordResetModel->insert([
            'user_id'    => $user['id'],
            'code'       => $code,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        ]);

        $emailService = \Config\Services::email();
        $emailService->setTo($user['email']);
        $emailService->setSubject('Recuperación de contraseña');
        $emailService->setMessage("
        <p>Tu código de recuperación es:</p>
        <h2>{$code}</h2>
        <p>Expira en 10 minutos.</p>
    ");
        $emailService->setFrom(
            'gerente@fcencomiendas.net',
            'FC Encomiendas'
        );
        if (!$emailService->send()) {

            log_message('error', print_r($emailService->printDebugger(['headers']), true));

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al enviar correo, revisa logs'
            ]);
        }
    }

    /**
     * Paso 2: Verificar código
     */
    public function verifyResetCode()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $email = $this->request->getPost('email');
        $code  = $this->request->getPost('code');

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON(['success' => false]);
        }

        $reset = $this->passwordResetModel
            ->where('user_id', $user['id'])
            ->where('code', $code)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$reset) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Código inválido o expirado'
            ]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Paso 3: Cambiar contraseña
     */
    public function resetPassword()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('user_password');

        if (strlen($password) < 6) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres'
            ]);
        }

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON(['success' => false]);
        }

        $this->userModel->update($user['id'], [
            'user_password' => password_hash($password, PASSWORD_DEFAULT)
        ]);


        // Eliminar código usado
        $this->passwordResetModel
            ->where('user_id', $user['id'])
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }
}
