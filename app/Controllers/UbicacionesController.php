<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ColoniaModel;
use App\Models\MunicipioModel;

class UbicacionesController extends BaseController
{
    /**
     * 🔍 Select2 - Buscar colonias
     * Retorna: colonia – municipio, departamento
     */
    public function searchColonias()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $term  = $this->request->getGet('q') ?? '';
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $model = new ColoniaModel();

        $results = $model->searchForSelect2($term, $limit);

        return $this->response->setJSON([
            'results' => $results,
            'pagination' => [
                'more' => count($results) === $limit
            ]
        ]);
    }

    /**
     * 🧠 Obtener jerarquía completa por colonia
     * Para backend (asignación, lógica interna)
     */
    public function getColoniaFull($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
        }

        $model = new ColoniaModel();
        $data = $model->getFullLocation((int)$id);

        if (!$data) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrado']);
        }

        return $this->response->setJSON($data);
    }

    /**
     * 📍 Municipios por departamento (cascada opcional)
     */
    public function municipiosByDepartamento($departamentoId = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        if (!$departamentoId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID requerido']);
        }

        $model = new MunicipioModel();

        $data = $model->getByDepartamento((int)$departamentoId);

        return $this->response->setJSON($data);
    }
}
