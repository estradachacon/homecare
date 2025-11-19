<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PackageModel;
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
        $statusList = ['Asignado a motorista', 'Ruta finalizada', 'Ruta cancelada'];

        return view('trackings/index', [
            'trackings' => $trackings,
            'pager' => $pager,
            'motoristas' => $motoristas,
            'statusList' => $statusList,
            'filter_motorista_id' => $filter_motorista_id,
            'filter_status' => $filter_status
        ]);
    }
    public function new()
    {
        helper('form');

        // Modelos
        $userModel = new \App\Models\UserModel();
        $rutaModel = new \App\Models\RouteModel();

        // Motoristas (usuarios tipo motorista)
        $motoristas = $userModel
            ->where('role_id', 4)
            ->orderBy('user_name', 'ASC')
            ->findAll();

        // Rutas
        $rutas = $rutaModel
            ->orderBy('route_name', 'ASC')
            ->findAll();

        $data = [
            'motoristas' => $motoristas,
            'rutas' => $rutas,
        ];

        return view('trackings/new', $data);
    }
    public function getPendientesPorRuta($rutaId)
    {
        $paqueteModel = new PackageModel();

        $paquetes = $paqueteModel
            ->select('
            packages.*, 
            sellers.seller AS vendedor, 
            routes.route_name AS ruta_nombre, 
            settled_points.point_name AS punto_fijo_nombre
        ')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->join('routes', 'routes.id = settled_points.ruta_id', 'left')
            ->where('settled_points.ruta_id', $rutaId)
            ->where('packages.estatus', 'pendiente')
            ->findAll();

        return $this->response->setJSON($paquetes);
    }

    public function getTodosPendientes()
    {
        $paqueteModel = new PackageModel();

        $paquetes = $paqueteModel
            ->select('
            packages.*, 
            sellers.seller AS vendedor, 
            routes.route_name AS ruta_nombre, 
            settled_points.point_name AS punto_fijo_nombre
        ')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->join('routes', 'routes.id = settled_points.ruta_id', 'left')
            ->where('packages.estatus', 'pendiente')
            ->findAll();

        return $this->response->setJSON($paquetes);
    }

    public function store()
    {
        helper(['form']);
        $session = session();

        $headerModel = new TrackingHeaderModel();
        $detailModel = new TrackingDetailsModel();
        $packageModel = new \App\Models\PackageModel();

        $motorista_id = $this->request->getPost('motorista_id');
        $fecha = $this->request->getPost('fecha');
        $paquetes = $this->request->getPost('paquetes'); // array

        if (empty($motorista_id) || empty($paquetes)) {
            return redirect()->back()->with('error', 'Motorista y paquetes son requeridos')->withInput();
        }

        // ruta_id puede venir vacío
        $ruta_id = $this->request->getPost('ruta_id') ?: null;

        // Guardar header
        $idHeader = $headerModel->insert([
            'user_id' => $motorista_id,
            'route_id' => $ruta_id,
            'date' => $fecha ?: date('Y-m-d'),
            'status' => 'Asignado a motorista',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$idHeader) {
            return redirect()->back()->with('error', 'No se pudo crear el tracking.');
        }

        // Guardar detalles y actualizar paquetes
        foreach ($paquetes as $pid) {
            $detailModel->insert([
                'tracking_header_id' => $idHeader,
                'package_id' => $pid,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // actualizar package: asignado (ejemplo)
            $packageModel->update($pid, [
                'estatus' => 'asignado',
                'tracking_id' => $idHeader
            ]);
        }

        // Registrar bitácora (ajusta a tu helper)
        if (function_exists('registrar_bitacora')) {
            registrar_bitacora(
                'Creación de seguimiento',
                'Paquetería',
                'Seguimiento #' . $idHeader . ' creado con ' . count($paquetes) . ' paquetes.',
                $session->get('user_id')
            );
        }

        return redirect()->to(base_url('tracking/' . $idHeader))->with('success', 'Tracking creado correctamente.');
    }
}
