<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PackageModel;
use App\Models\SellerModel;
use App\Models\SettledPointModel;
use App\Models\AccountModel;

class PackageController extends BaseController
{
    protected $packageModel;
    protected $sellerModel;
    protected $settledPointModel;
    protected $packages;
    public function __construct()
    {
        $this->packageModel = new PackageModel();
        $this->settledPointModel = new SettledPointModel();
        $this->sellerModel = new SellerModel();
        $this->packages = new PackageModel();
    }

    public function index()
    {
        $chk = requerirPermiso('ver_paquetes');
        if ($chk !== true) return $chk;

        // Cantidad de resultados por página (GET o 10 por defecto)
        $perPage = $this->request->getGet('per_page') ?? 10;

        $filter_vendedor_id = $this->request->getGet('vendedor_id');
        $filter_status = $this->request->getGet('estatus');
        $filter_status2 = $this->request->getGet('estatus2');
        $filter_service = $this->request->getGet('tipo_servicio');
        $filter_date_from = $this->request->getGet('fecha_desde');
        $filter_date_to = $this->request->getGet('fecha_hasta');

        $builder = $this->packageModel
            ->select('packages.*, sellers.seller AS seller_name, settled_points.point_name, branches.branch_name AS branch_name')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->join('branches', 'branches.id = packages.branch', 'left')
            ->orderBy('packages.id', 'DESC');

        if (!empty($filter_vendedor_id)) {
            $builder->where('vendedor', $filter_vendedor_id);
        }
        if (!empty($filter_status)) {
            $builder->where('estatus', $filter_status)
                ->orWhere('estatus2', $filter_status);
        }
        if (!empty($filter_service)) {
            $builder->where('tipo_servicio', $filter_service);
        }
        if (!empty($filter_date_from)) {
            $builder->where('DATE(fecha_ingreso) >=', $filter_date_from);
        }
        if (!empty($filter_date_to)) {
            $builder->where('DATE(fecha_ingreso) <=', $filter_date_to);
        }

        $packages = $builder->paginate($perPage);
        $pager = $builder->pager;

        $sellers = $this->sellerModel->findAll();
        $puntos_fijos = $this->settledPointModel->findAll();

        $filter_vendedor_id = $this->request->getGet('vendedor_id');

        $seller_selected = null;
        if (!empty($filter_vendedor_id)) {
            $seller_selected = $this->sellerModel
                ->select('id, seller')
                ->find($filter_vendedor_id);
        }


        return view('packages/index', [
            'packages' => $packages,
            'pager' => $pager,
            'sellers' => $sellers,

            'filter_vendedor_id' => $filter_vendedor_id,
            'filter_status' => $filter_status,
            'filter_status2' => $filter_status2,
            'filter_service' => $filter_service,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'perPage' => $perPage,
            'puntos_fijos' => $puntos_fijos,
            'filter_seller_id' => $filter_vendedor_id,
            'seller_selected'  => $seller_selected
        ]);
    }

