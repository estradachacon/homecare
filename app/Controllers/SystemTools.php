<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class SystemTools extends BaseController
{
    public function clearClientData()
    {
        session()->destroy();

        foreach ($_COOKIE as $key => $value) {
            setcookie($key, '', time() - 3600, '/');
            unset($_COOKIE[$key]);
        }

        $cache = Services::cache();
        $cache->clean();

        return redirect()->to('/')
            ->with('success', 'Datos del navegador limpiados correctamente. Recargando sistema...');
    }
    public function logoutAll()
    {
        $session = session();

        // 🔐 Seguridad: SOLO GERENTE / ADMIN
        if (!in_array($session->get('role_id'), [1])) { // ajusta el ID de gerente
            return redirect()->back()
                ->with('error', 'No autorizado');
        }

        // 🔥 Si usas sesiones por archivos (default CI4)
        $path = WRITEPATH . 'session';

        if (is_dir($path)) {
            foreach (glob($path . '/*') as $file) {
                @unlink($file);
            }
        }

        // Registrar bitácora
        registrar_bitacora(
            'Cerrar sesiones',
            'Sistema',
            'Cerró sesión a todos los usuarios.',
            $session->get('id')
        );

        // Cerrar también la sesión del gerente
        $session->destroy();

        return redirect()->to('/')
            ->with('success', 'Todas las sesiones fueron cerradas.');
    }
}
