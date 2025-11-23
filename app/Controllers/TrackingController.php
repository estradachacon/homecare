<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PackageModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TrackingHeaderModel;
use App\Models\TrackingDetailsModel;
use App\Models\RouteModel;

class TrackingController extends BaseController
{
    protected $trackingHeaderModel;
    protected $trackingDetailsModel;
    protected $routeModel;

    public function __construct()
    {
        $this->trackingDetailsModel = new TrackingDetailsModel();
        $this->trackingHeaderModel = new TrackingHeaderModel();
        $this->routeModel = new RouteModel();
    }
    public function index()
    {
        $perPage = 10;
        $routeModel = new RouteModel();
        // Filtros
        $filter_motorista_id = $this->request->getGet('motorista_id');
        $filter_status = $this->request->getGet('status'); // ← múltiple
        $filter_ruta_id = $this->request->getGet('ruta_id');
        $filter_date_from = $this->request->getGet('date_from');
        $filter_date_to = $this->request->getGet('date_to');
        $filter_search_id = $this->request->getGet('search_id');

        // Builder
        $builder = $this->trackingHeaderModel
            ->select('tracking_header.*, users.user_name AS motorista_name, routes.route_name AS route_name')
            ->join('users', 'users.id = tracking_header.user_id', 'left')
            ->join('routes', 'routes.id = tracking_header.route_id', 'left')
            ->orderBy('tracking_header.date', 'DESC')
            ->orderBy('tracking_header.id', 'ASC');

        // Filtros
        if (!empty($filter_motorista_id))
            $builder->where('tracking_header.user_id', $filter_motorista_id);
        if (!empty($filter_ruta_id))
            $builder->where('tracking_header.route_id', $filter_ruta_id);
        if (!empty($filter_status) && is_array($filter_status))
            $builder->whereIn('tracking_header.status', $filter_status);
        if (!empty($filter_date_from))
            $builder->where('tracking_header.date >=', $filter_date_from);
        if (!empty($filter_date_to))
            $builder->where('tracking_header.date <=', $filter_date_to);
        if (!empty($filter_search_id))
            $builder->where('tracking_header.id', $filter_search_id);

        // Resultado paginado
        $trackings = $builder->paginate($perPage);
        $pager = $this->trackingHeaderModel->pager;

        // Motoristas para el select
        $userModel = new \App\Models\UserModel();
        $motoristas = $userModel->where('role_id', 4)->findAll();

        // Lista de estatus
        $statusList = ['asignado', 'finalizado', 'Ruta cancelada'];

        // Pasar filtros a la vista
        return view('trackings/index', [
            'trackings' => $trackings,
            'pager' => $pager,
            'motoristas' => $motoristas,
            'rutas' => $routeModel->orderBy('route_name', 'ASC')->findAll(),
            'statusList' => $statusList,
            'filter_motorista_id' => $filter_motorista_id,
            'filter_ruta_id' => $filter_ruta_id,
            'filter_status' => $filter_status,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'filter_search_id' => $filter_search_id
        ]);
    }
    public function new()
    {
        helper('form');

        // Modelos
        $userModel = new \App\Models\UserModel();
        $rutaModel = new RouteModel();

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
        $packageModel = new PackageModel();

        $motorista_id = $this->request->getPost('motorista_id');
        $fecha = $this->request->getPost('fecha');
        $paquetes = $this->request->getPost('paquetes'); // array
        $status = 'asignado'; // array

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
            'status' => 'asignado',
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
                'status' => 'asignado',
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
    public function show($id)
    {
        $headerModel = new TrackingHeaderModel();
        $detailsModel = new TrackingDetailsModel();

        // Obtener header con motorista y ruta
        $tracking = $headerModel->getHeaderWithRelations($id);

        if (!$tracking) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Tracking ID $id no encontrado");
        }

        // Obtener detalles con info de paquetes
        $detalles = $detailsModel->getDetailsWithPackages($id);

        return view('trackings/show', [
            'tracking' => $tracking,
            'detalles' => $detalles
        ]);
    }
}