    public function show($id = null)
    {
        // Traemos el paquete con joins a usuario, punto fijo y vendedor
        $package = $this->packageModel
            ->select(
                'packages.*,
         users.user_name as creador_nombre,
         settled_points.point_name as point_name,
         sellers.seller as seller_name,
         accounts.name as pago_cuenta_nombre'
            )
            ->join('users', 'users.id = packages.user_id', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('accounts', 'accounts.id = packages.pago_cuenta', 'left') // 👈 AQUI
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
        $chk = requerirPermiso('crear_paquetes');
        if ($chk !== true) return $chk;

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
        helper('transaction');

        $session = session();
        $userId = $session->get('user_id'); // 🛡️ OBTENER DE LA SESIÓN
        $db = \Config\Database::connect();
        $foto = $this->request->getFile('foto');
        $fotoName = null;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $fotoName = $foto->getRandomName();
            $foto->move('upload/paquetes', $fotoName);
        }
        // Determinar el estatus inicial
        $estatusInicial = 'pendiente'; // Estatus por defecto

        $tipoServicio = $this->request->getPost('tipo_servicio');
        // ✨ LÓGICA CASILLERO (#4)
        if ($tipoServicio == 4) {
            $estatusInicial = 'en_casillero'; // Si es Casillero, el estatus inicial es 'casillero'
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
            'estatus' => $estatusInicial, // o el valor que corresponda
            'branch' => $this->request->getPost('branch_id'), // o el valor que corresponda
            'user_id' => $userId, // Usamos el ID de la sesión
        ];

        $this->packageModel->save($dataToSave);
        $newPackageId = $this->packageModel->insertID();

        // 🟨 REGISTRO DE TRANSACCIÓN
        $pagoParcial = $this->request->getPost('pago_parcial'); // toggle
        $fleteTotal = floatval($this->request->getPost('flete_total'));
        $fletePagado = floatval($this->request->getPost('flete_pagado'));
        $accountId = 1; // <-- AJUSTA si manejas diferentes cuentas

        // 🚫 NO REGISTRAR TRANSACCIÓN SI tipo_servicio == 3 (recolecta)
        if ($tipoServicio != 3) {

            if ($pagoParcial) {
                // 🟦 PAGO PARCIAL
                if ($fletePagado > 0) {
                    $db->table('accounts')
                        ->where('id', $accountId)
                        ->set('balance', 'balance + ' . $fleteTotal, false)
                        ->update();
                    registrarEntrada(
                        $accountId,
                        $fletePagado,
                        'Pago parcial de envío',
                        'Paquete ID ' . $newPackageId,
                        $newPackageId
                    );
                }
            } else {
                // 🟩 PAGO COMPLETO
                if ($fleteTotal > 0) {
                    $db->table('accounts')
                        ->where('id', $accountId)
                        ->set('balance', 'balance + ' . $fleteTotal, false)
                        ->update();
                    registrarEntrada(
                        $accountId,
                        $fleteTotal,
                        'Pago completo de envío',
                        'Paquete ID ' . $newPackageId,
                        $newPackageId
                    );
                }
            }
        } else {
            // 👇 OPCIONAL: puedes dejar una bitácora para control interno
            registrar_bitacora(
                'Servicio de recolecta',
                'Paquetería',
                'Se registró paquete ID ' . $newPackageId . ' sin pago porque el motorista traerá el dinero.',
                $userId
            );
        }

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
        $db = \Config\Database::connect();

        $builder = $db->table('packages');
        $builder->select('packages.*, sellers.seller AS seller_name, branches.branch_name AS branch_name, settled_points.point_name AS point_name');
        $builder->join('sellers', 'sellers.id = packages.vendedor', 'left');
        $builder->join('branches', 'branches.id = packages.branch', 'left');
        $builder->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left');
        $builder->where('packages.id', $id);

        $data['package'] = $builder->get()->getRowArray();

        return view('packages/edit', $data);
    }

