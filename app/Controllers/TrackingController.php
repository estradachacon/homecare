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
        $chk = requerirPermiso('crear_tracking');
        if ($chk !== true) return $chk;

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

    // NOTA: Revisé esta función. Si buscas paquetes recolectados para ser reasignados a
    // una ruta para entrega, su estatus es 'recolectado' y deben ser incluidos.
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
            ->groupStart() // Agrupación para los estatus
            ->where('packages.estatus', 'pendiente')
            ->orWhere('packages.estatus', 'recolectado') // Paquetes recolectados listos para ser reasignados a entrega
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
            ->groupStart() // Agrupación para los estatus
            ->where('packages.estatus', 'pendiente')
            ->orWhere('packages.estatus', 'recolecta_fallida')
            ->orWhere('packages.estatus', 'recolectado') // Incluimos recolectados, si no tienen ruta asignada.
            ->groupEnd()
            ->findAll();

        return $this->response->setJSON($paquetes);
    }

    public function store()
    {
        // Cargar helpers (asumiendo que 'registrar_bitacora' está en 'bitacora')
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

        // ruta_id puede venir vacío
        $ruta_id = $this->request->getPost('ruta_id') ?: null;

        // 1. Guardar header
        $idHeader = $headerModel->insert([
            'user_id' => $motorista_id,
            'route_id' => $ruta_id,
            'date' => $fecha_seguimiento ?: date('Y-m-d'),
            'status' => 'asignado',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$idHeader) {
            // Registrar error en la bitácora
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

        // 2. Guardar detalles y actualizar paquetes
        $paquetesActualizados = []; // Para la bitácora

        foreach ($paquetes as $pid) {

            // Obtener el paquete para ver su estatus y tipo de servicio
            $paquete = $packageModel->find($pid);
            $paqueteEstatus = $paquete['estatus'] ?? 'pendiente';

            $estatus2 = $paquete['estatus2'] ?? null;
            $reenviosActuales = (int) ($paquete['reenvios'] ?? 0);

            $estadoInicial = 'asignado_para_entrega'; // Estado por defecto: entrega

            if ($paquete) {
                // Revisamos si necesita recolección
                if ($paquete['tipo_servicio'] == 3) {
                    if ($paqueteEstatus == 'pendiente' || $paqueteEstatus == 'recolecta_fallida') {
                        // Es Recolecta (tipo 3) y AÚN no ha sido recolectado exitosamente
                        $estadoInicial = 'asignado_para_recolecta';
                    } else {
                        // Es Recolecta (tipo 3) pero YA está en estatus 'recolectado'.
                        // Por lo tanto, el siguiente paso es la ENTREGA.
                        $estadoInicial = 'asignado_para_entrega'; // Ya estaba asignado arriba, pero lo ponemos explícito
                    }
                }

                // Si el estatus anterior fue 'recolecta_fallida' y no es tipo 3, puede ir directo a entrega si tiene sentido en tu flujo.
                // Basado en tu código anterior, si el estatus es 'recolecta_fallida', se intenta de nuevo:
                if ($paqueteEstatus == 'recolecta_fallida') {
                    $estadoInicial = 'asignado_para_recolecta';
                }
            }


            // Insertar detalle del tracking
            $detailModel->insert([
                'tracking_header_id' => $idHeader,
                'package_id' => $pid,
                'status' => 'asignado', // status del detalle
                'created_at' => date('Y-m-d H:i:s')
            ]);
            // 👉 Lógica mínima de reenvío (SIN romper nada)
            $updateData = [
                'estatus' => $estadoInicial,
                'tracking_id' => $idHeader
            ];

            if ($estatus2 === 'reenvio') {
                $updateData['reenvios'] = $reenviosActuales + 1;
            }

            // Actualizar paquete: estado inicial según tipo de servicio y estatus
            $packageModel->update($pid, $updateData);

            // Recolectar info para la bitácora
            $paquetesActualizados[] = "ID " . esc($pid) . " → " . esc($estadoInicial);
        }

        // 3. Registrar bitácora
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
        // ... (Código de show se mantiene sin cambios) ...
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
    public function paquetesPorRuta($rutaId)
    {
        // ... (Código de paquetesPorRuta se mantiene sin cambios) ...
        $fecha = $this->request->getVar('fecha'); // opcional, si quieres filtrar aquí
        $paquetes = $this->paqueteModel->where('ruta_id', $rutaId)->findAll();
        return $this->response->setJSON($paquetes);
    }

    public function todos()
    {
        // ... (Código de todos se mantiene sin cambios) ...
        $paquetes = $this->paqueteModel->findAll();
        return $this->response->setJSON($paquetes);
    }

    public function rutasConPaquetes($fecha)
    {
        // ... (Código de rutasConPaquetes se mantiene sin cambios) ...
        // 1. Corrección: Reemplazar FILTER_SANITIZE_STRING (obsoleto)
        $fecha = filter_var($fecha, FILTER_SANITIZE_SPECIAL_CHARS);

        $db = \Config\Database::connect();
        $builder = $db->table('routes AS r');

        $builder->select('r.id, r.route_name AS text');
        $builder->distinct();

        $builder->join('packages AS p', 'r.id = p.ruta_id', 'inner');

        $builder->where('p.fecha_entrega_puntofijo', $fecha);

        // 2. Corrección: Usar groupEnd() en lugar de endGroup()
        $builder->groupStart()
            // Condición A: Tipo Servicio 1 + estado pendiente
            ->groupStart()
            ->where('p.tipo_servicio', 1)
            ->where('p.estado', 'pendiente')
            ->groupEnd() // ⬅️ Corregido: Usar groupEnd()
            // Condición B: Tipo Servicio 3 + estado recolectado + id_puntofijo no nulo
            ->orGroupStart()
            ->where('p.tipo_servicio', 3)
            ->where('p.estado', 'recolectado')
            ->where('p.id_puntofijo IS NOT NULL')
            ->groupEnd() // ⬅️ Corregido: Usar groupEnd()
            ->groupEnd(); // ⬅️ Corregido: Usar groupEnd()

        $search = $this->request->getVar('search');
        if (!empty($search)) {
            $builder->like('r.route_name', $search);
        }

        $rutas = $builder->get()->getResult();

        return $this->response->setJSON($rutas);
    }
}
