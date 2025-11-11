<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Si NO está logueado → redirigir al home (con modal)
        if (!$session->get('logged_in')) {
            $session->setFlashdata('alert', [
                'type' => 'error',
                'title' => 'Acceso requerido',
                'message' => 'Por favor inicia sesión'
            ]);
            return redirect()->to('/');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $session = session();

        // ✅ Si YA está logueado, y accede a la raíz o al login, redirigir al dashboard
        $uri = $request->getUri()->getPath();

        if ($session->get('logged_in') && in_array($uri, ['', '/', 'login', 'auth/login'])) {
            return redirect()->to('/dashboard');
        }
    }
}
