<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TrackingHeaderModel;
use App\Models\TrackingDetailsModel;

class TrackingRendicionController extends BaseController
{
    protected $headerModel;
    protected $detailModel;

    public function __construct()
    {
        $this->headerModel = new TrackingHeaderModel();
        $this->detailModel = new TrackingDetailsModel();
    }

    public function index($trackingId)
    {
        $header = $this->headerModel->getHeaderWithRelations($trackingId);
        $userModel = new \App\Models\UserModel();

        $motoristas = $userModel
            ->where('role_id', 4)
            ->orderBy('user_name', 'ASC')
            ->findAll();

        if (!$header) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Tracking ID $trackingId no encontrado");
        }

        // $paquetes incluirá ahora el vendedor
        $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);

        // Buscar el nombre del motorista correspondiente
        $motoristaNombre = '';
        foreach ($motoristas as $m) {
            if ($m['id'] == $header->user_id) {
                $motoristaNombre = $m['user_name'];
                break;
            }
        }

        return view('trackings/rendicion_index', [
            'tracking' => $header,
            'paquetes' => $paquetes,
            'motoristaNombre' => $motoristaNombre, // enviar nombre
        ]);
    }
public function save()
{
    $trackingId = $this->request->getPost('tracking_id');
    $regresados = $this->request->getPost('regresados') ?? [];

    // Cargar los paquetes asociados al tracking
    $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);

    $packageModel = new \App\Models\PackageModel();

    foreach ($paquetes as $p) {
        // Contar destinos (solo aplica para tipo_servicio = 3)
        $destinoCount = 1; // mínimo 1
        if ($p->tipo_servicio == 3) {
            if (!empty($p->destino_personalizado)) $destinoCount++;
            if (!empty($p->puntofijo_nombre)) $destinoCount++;
        }

        // Determinar nuevo estatus
        if (in_array($p->id, $regresados)) {
            // No exitoso
            if ($p->tipo_servicio == 3) {
                $newStatus = ($destinoCount == 1) ? 'recolecta_fallida' : 'no_retirado';
            } else {
                $newStatus = 'no_retirado';
            }
        } else {
            // Exitoso
            if ($p->tipo_servicio == 3) {
                $newStatus = ($destinoCount == 1) ? 'recolectado' : 'entregado';
            } else {
                $newStatus = 'entregado';
            }
        }

        // Actualizar estatus en la tabla packages
        $packageModel->update($p->package_id, ['estatus' => $newStatus]);
    }

    // Finalmente, marcar el tracking como finalizado
    $this->headerModel->update($trackingId, ['status' => 'finalizado']);

    return redirect()->to('tracking/' . $trackingId)
        ->with('success', 'Rendición procesada con éxito.');
}

}
