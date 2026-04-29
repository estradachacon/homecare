<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DoctorModel;

class Doctores extends BaseController
{
    public function searchAjax()
    {
        $q = trim($this->request->getGet('q') ?? '');

        $model = new DoctorModel();

        $doctores = $model
            ->select('id, nombre, especialidad')
            ->where('activo', 1)
            ->groupStart()
                ->like('nombre', $q)
                ->orLike('especialidad', $q)
            ->groupEnd()
            ->orderBy('nombre', 'ASC')
            ->findAll(20);

        $results = [];

        foreach ($doctores as $d) {
            $results[] = [
                'id'   => $d->id,
                'text' => $d->nombre . (!empty($d->especialidad) ? ' | ' . $d->especialidad : ''),
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
        ]);
    }

    public function storeAjax()
    {
        $model = new DoctorModel();

        $nombre = trim($this->request->getPost('nombre') ?? '');

        if ($nombre === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El nombre del doctor es obligatorio.',
            ]);
        }

        $data = [
            'nombre'       => $nombre,
            'especialidad' => $this->request->getPost('especialidad') ?: null,
            'telefono'     => $this->request->getPost('telefono') ?: null,
            'correo'       => $this->request->getPost('correo') ?: null,
            'activo'       => 1,
        ];

        $id = $model->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'doctor' => [
                'id'   => $id,
                'text' => $data['nombre'] . (!empty($data['especialidad']) ? ' | ' . $data['especialidad'] : ''),
            ],
        ]);
    }
}