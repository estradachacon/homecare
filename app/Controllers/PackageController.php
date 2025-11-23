<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\SellerModel;
use App\Models\SettledPointModel;

class PackageController extends BaseController
{
    protected $packageModel;
    protected $sellerModel;
    protected $settledPointModel;

    public function __construct()
    {
        $this->packageModel = new PackageModel();
        $this->settledPointModel = new SettledPointModel();
        $this->sellerModel = new SellerModel();
    }

    public function index()
    {
        // Cantidad de resultados por página (GET o 10 por defecto)
        $perPage = $this->request->getGet('per_page') ?? 10;

        $filter_vendedor_id = $this->request->getGet('vendedor_id');
        $filter_status = $this->request->getGet('estatus');
        $filter_service = $this->request->getGet('tipo_servicio');
        $filter_date_from = $this->request->getGet('fecha_desde');
        $filter_date_to = $this->request->getGet('fecha_hasta');

        $builder = $this->packageModel
            ->select('packages.*, sellers.seller AS seller_name, settled_points.point_name')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->orderBy('packages.id', 'DESC');

        // FILTRO VENDEDOR
        if (!empty($filter_vendedor_id)) {
            $builder->where('vendedor', $filter_vendedor_id);
        }

        // FILTRO ESTATUS
        if (!empty($filter_status)) {
            $builder->where('estatus', $filter_status);
        }

        // FILTRO TIPO DE SERVICIO
        if (!empty($filter_service)) {
            $builder->where('tipo_servicio', $filter_service);
        }

        // FILTRO FECHA DESDE
        if (!empty($filter_date_from)) {
            $builder->where('DATE(fecha_ingreso) >=', $filter_date_from);
        }

        // FILTRO FECHA HASTA
        if (!empty($filter_date_to)) {
            $builder->where('DATE(fecha_ingreso) <=', $filter_date_to);
        }

        // ⬅️ PAGINACIÓN CORRECTA
        $packages = $builder->paginate($perPage);
        $pager = $builder->pager; // ⬅️ ESTE ES EL PAGER CORRECTO

        $sellers = $this->sellerModel->findAll();
        $puntos_fijos = $this->settledPointModel->findAll();

        return view('packages/index', [
            'packages' => $packages,
            'pager' => $pager,
            'sellers' => $sellers,

            'filter_vendedor_id' => $filter_vendedor_id,
            'filter_status' => $filter_status,
            'filter_service' => $filter_service,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'perPage' => $perPage,
            'puntos_fijos' => $puntos_fijos,
        ]);
    }

public function show($id = null)
{
    // Traemos el paquete con joins a usuario, punto fijo y vendedor
    $package = $this->packageModel
        ->select('packages.*, users.user_name as creador_nombre, settled_points.point_name as point_name, sellers.seller as seller_name')
        ->join('users', 'users.id = packages.user_id', 'left')
        ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
        ->join('sellers', 'sellers.id = packages.vendedor', 'left')
        ->where('packages.id', $id)
        ->first();

    if (!$package) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException("Paquete no encontrado");
    }

    // Normalizamos a array para que tu vista use siempre $package['campo']
    $package = (array) $package;

    // DEBUG: Verificar que el campo 'foto' existe (puedes eliminar esto después)
    if (!array_key_exists('foto', $package)) {
        log_message('error', "Campo 'foto' no encontrado en package ID: " . $id);
        $package['foto'] = null; // Asegurar que existe la clave
    }

    // Calculamos un campo destinos para mostrar en un solo lugar si quieres
    $destinos = [];
    switch ($package['tipo_servicio']) {
        case 1: // Punto fijo
            $destinos[] = $package['point_name'] ?? 'N/A';
            $package['fecha_entrega_mostrar'] = $package['fecha_entrega_puntofijo'] ?? 'Pendiente';
            break;
        case 2: // Personalizado
            $destinos[] = $package['destino_personalizado'] ?? 'N/A';
            $package['fecha_entrega_mostrar'] = $package['fecha_entrega_personalizado'] ?? 'Pendiente';
            break;
        case 3: // Recolección y entrega final
            $destinos[] = $package['lugar_recolecta_paquete'] ?? 'N/A';
            if (!empty($package['destino_entrega_final'])) {
                $destinos[] = $package['destino_entrega_final'];
            }
            $package['fecha_entrega_mostrar'] = $package['fecha_entrega_personalizado'] ?? 'Pendiente';
            break;
        case 4: // Casillero
            $destinos[] = $package['numero_casillero'] ?? 'N/A';
            $package['fecha_entrega_mostrar'] = 'Pendiente';
            break;
    }

    $package['destinos'] = implode(' → ', $destinos);

