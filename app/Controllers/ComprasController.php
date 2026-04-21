<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProveedorModel;
use App\Models\CompraHeadModel;
use App\Models\CompraDetalleModel;
use App\Models\ProductoModel;
use App\Models\ProductoMovimientoModel;
use App\Models\PagosComprasDetallesModel;

class ComprasController extends BaseController
{

    public function index()
    {
        $chk = requerirPermiso('ver_compras');
        if ($chk !== true) return $chk;

        $model = new CompraHeadModel();

        $perPage = (int)($this->request->getGet('per_page') ?? 25);
        if (!in_array($perPage, [10, 15, 25, 50, 100, 99999])) $perPage = 25;

        $query = $model
            ->select('compras_head.*, proveedores.nombre AS proveedor_nombre')
            ->join('proveedores', 'proveedores.id = compras_head.proveedor_id', 'left');

        // FILTROS
        if ($proveedorId = $this->request->getGet('proveedor_id')) {
            $query->where('compras_head.proveedor_id', $proveedorId);
        }

        if ($tipoDte = $this->request->getGet('tipo_dte')) {
            $query->where('compras_head.tipo_dte', $tipoDte);
        }

        if ($fecha = $this->request->getGet('fecha')) {
            $query->where('DATE(compras_head.fecha_emision)', $fecha);
        }

        if ($numero = $this->request->getGet('numero_compra')) {
            $query->like('compras_head.numero_control', $numero, 'before'); // 'before' = %1036
        }

        if ($estado = $this->request->getGet('estado')) {
            switch ($estado) {
                case 'activa':
                    $query->where('compras_head.anulada', 0)
                        ->where('compras_head.saldo >', 0)
                        ->where('compras_head.saldo = compras_head.total_pagar');
                    break;
                case 'parcial':
                    $query->where('compras_head.anulada', 0)
                        ->where('compras_head.saldo >', 0)
                        ->where('compras_head.saldo < compras_head.total_pagar');
                    break;
                case 'pagada':
                    $query->where('compras_head.anulada', 0)
                        ->where('compras_head.saldo', 0);
                    break;
                case 'anulada':
                    $query->where('compras_head.anulada', 1);
                    break;
            }
        }

        $query->orderBy('compras_head.fecha_emision', 'DESC')
            ->orderBy('compras_head.id', 'DESC');

        $compras = $query->paginate($perPage);
        $pager   = $model->pager;

        // RESPUESTA AJAX
        if ($this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {

            $tbody = view('compras/_tbody', ['compras' => $compras]);
            $pagerHtml = $pager->links('default', 'bootstrap_full');

            return $this->response->setJSON([
                'tbody' => $tbody,
                'pager' => $pagerHtml,
            ]);
        }

        return view('compras/index', [
            'compras' => $compras,
            'pager'   => $pager,
        ]);
    }
    public function new()
    {
        $chk = requerirPermiso('ingresar_compras');
        if ($chk !== true) return $chk;

        return view('compras/new');
    }
    public function carga()
    {
        $chk = requerirPermiso('cargar_compras_json');
        if ($chk !== true) return $chk;

        $emisorModel = new \App\Models\EmisorModel();
        $emisor = $emisorModel->first();

        // limpiar por si acaso
        $emisor->nrc = preg_replace('/[^0-9]/', '', $emisor->nrc ?? '');
        $emisor->nit = preg_replace('/[^0-9]/', '', $emisor->nit ?? '');

        return view('compras/carga_procesado', [
            'emisor' => $emisor
        ]);
    }
    public function procesarCarga()
    {
        $files = $this->request->getFiles();

        if (!isset($files['archivos'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron archivos'
            ]);
        }

        $proveedorModel = new ProveedorModel();
        $compraHeadModel = new CompraHeadModel();
        $compraDetalleModel = new CompraDetalleModel();
        $productoModel = new ProductoModel();
        $movModel = new ProductoMovimientoModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $procesadas = 0;

        foreach ($files['archivos'] as $file) {

            if (!$file->isValid()) continue;

            $contenido = file_get_contents($file->getTempName());
            $json = json_decode($contenido, true);

            if (!$json || json_last_error() !== JSON_ERROR_NONE) continue;

            // VALIDAR DUPLICADO
            $numeroControl = $json['identificacion']['numeroControl'] ?? null;

            if (!$numeroControl) continue;

            $existe = $compraHeadModel
                ->where('numero_control', $numeroControl)
                ->first();

            if ($existe) continue;

            // PROVEEDOR
            $proveedorId = null;

            if (!empty($json['emisor'])) {

                $emisor = $json['emisor'];

                $nombre = trim($emisor['nombre'] ?? '');
                $telefono = $emisor['telefono'] ?? null;
                $correo = $emisor['correo'] ?? null;

                $direccion = null;

                if (!empty($emisor['direccion'])) {
                    $direccion = implode(', ', array_filter([
                        $emisor['direccion']['departamento'] ?? null,
                        $emisor['direccion']['municipio'] ?? null,
                        $emisor['direccion']['complemento'] ?? null,
                    ]));
                }

                if ($nombre) {

                    $proveedor = $proveedorModel
                        ->where('nombre', $nombre)
                        ->first();

                    if (!$proveedor) {

                        $proveedorId = $proveedorModel->insert([
                            'nombre'    => $nombre,
                            'telefono'  => $telefono,
                            'email'     => $correo,
                            'direccion' => $direccion,
                        ]);
                    } else {

                        $proveedorId = $proveedor->id;

                        $proveedorModel->update($proveedorId, [
                            'telefono'  => $telefono ?? $proveedor->telefono,
                            'email'     => $correo ?? $proveedor->email,
                            'direccion' => $direccion ?? $proveedor->direccion,
                        ]);
                    }
                }
            }

            // TOTALES
            $total =
                $json['resumen']['totalPagar']
                ?? $json['resumen']['montoTotalOperacion']
                ?? 0;

            $totalGravada = $json['resumen']['totalGravada'] ?? 0;

            $iva = 0;

            if (!empty($json['resumen']['tributos'])) {
                foreach ($json['resumen']['tributos'] as $t) {
                    if (($t['codigo'] ?? null) == '20') {
                        $iva = (float)$t['valor'];
                    }
                }
            }

            // CONDICIÓN
            $condicion = (int)($json['resumen']['condicionOperacion'] ?? 1);
            $plazo = $condicion === 2 ? 30 : null;

            // SALDO
            $tipoDte = $json['identificacion']['tipoDte'] ?? null;

            $saldo = ($tipoDte === '05') ? 0 : $total;

            // INSERT HEAD
            $dataHead = [
                'numero_control'    => $numeroControl,
                'codigo_generacion' => $json['identificacion']['codigoGeneracion'] ?? null,
                'fecha_emision'     => $json['identificacion']['fecEmi'] ?? null,
                'sello_recibido'    => $json['identificacion']['selloRecibido'] ?? null,
                'tipo_dte'          => $json['identificacion']['tipoDte'] ?? null,

                'proveedor_id' => $proveedorId,

                'total_gravada' => $totalGravada,
                'sub_total' => $json['resumen']['subTotal'] ?? 0,
                'total_iva' => $iva,
                'monto_total_operacion' => $json['resumen']['montoTotalOperacion'] ?? 0,
                'total_pagar' => $total,

                'condicion_operacion' => $condicion,
                'plazo_credito' => $plazo,

                'iva_rete1' => $json['resumen']['ivaRete1'] ?? 0,

                'saldo' => $saldo,

                'codigo_generacion_relacionado' =>
                $json['documentoRelacionado'][0]['numeroDocumento'] ?? null,
            ];

            if (!$compraHeadModel->insert($dataHead)) {

                $db->transRollback();

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error insertando compra',
                    'errors' => $compraHeadModel->errors()
                ]);
            }

            $compraId = $compraHeadModel->getInsertID();

            // DETALLES
            if (!empty($json['cuerpoDocumento'])) {

                // 🔥 IVA total del resumen (para distribuir proporcionalmente si ivaItem no viene por línea)
                $ivaTotal = 0;
                if (!empty($json['resumen']['tributos'])) {
                    foreach ($json['resumen']['tributos'] as $t) {
                        if (($t['codigo'] ?? null) == '20') {
                            $ivaTotal = (float)$t['valor'];
                        }
                    }
                }

                $totalGravadaDoc = (float)($json['resumen']['totalGravada'] ?? 0);

                foreach ($json['cuerpoDocumento'] as $item) {

                    $codigo = trim($item['codigo'] ?? '');
                    $codigo = preg_replace('/\s+/', '', $codigo);
                    $codigo = strtoupper($codigo);

                    $descripcion = strtok(trim($item['descripcion'] ?? ''), "\n");

                    // 🔍 BUSCAR PRODUCTO
                    $producto = null;

                    if ($codigo) {
                        $producto = $productoModel
                            ->where('UPPER(codigo)', $codigo)
                            ->first();
                    }

                    // CREAR SI NO EXISTE
                    if (!$producto) {
                        $productoId = $productoModel->insert([
                            'codigo'      => $codigo ?: null,
                            'descripcion' => $descripcion ?: 'SIN DESCRIPCIÓN',
                            'activo'      => 1,
                            'tipo'        => 'AUTO'
                        ]);
                        $producto = $productoModel->find($productoId);
                    }

                    // VALIDAR PRODUCTO ANTES DE CONTINUAR
                    if (empty($producto) || empty($producto->id)) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Producto inválido',
                            'data'    => ['codigo' => $codigo, 'descripcion' => $descripcion]
                        ]);
                    }

