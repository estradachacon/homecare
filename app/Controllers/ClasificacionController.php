<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClasificacionModel;
use App\Models\ProductoModel;

class ClasificacionController extends BaseController
{
    public function lista()
    {
        $lista = (new ClasificacionModel())
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data'    => array_map(fn($c) => ['id' => $c->id, 'nombre' => $c->nombre], $lista),
        ]);
    }

    public function guardar()
    {
        $model  = new ClasificacionModel();
        $data   = $this->request->getJSON(true);
        $id     = (int)($data['id'] ?? 0);
        $nombre = trim($data['nombre'] ?? '');

        if (!$nombre) {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es requerido']);
        }

        $q = $model->where('nombre', $nombre)->where('activo', 1);
        if ($id) {
            $q->where('id !=', $id);
        }
        if ($q->first()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya existe una clasificación con ese nombre']);
        }

        if ($id) {
            $model->update($id, ['nombre' => $nombre]);
            $returnId = $id;
            $msg      = 'Clasificación actualizada';
        } else {
            $returnId = $model->insert(['nombre' => $nombre, 'activo' => 1]);
            $msg      = 'Clasificación creada';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
            'id'      => $returnId,
            'nombre'  => $nombre,
        ]);
    }

    public function eliminar($id)
    {
        $enUso = (new ProductoModel())->where('clasificacion_id', $id)->first();

        if ($enUso) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Esta clasificación está asignada a uno o más productos',
            ]);
        }

        (new ClasificacionModel())->update($id, ['activo' => 0]);

        return $this->response->setJSON(['success' => true, 'message' => 'Clasificación eliminada']);
    }
}
