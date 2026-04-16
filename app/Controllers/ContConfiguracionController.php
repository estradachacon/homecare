<?php

namespace App\Controllers;

use App\Models\ContConfiguracionModel;
use App\Models\ContPlanCuentasModel;

class ContConfiguracionController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('configurar_contabilidad');
        if ($chk !== true) return $chk;

        $configModel  = new ContConfiguracionModel();
        $cuentasModel = new ContPlanCuentasModel();

        $config  = $configModel->getConfig();
        $cuentas = $cuentasModel->getCuentasMovimiento();

        return view('contabilidad/configuracion/index', [
            'config'  => $config,
            'cuentas' => $cuentas,
        ]);
    }

    public function guardar()
    {
        $chk = requerirPermiso('configurar_contabilidad');
        if ($chk !== true) return $chk;

        $model = new ContConfiguracionModel();

        $data = [
            'cuenta_caja_id'         => $this->request->getPost('cuenta_caja_id')         ?: null,
            'cuenta_banco_id'        => $this->request->getPost('cuenta_banco_id')        ?: null,
            'cuenta_cxc_id'          => $this->request->getPost('cuenta_cxc_id')          ?: null,
            'cuenta_cxp_id'          => $this->request->getPost('cuenta_cxp_id')          ?: null,
            'cuenta_inventario_id'   => $this->request->getPost('cuenta_inventario_id')   ?: null,
            'cuenta_ventas_id'       => $this->request->getPost('cuenta_ventas_id')       ?: null,
            'cuenta_costos_id'       => $this->request->getPost('cuenta_costos_id')       ?: null,
            'cuenta_gastos_admin_id' => $this->request->getPost('cuenta_gastos_admin_id') ?: null,
            'cuenta_gastos_venta_id' => $this->request->getPost('cuenta_gastos_venta_id') ?: null,
            'cuenta_resultado_id'    => $this->request->getPost('cuenta_resultado_id')    ?: null,
            'cuenta_capital_id'      => $this->request->getPost('cuenta_capital_id')      ?: null,
            'moneda'                 => $this->request->getPost('moneda')   ?: 'USD',
            'digitos_decimales'      => (int)($this->request->getPost('digitos_decimales') ?: 2),
        ];

        if ($model->guardar($data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Configuración guardada correctamente']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Error al guardar la configuración']);
    }
}
