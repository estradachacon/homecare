<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TipoNotaModel;

class TipoNotas extends BaseController
{
    public function searchAjax()
    {
        $q = trim($this->request->getGet('q') ?? '');
        $model = new TipoNotaModel();

        $items = $model->where('activo', 1)
            ->like('nombre', $q)
            ->orderBy('nombre', 'ASC')
            ->findAll(50);

        $results = [];
        foreach ($items as $it) {
            $results[] = ['id' => $it->id, 'text' => $it->nombre];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    public function storeAjax()
    {
        $model = new TipoNotaModel();
        $nombre = trim($this->request->getPost('nombre') ?? '');
        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es obligatorio.']);
        }

        $id = $model->insert(['nombre' => $nombre, 'activo' => 1]);

        return $this->response->setJSON(['success' => true, 'tipo_nota' => ['id' => $id, 'text' => $nombre]]);
    }

    public function index()
    {
        $chk = requerirPermiso('ver_tipo_notas');
        if ($chk !== true) return $chk;

        $model = new TipoNotaModel();
        $items = $model->orderBy('nombre','ASC')->paginate(50);
        $pager = $model->pager;

        return view('tipo_notas/index', ['items' => $items, 'pager' => $pager]);
    }

    public function guardar()
    {
        $chk = requerirPermiso('ver_tipo_notas');
        if ($chk !== true) return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);

        $model = new TipoNotaModel();
        $id = (int)$this->request->getPost('id');
        $nombre = trim($this->request->getPost('nombre') ?? '');
        if ($nombre === '') return $this->response->setJSON(['success' => false, 'message' => 'El nombre es obligatorio.']);

        $data = ['nombre' => $nombre, 'activo' => 1];
        if ($id) $model->update($id, $data); else $id = $model->insert($data);

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    public function eliminar(int $id)
    {
        $chk = requerirPermiso('ver_tipo_notas');
        if ($chk !== true) return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);

        (new TipoNotaModel())->update($id, ['activo' => 0]);
        return $this->response->setJSON(['success' => true]);
    }
}
