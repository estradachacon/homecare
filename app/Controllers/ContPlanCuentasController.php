<?php

namespace App\Controllers;

use App\Models\ContPlanCuentasModel;

class ContPlanCuentasController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_plan_cuentas');
        if ($chk !== true) return $chk;

        $model   = new ContPlanCuentasModel();
        $cuentas = $model->getArbol();
        $arbol   = $model->construirArbol($cuentas);

        return view('contabilidad/plan_cuentas/index', [
            'cuentas' => $cuentas,
            'arbol'   => $arbol,
        ]);
    }

    public function store()
    {
        $chk = requerirPermiso('crear_cuenta_contable');
        if ($chk !== true) return $chk;

        $model = new ContPlanCuentasModel();

        $rules = [
            'codigo'          => 'required|max_length[20]|is_unique[cont_plan_cuentas.codigo]',
            'nombre'          => 'required|max_length[150]',
            'tipo'            => 'required|in_list[ACTIVO,PASIVO,CAPITAL,INGRESO,COSTO,GASTO]',
            'naturaleza'      => 'required|in_list[DEUDORA,ACREEDORA]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $data = [
            'codigo'             => strtoupper($this->request->getPost('codigo')),
            'nombre'             => $this->request->getPost('nombre'),
            'tipo'               => $this->request->getPost('tipo'),
            'naturaleza'         => $this->request->getPost('naturaleza'),
            'nivel'              => (int)$this->request->getPost('nivel') ?: 1,
            'cuenta_padre_id'    => $this->request->getPost('cuenta_padre_id') ?: null,
            'acepta_movimientos' => (int)$this->request->getPost('acepta_movimientos'),
            'activo'             => 1,
        ];

        if (!$model->insert($data)) {
            return $this->response->setJSON(['success' => false, 'errors' => $model->errors()]);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Cuenta creada correctamente']);
    }

    public function update($id)
    {
        $chk = requerirPermiso('editar_cuenta_contable');
        if ($chk !== true) return $chk;

        $model  = new ContPlanCuentasModel();
        $cuenta = $model->find($id);

        if (!$cuenta) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cuenta no encontrada']);
        }

        $rules = [
            'nombre'      => 'required|max_length[150]',
            'tipo'        => 'required|in_list[ACTIVO,PASIVO,CAPITAL,INGRESO,COSTO,GASTO]',
            'naturaleza'  => 'required|in_list[DEUDORA,ACREEDORA]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $data = [
            'nombre'             => $this->request->getPost('nombre'),
            'tipo'               => $this->request->getPost('tipo'),
            'naturaleza'         => $this->request->getPost('naturaleza'),
            'nivel'              => (int)$this->request->getPost('nivel') ?: (int)$cuenta->nivel,
            'cuenta_padre_id'    => $this->request->getPost('cuenta_padre_id') ?: null,
            'acepta_movimientos' => (int)$this->request->getPost('acepta_movimientos'),
            'activo'             => (int)$this->request->getPost('activo'),
        ];

        $model->update($id, $data);

        return $this->response->setJSON(['success' => true, 'message' => 'Cuenta actualizada correctamente']);
    }

    public function delete()
    {
        $chk = requerirPermiso('eliminar_cuenta_contable');
        if ($chk !== true) return $chk;

        $id    = $this->request->getPost('id');
        $model = new ContPlanCuentasModel();

        if ($model->tieneHijos($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede eliminar una cuenta con subcuentas']);
        }

        // Verificar si tiene movimientos
        $db = \Config\Database::connect();
        $mov = $db->query('SELECT COUNT(*) AS c FROM cont_asientos_detalle WHERE cuenta_id = ?', [$id])->getRow();
        if ($mov->c > 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede eliminar una cuenta con movimientos']);
        }

        $model->delete($id);
        return $this->response->setJSON(['success' => true, 'message' => 'Cuenta eliminada']);
    }

    public function searchAjax()
    {
        $q     = $this->request->getGet('q') ?? '';
        $model = new ContPlanCuentasModel();
        $rows  = $model->buscarParaSelect2($q);

        $results = array_map(fn($r) => [
            'id'   => $r->id,
            'text' => $r->codigo . ' - ' . $r->nombre,
        ], $rows);

        return $this->response->setJSON(['results' => $results]);
    }

    public function getById($id)
    {
        $model  = new ContPlanCuentasModel();
        $cuenta = $model->find($id);
        return $this->response->setJSON(['cuenta' => $cuenta]);
    }
}
