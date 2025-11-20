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
        $perPage = 10;

        $filter_vendedor_id = $this->request->getGet('vendedor_id');

        $builder = $this->packageModel
            ->select('packages.*, sellers.seller AS seller_name, settled_points.point_name')
            ->join('sellers', 'sellers.id = packages.vendedor', 'left')
            ->join('settled_points', 'settled_points.id = packages.id_puntofijo', 'left')
            ->orderBy('packages.id', 'DESC');

        if (!empty($filter_vendedor_id)) {
            $builder->where('vendedor', $filter_vendedor_id);
        }

        $packages = $builder->paginate($perPage);
        $pager = $this->packageModel->pager;

        // Traemos todos los vendedores para el select
        $sellers = $this->sellerModel->findAll();

        return view('packages/index', [
            'packages' => $packages,
            'pager' => $pager,
            'sellers' => $sellers,
            'filter_vendedor_id' => $filter_vendedor_id
        ]);
    }




    public function show($id = null)
    {
        $data['package'] = $this->packageModel->find($id);
        if (!$data['package']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Paquete no encontrado");
        }
        return view('packages/show', $data);
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

        $foto = $this->request->getFile('foto');
        $fotoName = null;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $fotoName = $foto->getRandomName();
            $foto->move('upload/paquetes', $fotoName);
        }

        $this->packageModel->save([
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
            'user_id' => $this->request->getPost('user_id'),
        ]);

        registrar_bitacora(
            'Registro de paquete',
            'Paquetería',
            'Nuevo paquete registrado con ID ' . esc($this->packageModel->insertID()),
            $session->get('user_id')
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
}