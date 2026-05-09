<?php

namespace App\Controllers;

use App\Models\ContTiposPartidaModel;

class ContTiposPartidaController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $model = new ContTiposPartidaModel();

        return view('contabilidad/mantenimientos/tipos_partida', [
            'tipos' => $model->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $model  = new ContTiposPartidaModel();
        $nombre = trim($this->request->getPost('nombre') ?? '');

        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es requerido.']);
        }

        $id = $model->insert([
            'nombre'      => $nombre,
            'descripcion' => $this->request->getPost('descripcion') ?: null,
            'activo'      => 1,
        ]);

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tipo de partida creado.',
            'tipo'    => $model->find($id),
        ]);
    }

    public function update($id)
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $model  = new ContTiposPartidaModel();
        $nombre = trim($this->request->getPost('nombre') ?? '');

        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es requerido.']);
        }

        $model->update((int)$id, [
            'nombre'      => $nombre,
            'descripcion' => $this->request->getPost('descripcion') ?: null,
            'activo'      => (int)($this->request->getPost('activo') ?? 1),
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Actualizado correctamente.']);
    }

    public function delete($id)
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $model = new ContTiposPartidaModel();
        $model->update((int)$id, ['activo' => 0]);

        return $this->response->setJSON(['success' => true, 'message' => 'Tipo de partida desactivado.']);
    }

    public function search()
    {
        $model = new ContTiposPartidaModel();
        $q     = $this->request->getGet('q') ?? '';

        return $this->response->setJSON(['results' => $model->searchAjax($q)]);
    }
}
