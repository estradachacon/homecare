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

        // Buscar por cualquiera de las partes del nombre o por especialidad
        $doctores = $model
            ->select('id, nombre, especialidad, nombre1, nombre2, apellido1, apellido2')
            ->where('activo', 1)
            ->groupStart()
                ->like('nombre', $q)
                ->orLike('nombre1', $q)
                ->orLike('nombre2', $q)
                ->orLike('apellido1', $q)
                ->orLike('apellido2', $q)
                ->orLike('especialidad', $q)
            ->groupEnd()
            ->orderBy('nombre', 'ASC')
            ->findAll(20);

        $results = [];

        foreach ($doctores as $d) {
            $display = trim(implode(' ', array_filter([
                $d->nombre1 ?? '',
                $d->nombre2 ?? '',
                $d->apellido1 ?? '',
                $d->apellido2 ?? '',
            ])));
            if ($display === '') $display = $d->nombre;

            $results[] = [
                'id'   => $d->id,
                'text' => $display . (!empty($d->especialidad) ? ' | ' . $d->especialidad : ''),
            ];
        }

        return $this->response->setJSON([
            'results' => $results,
        ]);
    }

    private function saveFoto(string $inputName, string $folder): ?string
    {
        $file = $this->request->getFile($inputName);
        if (!$file || !$file->isValid() || strpos($file->getMimeType(), 'image/') !== 0) {
            return null;
        }

        $uploadDir = FCPATH . 'upload/' . trim($folder, '/') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move($uploadDir, $fileName);

        return $fileName;
    }

    public function storeAjax()
    {
        $model = new DoctorModel();

        $n1 = trim($this->request->getPost('nombre1') ?? '');
        $n2 = trim($this->request->getPost('nombre2') ?? '');
        $a1 = trim($this->request->getPost('apellido1') ?? '');
        $a2 = trim($this->request->getPost('apellido2') ?? '');

        if ($n1 === '' || $a1 === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nombre y primer apellido son obligatorios.',
            ]);
        }

        $fullName = trim(implode(' ', array_filter([$n1, $n2, $a1, $a2])));

        $data = [
            'nombre'       => $fullName,
            'nombre1'      => $n1 ?: null,
            'nombre2'      => $n2 ?: null,
            'apellido1'    => $a1 ?: null,
            'apellido2'    => $a2 ?: null,
            'especialidad' => $this->request->getPost('especialidad') ?: null,
            'telefono'     => $this->request->getPost('telefono') ?: null,
            'correo'       => $this->request->getPost('correo') ?: null,
            'activo'       => 1,
        ];

        if ($foto = $this->saveFoto('foto', 'doctores')) {
            $data['foto'] = $foto;
        }

        $id = $model->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'doctor' => [
                'id'   => $id,
                'text' => $data['nombre'] . (!empty($data['especialidad']) ? ' | ' . $data['especialidad'] : ''),
            ],
        ]);
    }

    public function index()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $model = new DoctorModel();
        $q     = trim($this->request->getGet('q') ?? '');

        $query = $model->where('activo', 1);
        if ($q !== '') {
            $query->groupStart()
                ->like('nombre', $q)
                ->orLike('especialidad', $q)
            ->groupEnd();
        }

        $doctores = $query->orderBy('nombre', 'ASC')->paginate(20);
        $pager    = $model->pager;

        return view('doctores/index', [
            'doctores' => $doctores,
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

        $model  = new DoctorModel();
        $id  = (int)$this->request->getPost('id');

        $n1 = trim($this->request->getPost('nombre1') ?? '');
        $n2 = trim($this->request->getPost('nombre2') ?? '');
        $a1 = trim($this->request->getPost('apellido1') ?? '');
        $a2 = trim($this->request->getPost('apellido2') ?? '');

        if ($n1 === '' || $a1 === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Nombre y primer apellido son obligatorios.']);
        }

        $fullName = trim(implode(' ', array_filter([$n1, $n2, $a1, $a2])));

        $data = [
            'nombre'       => $fullName,
            'nombre1'      => $n1 ?: null,
            'nombre2'      => $n2 ?: null,
            'apellido1'    => $a1 ?: null,
            'apellido2'    => $a2 ?: null,
            'especialidad' => $this->request->getPost('especialidad') ?: null,
            'telefono'     => $this->request->getPost('telefono') ?: null,
            'correo'       => $this->request->getPost('correo') ?: null,
            'activo'       => 1,
        ];

        if ($foto = $this->saveFoto('foto', 'doctores')) {
            $data['foto'] = $foto;
        }

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

        (new DoctorModel())->update($id, ['activo' => 0]);

        return $this->response->setJSON(['success' => true]);
    }
}
