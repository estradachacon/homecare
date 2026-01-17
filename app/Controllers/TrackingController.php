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
    protected $paqueteModel;

    public function __construct()
    {
        $this->trackingDetailsModel = new TrackingDetailsModel();
        $this->trackingHeaderModel = new TrackingHeaderModel();
        $this->routeModel = new RouteModel();
        $this->paqueteModel = new PackageModel();
    }
    public function index()
    {
        $chk = requerirPermiso('ver_tracking');
        if ($chk !== true) return $chk;

        $perPage = 10;
        $routeModel = new RouteModel();
        $filter_motorista_id = $this->request->getGet('motorista_id');
        $filter_status = $this->request->getGet('status'); 
        $filter_ruta_id = $this->request->getGet('ruta_id');
        $filter_date_from = $this->request->getGet('date_from');
        $filter_date_to = $this->request->getGet('date_to');
        $filter_search_id = $this->request->getGet('search_id');

        $builder = $this->trackingHeaderModel
            ->select('tracking_header.*, users.user_name AS motorista_name, routes.route_name AS route_name')
            ->join('users', 'users.id = tracking_header.user_id', 'left')
            ->join('routes', 'routes.id = tracking_header.route_id', 'left')
            ->orderBy('tracking_header.date', 'DESC')
            ->orderBy('tracking_header.id', 'ASC');

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

        $trackings = $builder->paginate($perPage);
        $pager = $this->trackingHeaderModel->pager;

        $userModel = new \App\Models\UserModel();
        $motoristas = $userModel->where('role_id', 4)->findAll();

        $statusList = ['asignado', 'finalizado'];

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
        $chk = requerirPermiso('crear_tracking');
        if ($chk !== true) return $chk;

        $userModel = new \App\Models\UserModel();
        $rutaModel = new RouteModel();

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
            ->groupStart() 
            ->where('packages.estatus', 'pendiente')
            ->orWhere('packages.estatus', 'recolectado') 
            ->groupEnd()
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
            ->groupStart() 
            ->where('packages.estatus', 'pendiente')
            ->orWhere('packages.estatus', 'recolecta_fallida')
            ->orWhere('packages.estatus', 'recolectado') 
            ->groupEnd()
            ->findAll();

        return $this->response->setJSON($paquetes);
    }

    public function store()
    {
        helper(['form', 'bitacora']);
        $session = session();

        $headerModel = new TrackingHeaderModel();
        $detailModel = new TrackingDetailsModel();
        $packageModel = new PackageModel();

        $motorista_id = $this->request->getPost('motorista_id');
        $fecha_seguimiento = $this->request->getPost('fecha_tracking');
        $paquetes = $this->request->getPost('paquetes'); // array

        if (empty($motorista_id) || empty($paquetes)) {
            return redirect()->back()->with('error', 'Motorista y paquetes son requeridos')->withInput();
        }

        $ruta_id = $this->request->getPost('ruta_id') ?: null;

        $idHeader = $headerModel->insert([
            'user_id' => $motorista_id,
            'route_id' => $ruta_id,
            'date' => $fecha_seguimiento ?: date('Y-m-d'),
            'status' => 'asignado',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$idHeader) {
            if (function_exists('registrar_bitacora')) {
                registrar_bitacora(
                    'Error de Creación de Tracking',
                    'Tracking',
                    'Fallo al insertar el encabezado del tracking. Motorista ID: ' . esc($motorista_id) . ', Paquetes: ' . count($paquetes),
                    $session->get('user_id')
                );
            }
            return redirect()->back()->with('error', 'No se pudo crear el tracking.');
        }

        $paquetesActualizados = [];

        foreach ($paquetes as $pid) {

            $paquete = $packageModel->find($pid);
            $paqueteEstatus = $paquete['estatus'] ?? 'pendiente';

            $estatus2 = $paquete['estatus2'] ?? null;
            $reenviosActuales = (int) ($paquete['reenvios'] ?? 0);

            $estadoInicial = 'asignado_para_entrega';

            if ($paquete) {
                if ($paquete['tipo_servicio'] == 3) {
                    if ($paqueteEstatus == 'pendiente' || $paqueteEstatus == 'recolecta_fallida') {
                        $estadoInicial = 'asignado_para_recolecta';
                    } else {
                        $estadoInicial = 'asignado_para_entrega'; 
                    }
                }

                if ($paqueteEstatus == 'recolecta_fallida') {
                    $estadoInicial = 'asignado_para_recolecta';
                }
            }

            $detailModel->insert([
                'tracking_header_id' => $idHeader,
                'package_id' => $pid,
                'status' => 'asignado', 
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $updateData = [
                'estatus' => $estadoInicial,
                'tracking_id' => $idHeader
            ];

            if ($estatus2 === 'reenvio') {
                $updateData['reenvios'] = $reenviosActuales + 1;
            }

            $packageModel->update($pid, $updateData);
            $paquetesActualizados[] = "ID " . esc($pid) . " → " . esc($estadoInicial);
        }

        $descripcionBitacora = 'Seguimiento **#' . esc($idHeader) . '** creado y asignado al motorista ID ' . esc($motorista_id) . ' (' . count($paquetes) . ' paquetes). Detalles de estado: ' . implode('; ', $paquetesActualizados);

        if (function_exists('registrar_bitacora')) {
            registrar_bitacora(
                'Creación de Tracking',
                'Tracking',
                $descripcionBitacora,
                $session->get('user_id')
            );
        }

        return redirect()->to(base_url('tracking/' . $idHeader))->with('success', 'Tracking creado correctamente.');
    }
    public function show($id)
    {
        $headerModel = new TrackingHeaderModel();
        $detailsModel = new TrackingDetailsModel();
        $tracking = $headerModel->getHeaderWithRelations($id);

        if (!$tracking) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Tracking ID $id no encontrado");
        }

        $detalles = $detailsModel->getDetailsWithPackages($id);

        return view('trackings/show', [
            'tracking' => $tracking,
            'detalles' => $detalles
        ]);
    }
    public function paquetesPorRuta($rutaId)
    {
        $fecha = $this->request->getVar('fecha'); 
        $paquetes = $this->paqueteModel->where('ruta_id', $rutaId)->findAll();
        return $this->response->setJSON($paquetes);
    }

    public function todos()
    {
        $paquetes = $this->paqueteModel->findAll();
        return $this->response->setJSON($paquetes);
    }

    public function rutasConPaquetes($fecha)
    {
        $fecha = filter_var($fecha, FILTER_SANITIZE_SPECIAL_CHARS);
        $db = \Config\Database::connect();
        $builder = $db->table('routes AS r');
        $builder->select('r.id, r.route_name AS text');
        $builder->distinct();
        $builder->join('packages AS p', 'r.id = p.ruta_id', 'inner');
        $builder->where('p.fecha_entrega_puntofijo', $fecha);

        $builder->groupStart()
            ->groupStart()
            ->where('p.tipo_servicio', 1)
            ->where('p.estado', 'pendiente')
            ->groupEnd() 
            ->orGroupStart()
            ->where('p.tipo_servicio', 3)
            ->where('p.estado', 'recolectado')
            ->where('p.id_puntofijo IS NOT NULL')
            ->groupEnd() 
            ->groupEnd(); 

        $search = $this->request->getVar('search');
        if (!empty($search)) {
            $builder->like('r.route_name', $search);
        }
        $rutas = $builder->get()->getResult();
        return $this->response->setJSON($rutas);
    }
}
