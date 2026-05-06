<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PacienteModel;

class PacientesController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $model = new PacienteModel();
        $q     = trim($this->request->getGet('q') ?? '');

        $query = $model->where('activo', 1);

        if ($q !== '') {
            $query->groupStart()
                ->like('nombre', $q)
                ->orLike('identificacion', $q)
            ->groupEnd();
        }

        $pacientes = $query->orderBy('nombre', 'ASC')->paginate(20);
        $pager     = $model->pager;

        return view('pacientes/index', [
            'pacientes' => $pacientes,
            'pager'     => $pager,
            'q'         => $q,
        ]);
    }

    public function guardar()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $model  = new PacienteModel();
        $id     = (int)$this->request->getPost('id');
        $nombre = trim($this->request->getPost('nombre') ?? '');

        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es obligatorio.']);
        }

        $data = [
            'nombre'         => $nombre,
            'identificacion' => $this->request->getPost('identificacion') ?: null,
            'telefono'       => $this->request->getPost('telefono')       ?: null,
            'correo'         => $this->request->getPost('correo')         ?: null,
            'activo'         => 1,
        ];

        if ($id) {
            $model->update($id, $data);
        } else {
            $id = $model->insert($data);
        }

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    public function eliminar(int $id)
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        (new PacienteModel())->update($id, ['activo' => 0]);

        return $this->response->setJSON(['success' => true]);
    }

    public function searchAjax()
    {
        $q      = trim($this->request->getGet('q') ?? '');
        $model  = new PacienteModel();

        $pacientes = $model
            ->select('id, nombre, identificacion')
            ->where('activo', 1)
            ->groupStart()
                ->like('nombre', $q)
                ->orLike('identificacion', $q)
            ->groupEnd()
            ->orderBy('nombre', 'ASC')
            ->findAll(20);

        $results = [];

        foreach ($pacientes as $p) {
            $text = $p->nombre;
            if (!empty($p->identificacion)) {
                $text .= ' | ' . $p->identificacion;
            }
            $results[] = ['id' => $p->id, 'text' => $text];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    public function storeAjax()
    {
        $model  = new PacienteModel();
        $nombre = trim($this->request->getPost('nombre') ?? '');

        if ($nombre === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El nombre del paciente es obligatorio.',
            ]);
        }

        $data = [
            'nombre'         => $nombre,
            'identificacion' => $this->request->getPost('identificacion') ?: null,
            'telefono'       => $this->request->getPost('telefono')       ?: null,
            'correo'         => $this->request->getPost('correo')         ?: null,
            'activo'         => 1,
        ];

        $id = $model->insert($data);

        $text = $data['nombre'];
        if (!empty($data['identificacion'])) {
            $text .= ' | ' . $data['identificacion'];
        }

        return $this->response->setJSON([
            'success'  => true,
            'paciente' => ['id' => $id, 'text' => $text],
        ]);
    }
}