                    // ✅ PRECIO FIEL AL JSON (para compras_detalles)
                    $precioUniJson    = (float)($item['precioUni']    ?? 0);  // como viene en el doc
                    $ventaGravada     = (float)($item['ventaGravada'] ?? 0);
                    $montoDescu       = (float)($item['montoDescu']   ?? 0);
                    $cantidadNueva    = (float)($item['cantidad']     ?? 1);

                    // ✅ IVA proporcional (para movimiento e inventario)
                    $ivaItem = (float)($item['ivaItem'] ?? 0);
                    if ($ivaItem == 0 && $tipoDte === '03' && $totalGravadaDoc > 0) {
                        $ivaItem = round($ivaTotal * ($ventaGravada / $totalGravadaDoc), 2);
                    }

                    // ✅ COSTO REAL (para productos_movimientos y costo_promedio)
                    if ($tipoDte === '03') {
                        $costoConIva      = $ventaGravada + $ivaItem;
                        $costoUnitarioMov = $cantidadNueva > 0 ? $costoConIva / $cantidadNueva : 0;
                    } else {
                        $costoConIva      = $ventaGravada;
                        $costoUnitarioMov = $cantidadNueva > 0 ? $costoConIva / $cantidadNueva : 0;
                    }

                    // INSERT DETALLE → fiel al documento físico
                    $detalle = [
                        'compra_id'       => $compraId,
                        'num_item'        => $item['numItem']      ?? null,
                        'tipo_item'       => $item['tipoItem']     ?? null,
                        'codigo'          => $codigo,
                        'descripcion'     => $descripcion,
                        'cantidad'        => (float)($item['cantidad']     ?? 0),
                        'unidad_medida'   => $item['uniMedida']    ?? null,
                        'precio_unitario' => (float)($item['precioUni']    ?? 0),  // 👈 tal cual el JSON
                        'venta_gravada'   => (float)($item['ventaGravada'] ?? 0),  // 👈 tal cual el JSON
                        'monto_descuento' => (float)($item['montoDescu']   ?? 0),  // 👈 tal cual el JSON
                        'iva_item'        => (float)($item['ivaItem']      ?? 0),  // 👈 tal cual el JSON
                        'producto_id'     => $producto->id
                    ];

