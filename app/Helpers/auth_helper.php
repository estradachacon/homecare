<?php

function isLoggedIn()
{
    return session()->get('logged_in') === true;
}

function tienePermiso($accion)
{
    $permisos = session()->get('permisos');

    // Si no hay permisos en sesión, negar acceso
    if (!$permisos || !is_array($permisos)) {
        return false;
    }

    // Retorna true solo si existe el permiso y está en 1
    return isset($permisos[$accion]) && $permisos[$accion] == 1;
}

function requerirPermiso($accion)
{
    if (!tienePermiso($accion)) {
        session()->setFlashdata('permiso_error', 'Necesita permisos para acceder a este módulo.');
        return redirect()->to('/dashboard');  // Ruta del dashboard
    }

    return true;
}