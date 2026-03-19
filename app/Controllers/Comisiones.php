<?php

namespace App\Controllers;

use App\Models\ComisionConfigModel;
use App\Models\ComisionVendedorModel;
use App\Models\ComisionReglaModel;
use App\Models\ComisionMargenModel;
use App\Models\SellerModel;

class Comisiones extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_comisiones');
        if ($chk !== true) return $chk;

        $model = new \App\Models\ComisionModel();

        // FILTROS
        $seller = $this->request->getGet('seller_id');
        $estado = $this->request->getGet('estado');
        $inicio = $this->request->getGet('fecha_inicio');
        $fin = $this->request->getGet('fecha_fin');

        // QUERY BASE
        $model->select('comisiones.*, sellers.seller as vendedor_nombre')
            ->join('sellers', 'sellers.id = comisiones.vendedor_id', 'left');

        if ($seller) {
            $model->where('comisiones.vendedor_id', $seller);
        }

        if ($estado) {
            $model->where('comisiones.estado', $estado);
        }

        if ($inicio) {
            $model->where('DATE(comisiones.fecha_inicio) >=', $inicio);
        }

        if ($fin) {
            $model->where('DATE(comisiones.fecha_fin) <=', $fin);
        }

        // PAGINACIÓN (CLAVE)
        $comisiones = $model->orderBy('comisiones.id', 'DESC')
            ->paginate(10);

        $pager = $model->pager;

        // 🔹 AJAX
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'tbody' => view('comisiones/partials/_tabla', [
                    'comisiones' => $comisiones
                ]),
                'pager' => $pager->links('default', 'bootstrap_full')
            ]);
        }

        return view('comisiones/index', [
            'comisiones' => $comisiones,
            'pager'      => $pager
        ]);
    }

    private function getNombreVendedor($id)
    {
        $sellerModel = new SellerModel();

        $seller = $sellerModel->find($id);

        return $seller ? $seller->seller : "ID {$id}";
    }

    public function config()
    {
        $configModel   = new ComisionConfigModel();
        $vendedorModel = new ComisionVendedorModel();
        $reglaModel    = new ComisionReglaModel();
        $margenModel   = new ComisionMargenModel();
        $sellerModel   = new SellerModel();

        $chk = requerirPermiso('configurar_comisiones');
        if ($chk !== true) return $chk;

        // 🔹 CONFIG GENERAL
        $config = $configModel->first();

        if (!$config) {
            $configModel->insert([
                'porcentaje_default' => 0
            ]);
            $config = $configModel->first();
        }

        // 🔹 COMISIONES GUARDADAS
        $comisiones = $vendedorModel->findAll();

        $vendedores = [];

        if (!empty($comisiones)) {

            // obtener ids
            $ids = array_column($comisiones, 'vendedor_id');

            // traer solo esos sellers
            $sellers = $sellerModel->whereIn('id', $ids)->findAll();

            // mapear sellers
            $mapSeller = [];

            foreach ($sellers as $s) {
                $mapSeller[$s->id] = $s;
            }

            // armar array final
            foreach ($comisiones as $c) {
                if (isset($mapSeller[$c->vendedor_id])) {
                    $vendedores[] = (object)[
                        'vendedor_id' => $c->vendedor_id,
                        'nombre'      => $mapSeller[$c->vendedor_id]->seller,
                        'porcentaje'  => $c->porcentaje
                    ];
                }
            }
        }

        // 🔹 REGLAS
        // 🔹 REGLAS
        $reglas = $reglaModel->findAll();

        $productoModel = new \App\Models\ProductoModel();
        $tipoVentaModel = new \App\Models\TipoVentaModel();
        $sellerModel = new SellerModel();

        foreach ($reglas as $r) {

            // 🔹 PRODUCTO (valor = producto_id)
            $producto = $productoModel->find($r->valor);

            $r->producto_nombre = $producto
                ? $producto->descripcion . ' (' . $producto->codigo . ')'
                : 'Producto eliminado';

            // 🔹 TIPO DE VENTA
            if (!empty($r->tipo_venta_id)) {
                $tipo = $tipoVentaModel->find($r->tipo_venta_id);
                $r->tipo_venta_nombre = $tipo
                    ? $tipo->nombre_tipo_venta
                    : 'Tipo eliminado';
            } else {
                $r->tipo_venta_nombre = 'Todos';
            }

            // 🔹 VENDEDOR
            if (!empty($r->vendedor_id)) {
                $seller = $sellerModel->find($r->vendedor_id);
                $r->vendedor_nombre = $seller
                    ? $seller->seller
                    : 'Vendedor eliminado';
            } else {
                $r->vendedor_nombre = 'Todos';
            }
        }

        // MÁRGENES
        $margenes = $margenModel->orderBy('margen_min', 'ASC')->findAll();

        return view('comisiones/mantenimientos/config', [
            'config'     => $config,
            'vendedores' => $vendedores,
            'reglas'     => $reglas,
            'margenes'   => $margenes
        ]);
    }

    public function guardarGeneral()
    {
        $session = session();
        $model = new ComisionConfigModel();

        $porcentaje = $this->request->getPost('porcentaje_default');

        $config = $model->first();

        if ($config) {
            $model->update($config->id, [
                'porcentaje_default' => $porcentaje
            ]);
        } else {
            $model->insert([
                'porcentaje_default' => $porcentaje
            ]);
        }

        registrar_bitacora(
            'Actualización de porcentaje general',
            'Comisiones',
            'Se estableció el porcentaje general en ' . $porcentaje . '%',
            $session->get('user_id')
        );

        return redirect()->back()->with('success', 'General actualizado');
    }

    public function guardarVendedores()
    {
        $session = session();
        $model = new ComisionVendedorModel();

        $ids = $this->request->getPost('vendedor_ids') ?? [];
        $porcentajes = $this->request->getPost('vendedor_porcentaje') ?? [];

        // 🔹 obtener estado actual
        $actuales = $model->findAll();

        $mapActual = [];
        foreach ($actuales as $a) {
            $mapActual[$a->vendedor_id] = $a->porcentaje;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $cambios = [];

        // 🔹 INSERT / UPDATE
        foreach ($ids as $index => $id) {

            if (!$id) continue;

            $nuevoPorcentaje = $porcentajes[$index] ?? 0;

            if (!isset($mapActual[$id])) {

                // 🟢 NUEVO
                $model->insert([
                    'vendedor_id' => $id,
                    'porcentaje'  => $nuevoPorcentaje
                ]);

                $nombre = $this->getNombreVendedor($id);
                $cambios[] = "{$nombre} agregado con {$nuevoPorcentaje}%";
            } else {

                $anterior = $mapActual[$id];

                if ((float)$anterior !== (float)$nuevoPorcentaje) {

                    // 🟡 ACTUALIZACIÓN
                    $model->where('vendedor_id', $id)
                        ->set(['porcentaje' => $nuevoPorcentaje])
                        ->update();

                    $nombre = $this->getNombreVendedor($id);
                    $cambios[] = "{$nombre}: {$anterior}% → {$nuevoPorcentaje}%";
                }
            }
        }

        // 🔴 ELIMINADOS
        $idsNuevos = array_map('intval', $ids);

        foreach ($mapActual as $id => $porcentaje) {

            if (!in_array((int)$id, $idsNuevos)) {

                $db->table('comision_vendedores')
                    ->where('vendedor_id', $id)
                    ->delete();

                $nombre = $this->getNombreVendedor($id);
                $cambios[] = "{$nombre} eliminado (tenía {$porcentaje}%)";
            }
        }

        $db->transComplete();

        // 🔹 BITÁCORA SOLO SI HUBO CAMBIOS
        if (!empty($cambios)) {
            registrar_bitacora(
                'Actualización de comisiones por vendedor',
                'Comisiones',
                implode(' | ', $cambios),
                $session->get('user_id')
            );
        }

        return redirect()->back()->with('success', 'Vendedores actualizados');
    }

    public function guardarReglas()
    {
        $session = session();
        $model = new ComisionReglaModel();

        $productos   = $this->request->getPost('producto_id') ?? [];
        $tipos       = $this->request->getPost('tipo_venta_id') ?? [];
        $vendedores  = $this->request->getPost('vendedor_id') ?? [];
        $porcentajes = $this->request->getPost('porcentaje') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        // 🔥 limpiar todo (como ya hacías)
        $model->truncate();

        $count = 0;

        foreach ($productos as $i => $producto_id) {

            if (!$producto_id) continue;

            $tipoVenta = $tipos[$i] ?? null;
            $vendedor  = $vendedores[$i] ?? null;
            $porcentaje = $porcentajes[$i] ?? 0;

            $model->insert([
                'valor'         => $producto_id,
                'tipo_venta_id' => $tipoVenta ?: null,
                'vendedor_id'   => $vendedor ?: null,
                'porcentaje'    => $porcentaje
            ]);

            $count++;
        }

        $db->transComplete();

        registrar_bitacora(
            'Actualización de reglas de comisión',
            'Comisiones',
            'Se configuraron ' . $count . ' reglas de comisión',
            $session->get('user_id')
        );

        return redirect()->back()->with('success', 'Reglas actualizadas');
    }

    public function guardarMargen()
    {
        $session = session();
        $model = new ComisionMargenModel();

        $minimos      = $this->request->getPost('margen_min') ?? [];
        $maximos      = $this->request->getPost('margen_max') ?? [];
        $porcentajes  = $this->request->getPost('margen_porcentaje') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        $model->truncate();

        $count = 0;

        foreach ($minimos as $i => $min) {

            $max        = $maximos[$i] ?? null;
            $porcentaje = $porcentajes[$i] ?? 0;

            if ($min === null || $porcentaje === null) continue;

            $model->insert([
                'margen_min' => $min,
                'margen_max' => $max ?: null,
                'porcentaje' => $porcentaje
            ]);

            $count++;
        }

        $db->transComplete();

        registrar_bitacora(
            'Actualización de comisiones por margen',
            'Comisiones',
            'Se configuraron ' . $count . ' rangos de margen',
            $session->get('user_id')
        );

        return redirect()->back()->with('success', 'Margen actualizado');
    }

    //Para el borrado del PercXVendedor
    public function deleteVendedor()
    {
        $model = new ComisionVendedorModel();
        $session = session();

        $id = $this->request->getPost('vendedor_id');

        $actual = $model->where('vendedor_id', $id)->first();

        if (!$actual) {
            return $this->response->setJSON(['status' => 'error']);
        }

        $model->where('vendedor_id', $id)->delete();

        $nombre = $this->getNombreVendedor($id);

        registrar_bitacora(
            'Eliminación de vendedor',
            'Comisiones',
            "{$nombre} eliminado (tenía {$actual->porcentaje}%)",
            $session->get('user_id')
        );

        return $this->response->setJSON(['status' => 'ok']);
    }

    public function addVendedor()
    {
        $model = new ComisionVendedorModel();
        $session = session();

        $id = $this->request->getPost('vendedor_id');
        $porcentaje = $this->request->getPost('porcentaje');

        if (!$id) {
            return $this->response->setJSON(['status' => 'error']);
        }

        // 🔥 evitar duplicados
        $existe = $model->where('vendedor_id', $id)->first();

        if ($existe) {
            return $this->response->setJSON(['status' => 'exists']);
        }

        $model->insert([
            'vendedor_id' => $id,
            'porcentaje'  => $porcentaje
        ]);

        $nombre = $this->getNombreVendedor($id);

        registrar_bitacora(
            'Nuevo vendedor agregado',
            'Comisiones',
            "{$nombre} con {$porcentaje}%",
            $session->get('user_id')
        );

        return $this->response->setJSON(['status' => 'ok']);
    }
    public function updateVendedor()
    {
        $model = new ComisionVendedorModel();
        $session = session();

        $id = $this->request->getPost('vendedor_id');
        $porcentaje = $this->request->getPost('porcentaje');

        $actual = $model->where('vendedor_id', $id)->first();

        if (!$actual) {
            return $this->response->setJSON(['status' => 'error']);
        }

        // evitar update innecesario
        if ((float)$actual->porcentaje === (float)$porcentaje) {
            return $this->response->setJSON(['status' => 'nochange']);
        }

        $model->where('vendedor_id', $id)
            ->set(['porcentaje' => $porcentaje])
            ->update();

        $nombre = $this->getNombreVendedor($id);

        registrar_bitacora(
            'Actualización de comisión',
            'Comisiones',
            "{$nombre}: {$actual->porcentaje}% → {$porcentaje}%",
            $session->get('user_id')
        );

        return $this->response->setJSON(['status' => 'ok']);
    }
    public function generar()
    {
        $chk = requerirPermiso('generar_comisiones');
        if ($chk !== true) return $chk;

        return view('comisiones/new');
    }
    public function getDocumentos()
    {
        helper('dte');
        $tipos = dte_siglas();

        $seller = $this->request->getPost('seller_id');
        $inicio = $this->request->getPost('fecha_inicio');
        $fin = $this->request->getPost('fecha_fin');

        $db = \Config\Database::connect();

        $docs = $db->table('factura_detalles fd')
            ->select('
            fh.fecha_emision,
            fh.tipo_dte,
            fh.numero_control,
            fh.total_pagar,
            fh.vendedor_id,
            fh.id as factura_id,

            fd.codigo,
            fd.descripcion,
            fd.cantidad,
            fd.precio_unitario,
            fd.venta_gravada,
                        
            c.nombre as cliente,
            tv.nombre_tipo_venta as tipo_venta,
            p.id as producto_id,
            cr_producto.porcentaje as producto_porcentaje

        ')
            ->join('facturas_head fh', 'fh.id = fd.factura_id')
            ->join('clientes c', 'c.id = fh.receptor_id', 'left')
            ->join('tipo_venta tv', 'tv.id = fh.tipo_venta', 'left')
            ->join('productos p', 'p.codigo = fd.codigo', 'left')
            ->join('comisiones_reglas cr_producto', "cr_producto.valor = p.id   AND (cr_producto.tipo_venta_id = fh.tipo_venta OR cr_producto.tipo_venta_id IS NULL)
                                                                                AND (cr_producto.vendedor_id = fh.vendedor_id OR cr_producto.vendedor_id IS NULL)", 'left')
            ->join('comision_detalles cd', 'cd.factura_id = fh.id AND cd.producto_id = p.id', 'left')

            ->where('fh.vendedor_id', $seller)
            ->where('DATE(fh.fecha_emision) >=', $inicio)
            ->where('DATE(fh.fecha_emision) <=', $fin)
            ->where('fh.anulada', 0)
            ->where('cd.id IS NULL')
            ->orderBy('fh.fecha_emision', 'ASC')
            ->orderBy('fh.numero_control', 'ASC')
            ->get()
            ->getResult();

        $result = [];

        foreach ($docs as $d) {
            $precioUnitario = 0;

            if ((float)$d->cantidad > 0) {
                $precioUnitario = (float)$d->venta_gravada / (float)$d->cantidad;
            }

            $result[] = [
                'fecha_emision'   => $d->fecha_emision,
                'tipo'            => $tipos[$d->tipo_dte] ?? $d->tipo_dte,
                'numero_control'  => $d->numero_control,
                'cliente'         => $d->cliente,
                'codigo'          => $d->codigo,
                'descripcion'     => $d->descripcion,
                'cantidad'        => $d->cantidad,

                // 🔥 AQUÍ EL CAMBIO
                'precio_unitario' => round($precioUnitario, 6),

                'venta_gravada'   => $d->venta_gravada,
                'tipo_venta'      => $d->tipo_venta,
                'factura_id'      => $d->factura_id,
                'producto_id'     => $d->producto_id,

                'producto_porcentaje' => $d->producto_porcentaje !== null
                    ? (float)$d->producto_porcentaje
                    : null,
            ];
        }

        // NUEVO: obtener porcentaje general
        $configModel = new ComisionConfigModel();
        $config = $configModel->first();

        $porcentajeDefault = $config->porcentaje_default ?? 0;

        // NUEVO: porcentaje por vendedor
        $vendedorModel = new ComisionVendedorModel();
        $vendedor = $vendedorModel->getByVendedor($seller);

        $porcentajeVendedor = $vendedor ? $vendedor->porcentaje : null;
        $porcentajeDefault = $config->porcentaje_default ?? null;

        // CAMBIO: ya no devuelves solo $result
        return $this->response->setJSON([
            'documentos' => $result,
            'porcentaje_default' => $porcentajeDefault,
            'porcentaje_vendedor' => $porcentajeVendedor
        ]);
    }
    public function guardar()
    {
        try {

            $data = $this->request->getJSON(true);

            if (!$data) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se recibieron datos'
                ]);
            }

            $comision = $data['comision'] ?? null;
            $detalles = $data['detalles'] ?? [];

            if (!$comision || empty($detalles)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Datos incompletos'
                ]);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // MODELOS
            $comisionModel = new \App\Models\ComisionModel();
            $detalleModel  = new \App\Models\ComisionDetalleModel();

            // INSERT ENCABEZADO
            $comisionId = $comisionModel->insert([
                'vendedor_id'        => $comision['vendedor_id'],
                'fecha_inicio'       => $comision['fecha_inicio'],
                'fecha_fin'          => $comision['fecha_fin'],
                'total_ventas'       => $comision['total_ventas'],
                'total_comision'     => $comision['total_comision'],
                'porcentaje_promedio' => $comision['porcentaje_promedio'],
                'estado'             => 'generado'
            ]);

            if (!$comisionId) {
                throw new \Exception('No se pudo guardar la comisión');
            }

            // PREPARAR DETALLES
            $batch = [];

            foreach ($detalles as $d) {

                // 🔥 VALIDACIÓN CLAVE
                if (empty($d['factura_id']) || empty($d['producto_id'])) {
                    throw new \Exception('Detalle inválido: faltan IDs');
                }

                $batch[] = [
                    'comision_id'        => $comisionId,
                    'factura_id'         => (int)$d['factura_id'],
                    'producto_id'        => (int)$d['producto_id'],
                    'cantidad'           => $d['cantidad'],
                    'precio_sin_iva'     => $d['precio_sin_iva'],
                    'total_linea'        => $d['total_linea'],
                    'comision_aplicada'  => $d['comision_aplicada'],
                    'monto_comision'     => $d['monto_comision'],
                    'tipo_venta'         => $d['tipo_venta'],
                    'origen_comision'    => $d['origen_comision']
                ];
            }

            // INSERT MASIVO
            $detalleModel->insertBatch($batch);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            // RESPUESTA OK
            return $this->response->setJSON([
                'success' => true,
                'comision_id' => $comisionId
            ]);
        } catch (\Throwable $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function ver($id)
    {
        $comisionModel = new \App\Models\ComisionModel();
        $detalleModel  = new \App\Models\ComisionDetalleModel();

        $comision = $comisionModel->find($id);

        if (!$comision) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Comisión no encontrada");
        }

        $detalles = $detalleModel
            ->select('
            comision_detalles.*,
            fh.numero_control,
            fh.fecha_emision,
            fh.tipo_dte,
            p.descripcion as producto
        ')
            ->join('facturas_head fh', 'fh.id = comision_detalles.factura_id', 'left')
            ->join('productos p', 'p.id = comision_detalles.producto_id', 'left')
            ->where('comision_id', $id)
            ->findAll();

        return view('comisiones/ver', [
            'comision' => $comision,
            'detalles' => $detalles
        ]);
    }
}