                    if (!$compraDetalleModel->insert($detalle)) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Error insertando detalle',
                            'errors'  => $compraDetalleModel->errors(),
                            'data'    => $detalle
                        ]);
                    }

                    // COSTO PROMEDIO PONDERADO
                    $stock = $movModel
                        ->select('
                            SUM(CASE WHEN tipo_movimiento = "ENTRADA" THEN cantidad ELSE 0 END) -
                            SUM(CASE WHEN tipo_movimiento = "SALIDA"  THEN cantidad ELSE 0 END)
                            as stock
                        ')
                        ->where('producto_id', $producto->id)
                        ->first();

                    $stockActual = (float)($stock->stock ?? 0);
                    $costoActual = (float)($producto->costo_promedio ?? 0);

                    // INSERT MOVIMIENTO → costo real con IVA
                    $movData = [
                        'producto_id'     => $producto->id,
                        'tipo_movimiento' => 'ENTRADA',
                        'cantidad'        => $cantidadNueva,
                        'costo_unitario'  => $costoUnitarioMov,   // 👈 precio real pagado
                        'referencia_tipo' => 'compra',
                        'referencia_id'   => $compraId,
                    ];

                    // Costo promedio también usa costoUnitarioMov
                    if ($stockActual > 0) {
                        $nuevoCosto = (
                            ($stockActual * $costoActual) +
                            ($cantidadNueva * $costoUnitarioMov)
                        ) / ($stockActual + $cantidadNueva);
                    } else {
                        $nuevoCosto = $costoUnitarioMov;
                    }
                    if (!$movModel->insert($movData)) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Error insertando movimiento',
                            'errors'  => $movModel->errors(),
                            'data'    => $movData
                        ]);
                    }

                    // ACTUALIZAR COSTO PROMEDIO DEL PRODUCTO
                    $productoModel->update($producto->id, [
                        'costo_promedio' => $nuevoCosto
                    ]);
                }
            }

            $procesadas++;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {

            $error = $db->error();

            log_message('error', '❌ ERROR BD EN PROCESAR COMPRA');
            log_message('error', 'Código: ' . ($error['code'] ?? 'N/A'));
            log_message('error', 'Mensaje: ' . ($error['message'] ?? 'N/A'));

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en la base de datos'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Compras procesadas correctamente",
            'total' => $procesadas
        ]);
    }
    public function validarProductos()
    {
        $data = $this->request->getJSON(true);
        $productos = $data['productos'] ?? [];

        $productoModel = new ProductoModel();

        $noExistentes = [];

        foreach ($productos as $p) {

            $codigo = trim($p['codigo'] ?? '');

            // 🔥 limpiar código (por seguridad)
            $codigo = preg_replace('/\s+/', '', $codigo);
            $codigo = strtoupper($codigo);

            if (!$codigo) {
                // si no trae código, lo consideras inválido
                $noExistentes[] = 'SIN CÓDIGO';
                continue;
            }

            $producto = $productoModel
                ->where('UPPER(codigo)', $codigo)
                ->first();

            if (!$producto) {
                $noExistentes[] = $codigo;
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'no_existen' => $noExistentes
        ]);
    }
    public function show($id)
    {
        $compraHeadModel = new CompraHeadModel();
        $compraDetalleModel = new CompraDetalleModel();

        // 🔥 TRAER COMPRA + PROVEEDOR
        $compra = $compraHeadModel
            ->select('compras_head.*, proveedores.nombre as proveedor_nombre')
            ->join('proveedores', 'proveedores.id = compras_head.proveedor_id', 'left')
            ->where('compras_head.id', $id)
            ->first();

        if (!$compra) {
            return redirect()->to(base_url('purchases'))
                ->with('error', 'Compra no encontrada');
        }

        // 🔥 DETALLES
        $detalles = $compraDetalleModel
            ->where('compra_id', $id)
            ->orderBy('num_item', 'ASC')
            ->findAll();

        // 🔥 DATA PARA VIEW
        $data = [
            'compra'   => $compra,
            'detalles' => $detalles,
        ];

        return view('compras/show', $data);
    }
    public function delete($id)
    {
        $compraHeadModel = new \App\Models\CompraHeadModel();
        $compraDetalleModel = new \App\Models\CompraDetalleModel();
        $movModel = new \App\Models\ProductoMovimientoModel();
        $productoModel = new \App\Models\ProductoModel();

        $db = \Config\Database::connect();
        $db->transStart();

        // 🔍 VALIDAR EXISTENCIA
        $compra = $compraHeadModel->find($id);

        if (!$compra) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Compra no encontrada'
            ]);
        }

        // 🔥 OBTENER PRODUCTOS AFECTADOS
        $detalles = $compraDetalleModel
            ->where('compra_id', $id)
            ->findAll();

        $productosIds = [];

        foreach ($detalles as $d) {
            if (!empty($d->producto_id)) {
                $productosIds[] = $d->producto_id;
            }
        }

        $productosIds = array_unique($productosIds);

        // ❌ BORRAR MOVIMIENTOS
        $movModel
            ->where('referencia_tipo', 'COMPRA')
            ->where('referencia_id', $id)
            ->delete();

        // ❌ BORRAR DETALLES
        $compraDetalleModel
            ->where('compra_id', $id)
            ->delete();

        // ❌ BORRAR HEAD
        if (!$compraHeadModel->delete($id)) {

            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error eliminando compra'
            ]);
        }

        // =============================
        // 🔥 RECALCULAR COSTOS
        // =============================

        foreach ($productosIds as $productoId) {

            $movimientos = $movModel
                ->where('producto_id', $productoId)
                ->orderBy('id', 'ASC')
                ->findAll();

            $stock = 0;
            $costoPromedio = 0;

            foreach ($movimientos as $m) {

                if ($m->tipo_movimiento === 'ENTRADA') {

                    $cantidad = (float)$m->cantidad;
                    $costo = (float)$m->costo_unitario;

                    if ($stock > 0) {

                        $costoPromedio = (
                            ($stock * $costoPromedio) +
                            ($cantidad * $costo)
                        ) / ($stock + $cantidad);
                    } else {

                        $costoPromedio = $costo;
                    }

                    $stock += $cantidad;
                } elseif ($m->tipo_movimiento === 'SALIDA') {

                    $stock -= (float)$m->cantidad;

                    // 🔥 opcional: evitar negativos
                    if ($stock < 0) $stock = 0;
                }
            }

            // 🔥 ACTUALIZAR PRODUCTO
            $productoModel->update($productoId, [
                'costo_promedio' => $costoPromedio
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en la base de datos'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Compra eliminada y costos recalculados correctamente'
        ]);
    }
    public function validarDocumento()
    {
        $data = $this->request->getJSON(true);

        $codigo = $data['codigo'] ?? null;

        if (!$codigo) {
            return $this->response->setJSON(['ok' => false]);
        }

        $model = new CompraHeadModel();

        $existe = $model
            ->where('codigo_generacion', $codigo)
            ->first();

        return $this->response->setJSON([
            'ok' => true,
            'existe' => $existe ? true : false
        ]);
    }
    public function preview($id)
    {
        $compraModel = new CompraHeadModel();
        $detalleModel = new CompraDetalleModel();
        $pagosModel = new PagosComprasDetallesModel();

        // 🔥 JOIN con proveedores
        $compra = $compraModel
            ->select('compras_head.*, proveedores.nombre as proveedor_nombre')
            ->join('proveedores', 'proveedores.id = compras_head.proveedor_id')
            ->where('compras_head.id', $id)
            ->first();

        if (!$compra) {
            return 'Compra no encontrada';
        }

        $detalles = $detalleModel->getByCompra($id);

        // Pagos aplicados
        $pagos = $pagosModel
            ->select('pagos_compras_detalles.*, pagos_compras_head.fecha_pago, pagos_compras_head.forma_pago')
            ->join('pagos_compras_head', 'pagos_compras_head.id = pagos_compras_detalles.pago_id')
            ->where('pagos_compras_detalles.compra_id', $id)
            ->findAll();

        return view('compras/_preview_modal', [
            'factura' => $compra,
            'detalles' => $detalles,
            'pagos' => $pagos
        ]);
    }
}
