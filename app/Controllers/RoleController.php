<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\RoleModel;
use App\Models\PermisoRolModel;

class RoleController extends BaseController
{
    protected $roleModel;
    protected $permisoRolModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permisoRolModel = new PermisoRolModel();
    }

    public function index()
    {
        $chk = requerirPermiso('ver_roles');
        if ($chk !== true) return $chk;

        return view('roles/index', [
            'roles' => $this->roleModel->findAll()
        ]);
    }

    public function new()
    {
        $chk = requerirPermiso('crear_roles');
        if ($chk !== true) return $chk;

        return view('roles/create');
    }

    public function create()
    {
        $session = session();
        $rules = [
            'nombre'       => 'required|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            // Si falla validación, vuelve al formulario con errores
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Si pasa validación, inserta el registro
        $this->roleModel->insert([
            'nombre'      => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion')
        ]);
        registrar_bitacora(
            'Creación de rol',
            'Gestión de usuarios',
            'Se creó el rol: ' . $this->request->getPost('nombre') . '.',
            $session->get('user_id')
        );
        return redirect()->to('/roles')->with('success', 'Rol creado exitosamente.');
    }

    public function edit($id)
    {
        $chk = requerirPermiso('editar_roles');
        if ($chk !== true) return $chk;

        // 1. Obtener la caja a editar
        $route = $this->roleModel->find($id);

        if (!$route) {
            return redirect()->to('/roles')->with('error', 'Rol no encontrado.');
        }

        $data = [
            'roles' => $route,
        ];

        // Se asume que tienes una vista en 'seller/edit'
        return view('roles/edit', $data);
    }

    public function delete()
    {
        $session = session();
        $id = $this->request->getPost('id');
        $roleModel = new RoleModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }
        $role = $roleModel->find($id);
        if ($roleModel->delete($id)) {
            registrar_bitacora(
                'Eliminó rol',
                'Gestión de usuarios',
                'Se eliminó el rol: ' . esc($role['nombre']) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Rol eliminado correctamente.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No se pudo eliminar la sucursal.',
            'csrf'    => [
                'token'  => csrf_hash(),
                'header' => csrf_header(),
            ]
        ]);
    }
    public function update($id)
    {
        helper(['form']);
        $session = session();
        if (!$this->validate([
            'nombre' => 'required|min_length[3]|max_length[100]',
        ])) {
            return redirect()->back()
                ->withInput() // Mantiene los datos que el usuario ingresó
                ->with('errors', $this->validator->getErrors()); // Envía los errores a la vista
        }

        // 3. Si la validación es exitosa, se procede a la actualización
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
        ];

        $this->roleModel->update($id, $data);
        registrar_bitacora(
            'Editó rol',
            'Gestión de usuarios',
            'Se editó el rol con ID ' . esc($id) . '.',
            $session->get('user_id')
        );
        return redirect()->to('/roles')->with('success', 'Rol actualizado exitosamente.');
    }

    public function saveAccess($roleId)
    {
        if (!tienePermiso('ver_roles')) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }

        $permisoRolModel = new PermisoRolModel();

        $permisos = $this->request->getPost('permisos') ?? [];

        // Limpiar permisos anteriores
        $permisoRolModel->where('role_id', $roleId)->delete();

        foreach ($permisos as $accion) {
            $permisoRolModel->insert([
                'role_id'       => $roleId,
                'nombre_accion' => $accion,
                'habilitado'    => 1,
            ]);
        }

        return redirect()->to('/roles')
            ->with('success', 'Permisos actualizados correctamente');
    }


    public function access($roleId)
    {
        // Seguridad básica
        if (!tienePermiso('ver_roles')) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }

        $roleModel = new RoleModel();
        $permisoRolModel = new PermisoRolModel();

        $role = $roleModel->find($roleId);

        if (!$role) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Rol no encontrado');
        }

        // Permisos disponibles (archivo config)
        $permisosDisponibles = config('Permisos')->modulos;


        // Permisos ya asignados al rol
        $permisosRol = $permisoRolModel
            ->where('role_id', $roleId)
            ->where('habilitado', 1)
            ->findColumn('nombre_accion');

        return view('roles/access', [
            'role'                => $role,
            'permisos'            => $permisosDisponibles,
            'permisosAsignados'   => $permisosRol ?? [],
        ]);
    }
}
