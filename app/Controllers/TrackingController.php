<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TrackingHeaderModel;
use App\Models\TrackingDetailsModel;

class TrackingController extends BaseController
{
    protected $trackingHeaderModel;
    protected $trackingDetailsModel;

    public function __construct()
    {
        $this->trackingDetailsModel = new TrackingDetailsModel();
        $this->trackingHeaderModel = new TrackingHeaderModel();
    }
    public function index()
    {
        $perPage = 10;

        // Filtros
        $filter_motorista_id = $this->request->getGet('motorista_id');
        $filter_status = $this->request->getGet('status'); // ← múltiple

        // Builder
        $builder = $this->trackingHeaderModel
            ->select('
            tracking_header.*, 
            users.user_name AS motorista_name,
            routes.route_name AS route_name
        ')
            ->join('users', 'users.id = tracking_header.user_id', 'left')
            ->join('routes', 'routes.id = tracking_header.route_id', 'left')
            ->orderBy('tracking_header.date', 'DESC'); // Recientes primero

        // Filtro por motorista
        if (!empty($filter_motorista_id)) {
            $builder->where('tracking_header.user_id', $filter_motorista_id);
        }

        // Filtro múltiple por estado
        if (!empty($filter_status) && is_array($filter_status)) {
            $builder->whereIn('tracking_header.status', $filter_status);
        }

        // Resultado paginado
        $trackings = $builder->paginate($perPage);
        $pager = $this->trackingHeaderModel->pager;

        // Motoristas para el select
        $userModel = new \App\Models\UserModel();
        $motoristas = $userModel->where('role_id', 4)->findAll();

        // Lista de estatus
        $statusList = ['pendiente', 'en_camino', 'retrasado', 'completado', 'cancelado'];

        return view('trackings/index', [
            'trackings' => $trackings,
            'pager' => $pager,
            'motoristas' => $motoristas,
            'statusList' => $statusList,
            'filter_motorista_id' => $filter_motorista_id,
            'filter_status' => $filter_status
        ]);
    }
}
