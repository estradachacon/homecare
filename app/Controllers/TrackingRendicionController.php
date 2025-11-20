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

        if (!$header) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Tracking ID $trackingId no encontrado");
        }

        $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);

        return view('trackings/rendicion_index', [
            'tracking' => $header,
            'paquetes' => $paquetes
        ]);
    }

    public function save()
    {
        $trackingId = $this->request->getPost('tracking_id');
        $regresados = $this->request->getPost('regresados') ?? [];

        $this->detailModel
            ->where('tracking_header_id', $trackingId)
            ->set(['delivery_status' => 'entregado'])
            ->update();

        if (!empty($regresados)) {
            $this->detailModel
                ->whereIn('id', $regresados)
                ->set(['delivery_status' => 'regresado'])
                ->update();
        }

        return redirect()->to('tracking/' . $trackingId)
            ->with('success', 'Rendición procesada con éxito.');
    }
}
