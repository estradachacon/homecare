<?php

function isLoggedIn()
{
    return session()->get('logged_in') === true;
}

function tienePermiso($accion)
{
    refrescarPermisos();

    $permisos = session()->get('permisos');

    if (!$permisos || !is_array($permisos)) {
        return false;
    }

    return !empty($permisos[$accion]);
}

function requerirPermiso($accion)
{
    refrescarPermisos();

    if (!tienePermiso($accion)) {
        session()->setFlashdata(
            'permiso_error',
            'Necesita permisos para acceder a este módulo.'
        );

        return redirect()->to('/dashboard');
    }

    return true;
}

function refrescarPermisos()
{
    if (!session()->get('logged_in')) {
        return;
    }

    // ⛔ Ya refrescados en este request
    if (session()->get('_permisos_refrescados')) {
        return;
    }

    $roleId = session()->get('role_id');

    $permisoModel = new \App\Models\PermisoRolModel();
    $permisos = $permisoModel->getPermisosPorRol($roleId);

    session()->set([
        'permisos' => array_column($permisos, 'habilitado', 'nombre_accion'),
        '_permisos_refrescados' => true
    ]);
}

