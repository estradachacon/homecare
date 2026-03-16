<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\QuedanModel;
use App\Models\QuedanFacturaModel;
use App\Models\FacturaHeadModel;

class Quedans extends BaseController
{

    public function index()
    {
        $model = new QuedanModel();

        $data['quedans'] = $model->getQuedansConCliente();

        return view('quedans/index', $data);
    }


    public function crear()
    {
        $chk = requerirPermiso('crear_quedans');
        if ($chk !== true) return $chk;

        return view('quedans/crear');
    }


    public function facturasCliente($clienteId)
    {
        $facturaModel = new FacturaHeadModel();

        $facturas = $facturaModel
            ->select('id, numero_control, fecha_emision, total_pagar, saldo')
            ->where('receptor_id', $clienteId)
            ->where('saldo >', 0)
            ->where('anulada', 0)
            ->orderBy('fecha_emision', 'ASC')
            ->findAll();

        return $this->response->setJSON($facturas);
    }


    public function guardar()
    {
        $chk = requerirPermiso('crear_quedans');
        if ($chk !== true) return $chk;

        $db = \Config\Database::connect();
        $session = session();

        $quedanModel = new QuedanModel();
        $detalleModel = new QuedanFacturaModel();

        $clienteId = $this->request->getPost('cliente_id');
        $numeroQuedan = $this->request->getPost('numero_quedan');
        $fechaPago = $this->request->getPost('fecha_pago');
        $fechaEmision = $this->request->getPost('fecha_emision') ?? date('Y-m-d');
        $facturas = $this->request->getPost('facturas');

        if (empty($facturas)) {
            return redirect()->back()
                ->with('error', 'Debe seleccionar al menos una factura.');
        }

        $db->transStart();

        // Crear quedan
        $quedanId = $quedanModel->insert([
            'numero_quedan' => $numeroQuedan,
            'cliente_id' => $clienteId,
            'fecha_emision' => $fechaEmision,
            'fecha_pago' => $fechaPago,
            'estado' => 'pendiente',
            'total_aplicado' => 0
        ]);

        $total = 0;
        $cantidadFacturas = 0;

        foreach ($facturas as $factura) {

            if (!isset($factura['id']) || !isset($factura['monto'])) {
                continue;
            }

            $facturaId = (int)$factura['id'];
            $monto = (float)$factura['monto'];

            $detalleModel->insert([
                'quedan_id' => $quedanId,
                'factura_id' => $facturaId,
                'monto_aplicado' => $monto
            ]);

            $total += $monto;
            $cantidadFacturas++;
        }

        // Actualizar total del quedan
        $quedanModel->update($quedanId, [
            'total_aplicado' => $total
        ]);

        $db->transComplete();

        // 📝 Bitácora
        registrar_bitacora(
            'Crear quedan',
            'Quedan',
            'Se creó el quedan #' . $numeroQuedan .
                ' por $' . number_format($total, 2) .
                ' con ' . $cantidadFacturas . ' facturas.',
            $session->get('user_id')
        );

        return redirect()->to('/quedans')
            ->with('success', 'Quedan creado correctamente');
    }
    public function show($id)
    {
        $quedanModel = new QuedanModel();
        $detalleModel = new QuedanFacturaModel();

        $data['quedan'] = $quedanModel->getQuedan($id);
        $data['detalles'] = $detalleModel->getFacturasPorQuedan($id);

        return view('quedans/detalle', $data);
    }

    public function anular($id)
    {
        $session = session();

        $chk = requerirPermiso('anular_quedans');

        if ($chk !== true) {

            if ($this->request->isAJAX()) {

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No tiene permiso para anular quedans.'
                ]);
            }

            return $chk;
        }

        $quedanModel = new QuedanModel();

        $quedan = $quedanModel->find($id);

        if (!$quedan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Quedan no encontrado.'
            ]);
        }

        if ($quedan->anulado) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este quedan ya está anulado.'
            ]);
        }

        $quedanModel->update($id, [
            'estado' => 'anulado',
            'anulado' => 1,
            'anulado_por' => $session->get('user_id'),
            'fecha_anulacion' => date('Y-m-d H:i:s')
        ]);

        registrar_bitacora(
            'Anular quedan',
            'Quedan',
            'Se anuló el quedan #' . $quedan->numero_quedan,
            $session->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'El quedan fue anulado correctamente.'
        ]);
    }
}