    public function update($id)
    {
        $session = session();
        $userId = $session->get('user_id');

        // 1. Obtener el paquete antes de la actualización
        $oldPackage = $this->packages->find($id);

        if (!$oldPackage) {
            return redirect()->back()->with('error', 'Paquete no encontrado para actualizar.');
        }

        $rules = [
            'vendedor' => 'permit_empty',
            'cliente' => 'permit_empty',
            'tipo_servicio' => 'permit_empty',
            'destino_personalizado' => 'permit_empty',
            'lugar_recolecta_paquete' => 'permit_empty',
            'id_puntofijo' => 'permit_empty|numeric',
            'fecha_ingreso' => 'permit_empty|valid_date',
            'fecha_entrega_personalizado' => 'permit_empty|valid_date',
            'fecha_entrega_puntofijo' => 'permit_empty|valid_date',
            'flete_total' => 'permit_empty|decimal',
            'toggle_pago_parcial' => 'permit_empty',
            'flete_pagado' => 'permit_empty|decimal',
            'flete_pendiente' => 'permit_empty|decimal',
            'nocobrar_pack_cancelado' => 'permit_empty',
            'monto' => 'permit_empty|decimal',
            'fragil' => 'permit_empty|in_list[0,1]',
            'fecha_pack_entregado' => 'permit_empty|valid_date',
            'comentarios' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Datos limpios
        $data = $this->request->getPost();

        // Logica de foto:
        $foto = $this->request->getFile('foto');

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {

            // Validar tipo
            if (!in_array($foto->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('errors', ['foto' => 'Formato de imagen no permitido']);
            }

            // Crear carpeta si no existe
            $ruta = 'upload/paquetes/';
            if (!is_dir($ruta)) {
                mkdir($ruta, 0755, true);
            }

            // Nombre único
            $nuevoNombre = 'package_' . $id . '_' . time() . '.' . $foto->getExtension();

            // Mover archivo
            $foto->move($ruta, $nuevoNombre);

            // Borrar foto anterior si existe
            if (!empty($oldPackage['foto']) && file_exists($oldPackage['foto'])) {
                unlink($oldPackage['foto']);
            }

            // Guardar nueva ruta
            $data['foto'] = $nuevoNombre;
        } else {
            // No se subió foto → conservar la actual
            unset($data['foto']);
        }

        if (isset($data['tipo_servicio']) && $data['tipo_servicio'] == 4) {
            $data['estatus'] = 'en_casillero';
        }
        // 2. Ejecutar la actualización
        $this->packages->update($id, $data);

        // Generamos un detalle básico del cambio para la bitácora
        $log_details = '';
        $changes = [];

        // Comparamos campos importantes (puedes agregar o quitar campos aquí)
        $fields_to_check = ['vendedor', 'cliente', 'estatus', 'flete_total', 'flete_pagado', 'flete_pendiente', 'destino_personalizado', 'id_puntofijo', 'fecha_entrega_personalizado', 'fecha_entrega_puntofijo', 'monto'];

        foreach ($fields_to_check as $field) {
            // Aseguramos que la clave existe y los valores son diferentes
            if (isset($data[$field]) && $data[$field] != $oldPackage[$field]) {
                $changes[] = "$field: " . esc($oldPackage[$field]) . " -> " . esc($data[$field]);
            }
        }

        if (!empty($changes)) {
            $log_details = 'Campos modificados: ' . implode('; ', $changes);
        } else {
            $log_details = 'Paquete actualizado, sin cambios significativos detectados en campos clave.';
        }

        registrar_bitacora(
            'Actualización de paquete',
            'Paquetería',
            'Paquete ID ' . esc($id) . ' actualizado. Cliente: ' . esc($oldPackage['cliente']) . '. ' . $log_details,
            $userId
        );

        return redirect()->to(base_url('packages'))->with('success', 'Paquete actualizado correctamente.');
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
                'branch' => null,

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
                'branch' => null,
                // limpiar campos que no aplican
                'id_puntofijo' => null,
                'fecha_entrega_puntofijo' => null,
            ];

            $log_message = 'Destino actualizado a PERSONALIZADO';
            $log_details = 'Dirección: ' . esc($destinoPersonalizado) . ', Fecha de entrega: ' . esc($fechaEntregaPersonalizado);
        } elseif ($tipo === 'casillero') {
            $branchId = $this->request->getPost('branch');
            $data = [
                'destino_personalizado' => 'Casillero',
                'estatus' => 'en_casillero',
                'branch' => $branchId,

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

    public function setReenvio()
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
                'estatus' => 'pendiente',
                'estatus2' => 'reenvio',
                'branch' => null,

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
                'estatus' => 'pendiente',
                'estatus2' => 'reenvio',
                'branch' => null,
                // limpiar campos que no aplican
                'id_puntofijo' => null,
                'fecha_entrega_puntofijo' => null,
            ];

            $log_message = 'Destino actualizado a PERSONALIZADO';
            $log_details = 'Dirección: ' . esc($destinoPersonalizado) . ', Fecha de entrega: ' . esc($fechaEntregaPersonalizado);
        } elseif ($tipo === 'casillero') {
            $branchId = $this->request->getPost('branch');
            $data = [
                'destino_personalizado' => 'Casillero',
                'estatus' => 'en_casillero',
                'branch' => $branchId,

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

    public function getPackageData($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Petición no válida']);
        }

        $package = $this->packageModel->getFullPackage($id);

        if (!$package) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Paquete no encontrado']);
        }

        return $this->response->setJSON($package);
    }
    public function getDestinoInfo($id)
    {
        // Cargamos el paquete completo
        $package = $this->packageModel->find($id);

        if (!$package) {
            return $this->response->setJSON(['error' => 'Paquete no encontrado']);
        }

        // Si el paquete tiene destino personalizado
        if (!empty($package['destino_personalizado'])) {
            return $this->response->setJSON([
                'tipo' => 'personalizado',
                'direccion' => $package['destino_personalizado'],
                'fecha' => $package['fecha_entrega_personalizado']
            ]);
        }

        // Si el paquete tiene punto fijo
        if (!empty($package['id_puntofijo'])) {
            $punto = $this->settledPointModel->find($package['id_puntofijo']);

            return $this->response->setJSON([
                'tipo' => 'punto_fijo',
                'punto' => $punto,
                'fecha' => $package['fecha_entrega_puntofijo']
            ]);
        }

        return $this->response->setJSON([
            'tipo' => 'none',
            'mensaje' => 'El paquete no tiene destino configurado'
        ]);
    }
    public function devolver($id)
    {
        helper(['form']);
        $session = session();

        // ID del usuario que realiza la acción
        $userId = $session->get('user_id');

        // Datos para bitácora
        $log_message = 'Devolución de paquete';
        $log_details = 'El paquete fue marcado como devuelto por el usuario ' . $userId;

        // Actualizar estatus
        $this->packages->update($id, [
            'estatus' => 'no_retirado',
            'estatus2' => 'devuelto'
        ]);

        // Registrar bitácora
        registrar_bitacora(
            'Devolución de paquete ID ' . esc($id),
            'Paquetería',
            $log_message . ' para el paquete ' . esc($id) . '. Detalles: ' . $log_details,
            $userId
        );

        return $this->response->setJSON(['status' => 'ok']);
    }
    public function entregar($id)
    {
        helper(['form', 'transaction']);
        $session = session();
        $userId = $session->get('user_id');

        // 🔹 Leer JSON enviado desde SweetAlert
        $data = $this->request->getJSON(true);

        if (
            !$data ||
            empty($data['cuenta_id']) ||
            !isset($data['valor']) ||
            $data['valor'] <= 0
        ) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Datos incompletos para registrar la entrega'
            ]);
        }

        $cuentaId = (int)$data['cuenta_id'];
        $valor    = (float)$data['valor'];

        $db = db_connect();
        $db->transStart();

        try {

            // 1️⃣ Actualizar estatus del paquete
            $this->packages->update($id, [
                'estatus' => 'entregado',
            ]);

            // 2️⃣ Registrar entrada contable (MISMO MÉTODO QUE store())
            registrarEntrada(
                $cuentaId,
                $valor,
                'Pago por entrega de paquete',
                'Paquete ID ' . $id,
                $id
            );

            // 3️⃣ Bitácora
            registrar_bitacora(
                'Entrega de paquete ID ' . esc($id),
                'Paquetería',
                'Entrega registrada con pago de $' . number_format($valor, 2) .
                    ' en cuenta ID ' . $cuentaId .
                    ' por el usuario ' . $userId,
                $userId
            );

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en transacción');
            }

            return $this->response->setJSON(['status' => 'ok']);
        } catch (\Throwable $e) {

            $db->transRollback();

            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'No se pudo completar la entrega'
            ]);
        }
    }

    public function showReturnPackages()
    {
        $chk = requerirPermiso('devolver_paquetes');
        if ($chk !== true) return $chk;

        // Cantidad de resultados por página (GET o 10 por defecto)
        $perPage = $this->request->getGet('per_page') ?? 10;

        $filter_vendedor_id = $this->request->getGet('vendedor_id');
        $filter_status = $this->request->getGet('estatus');
        $filter_status2 = $this->request->getGet('estatus2');
        $filter_service = $this->request->getGet('tipo_servicio');
        $filter_date_from = $this->request->getGet('fecha_desde');
        $filter_date_to = $this->request->getGet('fecha_hasta');

        $allowedStatus = [
            'pendiente',
            'recolectado',
            'en_casillero',
            'no_retirado',
            'devuelto',
            'reenvio'
        ];

        $builder = $this->packageModel
            ->select('packages.*, sellers.seller AS seller_name, settled_points.point_name, branches.branch_name AS branch_name')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->join('branches', 'branches.id = packages.branch', 'left')
            ->groupStart()
            ->whereIn('packages.estatus', $allowedStatus)
            ->orWhereIn('packages.estatus2', $allowedStatus)
            ->groupEnd()
            ->orderBy('packages.id', 'DESC');


        if (!empty($filter_vendedor_id)) {
            $builder->where('vendedor', $filter_vendedor_id);
        }

        if (!empty($filter_status) && in_array($filter_status, $allowedStatus)) {
            $builder->groupStart()
                ->where('estatus', $filter_status)
                ->orWhere('estatus2', $filter_status)
                ->groupEnd();
        }

        if (!empty($filter_service)) {
            $builder->where('tipo_servicio', $filter_service);
        }
        if (!empty($filter_date_from)) {
            $builder->where('DATE(fecha_ingreso) >=', $filter_date_from);
        }
        if (!empty($filter_date_to)) {
            $builder->where('DATE(fecha_ingreso) <=', $filter_date_to);
        }

        $packages = $builder->paginate($perPage);
        $pager = $builder->pager;

        $sellers = $this->sellerModel->findAll();
        $puntos_fijos = $this->settledPointModel->findAll();

        $filter_vendedor_id = $this->request->getGet('vendedor_id');

        $seller_selected = null;
        if (!empty($filter_vendedor_id)) {
            $seller_selected = $this->sellerModel
                ->select('id, seller')
                ->find($filter_vendedor_id);
        }

        return view('packages/indexRemu', [
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
            'filter_seller_id' => $filter_vendedor_id,
            'seller_selected'  => $seller_selected
        ]);
    }
}