    return view('packages/show', [
        'package' => $package
    ]);
}

    public function new()
    {
        $settledPoint = $this->settledPointModel->findAll();
        $session = session();

        $data = [
            'settledPoints' => $settledPoint,
        ];
        return view('packages/new', $data);
    }

    public function store()
    {
        helper(['form']);

        $session = session();
        $userId = $session->get('user_id'); // 🛡️ OBTENER DE LA SESIÓN

        $foto = $this->request->getFile('foto');
        $fotoName = null;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $fotoName = $foto->getRandomName();
            $foto->move('upload/paquetes', $fotoName);
        }

        $dataToSave = [
            'vendedor' => $this->request->getPost('seller_id'),
            'cliente' => $this->request->getPost('cliente'),
            'tipo_servicio' => $this->request->getPost('tipo_servicio'),
            'destino_personalizado' => $this->request->getPost('destino'),
            'lugar_recolecta_paquete' => $this->request->getPost('retiro_paquete'),
            'id_puntofijo' => $this->request->getPost('id_puntofijo'),

            'fecha_ingreso' => $this->request->getPost('fecha_ingreso'),
            'fecha_entrega_personalizado' => $this->request->getPost('fecha_entrega'),
            'fecha_entrega_puntofijo' => $this->request->getPost('fecha_entrega_puntofijo'),

            'flete_total' => $this->request->getPost('flete_total'),
            'toggle_pago_parcial' => $this->request->getPost('pago_parcial'),
            'flete_pagado' => $this->request->getPost('flete_pagado'),
            'flete_pendiente' => $this->request->getPost('flete_pendiente'),

            'nocobrar_pack_cancelado' => $this->request->getPost('toggleCobro'),
            'monto' => $this->request->getPost('monto'),
            'foto' => $fotoName,
            'comentarios' => $this->request->getPost('comentarios'),
            'fragil' => $this->request->getPost('fragil'),
            'estatus' => 'pendiente', // o el valor que corresponda
            'user_id' => $userId, // Usamos el ID de la sesión
        ];

        $this->packageModel->save($dataToSave);
        $newPackageId = $this->packageModel->insertID();

        // 📜 BITÁCORA: Registro de creación
        registrar_bitacora(
            'Registro de paquete',
            'Paquetería',
            'Nuevo paquete registrado con ID ' . esc($newPackageId) . ' para el cliente ' . esc($dataToSave['cliente']),
            $userId
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Paquete creado correctamente.'
        ]);
    }

    public function subirImagen()
    {
        $file = $this->request->getFile('imagen_paquete');

        if (!$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $file->getErrorString()
            ]);
        }

        // Definir carpeta destino
        $newName = $file->getRandomName();

        $file->move(ROOTPATH . 'public/upload/paquetes', $newName);

        return $this->response->setJSON([
            'status' => 'success',
            'file' => $newName
        ]);
    }

    public function edit($id)
    {
        $package = $this->packageModel
            ->select('packages.*, sellers.seller AS seller_name, settled_points.point_name')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->where('packages.id', $id)
            ->first();

        return view('packages/edit', [
            'package' => $package
        ]);
    }


    public function update($id)
    {
        $session = session();
        $userId = $session->get('user_id');

        $package = $this->packageModel->find($id);
        if (!$package) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No encontrado']);
        }

        $data = $this->request->getPost();

        // Foto
        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid()) {
            $newName = $foto->getRandomName();
            $foto->move('upload/paquetes/', $newName);

            $data['foto'] = $newName;
        }

        $this->packageModel->update($id, $data);

        // 📜 BITÁCORA: Registro de actualización
        registrar_bitacora(
            'Actualización de Paquete',
            'Paquetería',
            'Datos del paquete ID ' . esc($id) . ' actualizados. Campos modificados: ' . implode(', ', array_keys($data)),
            $userId
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Paquete actualizado'
        ]);
    }

    public function setDestino()
    {
        $session = session();
        $userId = $session->get('user_id');

        $id = $this->request->getPost('id');
        $tipo = $this->request->getPost('tipo_destino');

        $package = $this->packageModel->find($id);
        if (!$package) {
            return redirect()->back()->with('error', 'Paquete no encontrado');
        }

        $data = [];
        $log_message = '';
        $log_details = '';

        if ($tipo === 'punto') {
            $puntoFijoId = $this->request->getPost('id_puntofijo');
            $fechaEntregaPuntoFijo = $this->request->getPost('fecha_entrega_puntofijo');

            $data = [
                'id_puntofijo' => $puntoFijoId,
                'fecha_entrega_puntofijo' => $fechaEntregaPuntoFijo,

                // limpiar campos que no aplican
                'destino_personalizado' => null,
                'fecha_entrega_personalizado' => null,
            ];

            $log_message = 'Destino actualizado a PUNTOS FIJOS (ID: ' . esc($puntoFijoId) . ')';
            $log_details = 'Fecha de entrega punto fijo: ' . esc($fechaEntregaPuntoFijo);

        } elseif ($tipo === 'personalizado') {
            $destinoPersonalizado = $this->request->getPost('destino_personalizado');
            $fechaEntregaPersonalizado = $this->request->getPost('fecha_entrega_personalizado');

            $data = [
                'destino_personalizado' => $destinoPersonalizado,
                'fecha_entrega_personalizado' => $fechaEntregaPersonalizado,

                // limpiar campos que no aplican
                'id_puntofijo' => null,
                'fecha_entrega_puntofijo' => null,
            ];

            $log_message = 'Destino actualizado a PERSONALIZADO';
            $log_details = 'Dirección: ' . esc($destinoPersonalizado) . ', Fecha de entrega: ' . esc($fechaEntregaPersonalizado);

        } elseif ($tipo === 'casillero') {

            $data = [
                'destino_personalizado' => 'Casillero',
                'estatus' => 'en_casillero',

                // limpiar campos que no aplican
                'id_puntofijo' => null,
                'fecha_entrega_puntofijo' => null,
                'fecha_entrega_personalizado' => null,
            ];

            $log_message = 'Destino actualizado a CASILLERO';
            $log_details = 'Estatus cambiado a: en_casillero';
        }

        // Si hay datos para actualizar, procedemos
        if (!empty($data)) {
            $this->packageModel->update($id, $data);

            registrar_bitacora(
                'Cambio de Destino Paquete ID ' . esc($id),
                'Paquetería',
                $log_message . ' para paquete ' . esc($id) . '. Detalles: ' . $log_details,
                $userId
            );

            return redirect()->back()->with('success', 'Destino actualizado correctamente');
        }

        return redirect()->back()->with('error', 'Tipo de destino no válido o faltante.');
    }

}