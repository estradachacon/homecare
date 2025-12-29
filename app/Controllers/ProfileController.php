<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $session = session();
        $userId = $session->get('id');

        if (!$userId) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($userId);

        return view('profile/index', [
            'user' => $user
        ]);
    }

    public function update()
    {
        $session = session();
        $userId = $session->get('id');

        $foto = $this->request->getFile('foto');
        $fotoName = null;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $fotoName = $foto->getRandomName();
            $foto->move('upload/perfiles', $fotoName);
        }

        $data = [
            'user_name' => $this->request->getPost('nombre'),
        ];

        if ($data['user_name']) {
            $session->set('user_name', $data['user_name']);
        }

        if ($fotoName) {
            $data['foto'] = $fotoName;
            $session->set('foto', $fotoName);
        }

        $password = $this->request->getPost('password');

        if (!empty($password)) {
            $data['user_password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $this->userModel->update($userId, $data);

        return redirect()->to('/perfil')->with('success', 'Perfil actualizado');
    }
}
