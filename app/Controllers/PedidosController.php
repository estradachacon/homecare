<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PedidoHeadModel;
use App\Models\PedidoDetalleModel;
use App\Models\PedidoLogModel;
use App\Models\ClienteModel;

class PedidosController extends BaseController
{
    // ──────────────────────────────────────────────
    //  LISTADO
    // ──────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('ver_pedidos');
        if ($chk !== true) return $chk;

        $model   = new PedidoHeadModel();
        $session = session();

        $filtros = [
            'q'              => $this->request->getGet('q'),
            'vendedor_id'    => $this->request->getGet('vendedor_id'),
            'estado'         => $this->request->getGet('estado'),
            'tipo_documento' => $this->request->getGet('tipo_documento'),
            'fecha_inicio'   => $this->request->getGet('fecha_inicio'),
            'fecha_fin'      => $this->request->getGet('fecha_fin'),
        ];

        $pedidos = $model->listar($filtros)->paginate(15);
        $pager   = $model->pager;

        $db      = \Config\Database::connect();
        $sellers = $db->table('sellers')->select('id, seller')->orderBy('seller', 'ASC')->get()->getResult();

        return view('pedidos/index', [
            'pedidos'  => $pedidos,
            'pager'    => $pager,
            'filtros'  => $filtros,
            'sellers'  => $sellers,
        ]);
    }

    // ──────────────────────────────────────────────
    //  FORMULARIO CREAR
    // ──────────────────────────────────────────────

    public function crear()
    {
        $chk = requerirPermiso('crear_pedidos');
        if ($chk !== true) return $chk;

        $model   = new PedidoHeadModel();
        $session = session();

        $db     = \Config\Database::connect();
        $seller = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id, sellers.seller')
            ->get()->getRow();

        return view('pedidos/crear', [
            'numero_sugerido' => $model->siguienteNumero(),
            'vendedor_nombre' => $seller->seller ?? $session->get('user_name'),
            'vendedor_id'     => $seller->id     ?? null,
        ]);
    }

    // ──────────────────────────────────────────────
    //  GUARDAR NUEVA NOTA
    // ──────────────────────────────────────────────

    public function guardar()
    {
        $chk = requerirPermiso('crear_pedidos');
        if ($chk !== true) return $chk;

        $db      = \Config\Database::connect();
        $session = session();

        $productos = $this->request->getPost('productos') ?? [];
        if (empty($productos)) {
            return redirect()->back()->withInput()->with('error', 'Debe agregar al menos un producto.');
        }

        $clienteId = (int)$this->request->getPost('cliente_id');
        if (!$clienteId) {
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar un cliente.');
        }

        $tipoPago    = $this->request->getPost('tipo_pago');
        $diasCredito = null;
        if ($tipoPago === 'credito') {
            $diasCredito = (int)$this->request->getPost('dias_credito') ?: 30;
        }

        $tipoDoc = $this->request->getPost('tipo_documento');

        $headModel = new PedidoHeadModel();
        $detModel  = new PedidoDetalleModel();

        $anio    = (int) date('Y');
        $secuencia = $headModel->siguienteSecuencia();
        $numero    = 'NP-' . $anio . '-' . str_pad($secuencia, 5, '0', STR_PAD_LEFT);

        // Resolve vendor for price lookup
        $sellerRow  = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id')
            ->get()->getRow();
        $vendedorId = $sellerRow ? (int)$sellerRow->id : 0;

        $productoIds = array_values(array_unique(array_filter(array_map(static function ($p) {
            return (int)($p['producto_id'] ?? 0);
        }, $productos))));

        $productosInfo = [];
        if (!empty($productoIds)) {
            $rows = $db->table('productos')
                ->select('id, descripcion, precio_minimo')
                ->whereIn('id', $productoIds)
                ->get()
                ->getResult();
            foreach ($rows as $row) {
                $productosInfo[(int)$row->id] = [
                    'nombre' => $row->descripcion,
                    'minimo' => (float)($row->precio_minimo ?? 0),
                ];
            }
        }

        $precioModel   = new \App\Models\ConsignacionPrecioModel();
        $erroresPrecio = [];

        foreach ($productos as $idx => $p) {
            if (empty($p['producto_id']) || (float)($p['cantidad'] ?? 0) <= 0) continue;
            $productoId = (int)$p['producto_id'];
            $cant       = (float)$p['cantidad'];
            $precio     = (float)($p['precio_unitario'] ?? 0);
            $info       = $productosInfo[$productoId] ?? ['nombre' => "Producto #{$productoId}", 'minimo' => 0];
            $minimoDb   = $info['minimo'];
            $rec        = $vendedorId ? $precioModel->getPrecioRecomendado($vendedorId, $productoId, $clienteId) : null;
            $piso       = max($minimoDb, $rec ?? 0);

            if ($precio <= 0) {
                $erroresPrecio[] = '• ' . $info['nombre'] . ': No se ha introducido el precio.';
            } elseif ($piso > 0 && $precio < $piso) {
                $etiqueta   = ($rec !== null && $rec > 0) ? 'precio configurado' : 'mínimo permitido';
                $referencia = ($rec !== null && $rec > 0) ? $rec : $minimoDb;
                $erroresPrecio[] = '• ' . $info['nombre'] . ': Precio ' . number_format($precio, 2) . ' es menor al ' . $etiqueta . ' ' . number_format($referencia, 2) . '.';
            }

            $productos[$idx]['precio_minimo'] = $piso;
            $productos[$idx]['subtotal']      = round($cant * $precio, 2);
        }

        if (!empty($erroresPrecio)) {
            return redirect()->back()->withInput()->with('error', 'Revisa los precios:<br>' . implode('<br>', $erroresPrecio));
        }

        // Calcular totales
        $subtotal = 0;
        foreach ($productos as $p) {
            $subtotal += (float)($p['subtotal'] ?? 0);
        }

        $iva   = ($tipoDoc === 'credito_fiscal') ? round($subtotal * 0.13, 2) : 0;
        $total = round($subtotal + $iva, 2);

        $db->transStart();

        $db     = \Config\Database::connect();
        $seller = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id')
            ->get()->getRow();

        $pedidoId = $headModel->insert([
            'numero'          => $numero,
            'anio'            => $anio,
            'secuencia'       => $secuencia,
            'cliente_id'      => $clienteId,
            'vendedor_id'     => $seller->id ?? null,
            'consignacion_id'  => (int)$this->request->getPost('consignacion_id') ?: null,
            'consignacion_ids' => $this->request->getPost('consignacion_ids') ?: null,
            'doctor_id'        => (int)$this->request->getPost('doctor_id')       ?: null,
            'paciente_id'      => (int)$this->request->getPost('paciente_id')     ?: null,
            'tipo_documento'  => $tipoDoc,
            'tipo_pago'       => $tipoPago,
            'dias_credito'    => $diasCredito,
            'notas'           => $this->request->getPost('notas'),
            'subtotal'        => $subtotal,
            'iva'             => $iva,
            'total'           => $total,
            'estado'          => 'pendiente',
            'created_by'      => $session->get('id'),
        ]);

        foreach ($productos as $p) {
            if (empty($p['producto_id']) || (float)($p['cantidad'] ?? 0) <= 0) continue;
            $cant    = (float)$p['cantidad'];
            $precio  = (float)$p['precio_unitario'];
            $minimo  = (float)($p['precio_minimo'] ?? 0);
            $detModel->insert([
                'pedido_id'       => $pedidoId,
                'producto_id'     => (int)$p['producto_id'],
                'cantidad'        => $cant,
                'precio_unitario' => $precio,
                'precio_minimo'   => $minimo,
                'subtotal'        => round($cant * $precio, 2),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Error al guardar la nota de pedido.');
        }

        registrar_bitacora('Crear nota de pedido', 'Pedidos', 'Se creó la nota ' . $numero . '.', $session->get('id'));
        $this->log($pedidoId, 'Nota creada');

        return redirect()->to('/pedidos/' . $pedidoId)->with('success', 'Nota de pedido creada correctamente.');
    }

    // ──────────────────────────────────────────────
    //  DETALLE / VIEW
    // ──────────────────────────────────────────────

    public function show(int $id)
    {
        $chk = requerirPermiso('ver_pedidos');
        if ($chk !== true) return $chk;

        $headModel = new PedidoHeadModel();
        $detModel  = new PedidoDetalleModel();
        $logModel  = new PedidoLogModel();

        $pedido = $headModel->getConRelaciones($id);
        if (!$pedido) {
            return redirect()->to('/pedidos')->with('error', 'Nota no encontrada.');
        }

        $detalles = $detModel->getPorPedido($id);
        $log      = $logModel->where('pedido_id', $id)->orderBy('created_at', 'ASC')->findAll();

        // Collect NE IDs from the pedido
        $neIds = [];
        if ($pedido->consignacion_id) {
            $neIds[] = (int)$pedido->consignacion_id;
        }
        if ($pedido->consignacion_ids) {
            $decoded = json_decode($pedido->consignacion_ids, true);
            if (is_array($decoded)) {
                foreach ($decoded as $nid) {
                    $nid = (int)$nid;
                    if ($nid && !in_array($nid, $neIds)) $neIds[] = $nid;
                }
            }
        }

        $lotesNE = [];
        if (!empty($neIds)) {
            $db      = \Config\Database::connect();
            $inList  = implode(',', $neIds);
            $lotesNE = $db->query("
                SELECT
                    ch.numero              AS ne_numero,
                    p.codigo               AS producto_codigo,
                    p.descripcion          AS producto_nombre,
                    cl.numero_lote,
                    cl.fecha_vencimiento,
                    SUM(cdl.cantidad)      AS cantidad
                FROM consignaciones_detalles cd
                JOIN consignaciones_head ch ON ch.id = cd.consignacion_id
                JOIN productos p            ON p.id  = cd.producto_id
                INNER JOIN consignacion_detalle_lotes cdl ON cdl.detalle_id = cd.id
                INNER JOIN consignacion_lotes cl          ON cl.id = cdl.lote_id
                WHERE cd.consignacion_id IN ({$inList})
                GROUP BY cd.id, cdl.lote_id
                ORDER BY p.descripcion, cl.numero_lote
            ")->getResultObject();
        }

        return view('pedidos/detalle', [
            'pedido'   => $pedido,
            'detalles' => $detalles,
            'log'      => $log,
            'lotesNE'  => $lotesNE,
        ]);
    }

    // ──────────────────────────────────────────────
    //  FORMULARIO EDITAR
    // ──────────────────────────────────────────────

    public function editar(int $id)
    {
        $chk = requerirPermiso('editar_pedidos');
        if ($chk !== true) return $chk;

        $headModel = new PedidoHeadModel();
        $detModel  = new PedidoDetalleModel();

        $pedido = $headModel->getConRelaciones($id);
        if (!$pedido || $pedido->estado !== 'pendiente') {
            return redirect()->to('/pedidos/' . $id)->with('error', 'Solo se pueden editar notas en estado Pendiente.');
        }

        $detalles = $detModel->getPorPedido($id);

        return view('pedidos/editar', [
            'pedido'   => $pedido,
            'detalles' => $detalles,
        ]);
    }

    // ──────────────────────────────────────────────
    //  ACTUALIZAR
    // ──────────────────────────────────────────────

    public function actualizar(int $id)
    {
        $chk = requerirPermiso('editar_pedidos');
        if ($chk !== true) return $chk;

        $db      = \Config\Database::connect();
        $session = session();

        $headModel = new PedidoHeadModel();
        $detModel  = new PedidoDetalleModel();

        $pedido = $headModel->find($id);
        if (!$pedido || $pedido->estado !== 'pendiente') {
            return redirect()->to('/pedidos/' . $id)->with('error', 'No se puede editar esta nota.');
        }

        $productos = $this->request->getPost('productos') ?? [];
        if (empty($productos)) {
            return redirect()->back()->withInput()->with('error', 'Debe agregar al menos un producto.');
        }

        $clienteId = (int)$this->request->getPost('cliente_id');
        if (!$clienteId) {
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar un cliente.');
        }

        $tipoPago    = $this->request->getPost('tipo_pago');
        $diasCredito = null;
        if ($tipoPago === 'credito') {
            $diasCredito = (int)$this->request->getPost('dias_credito') ?: 30;
        }

        $tipoDoc  = $this->request->getPost('tipo_documento');

        $vendedorId = (int)($pedido->vendedor_id ?? 0);

        $productoIds = array_values(array_unique(array_filter(array_map(static function ($p) {
            return (int)($p['producto_id'] ?? 0);
        }, $productos))));

        $productosInfo = [];
        if (!empty($productoIds)) {
            $rows = $db->table('productos')
                ->select('id, descripcion, precio_minimo')
                ->whereIn('id', $productoIds)
                ->get()
                ->getResult();
            foreach ($rows as $row) {
                $productosInfo[(int)$row->id] = [
                    'nombre' => $row->descripcion,
                    'minimo' => (float)($row->precio_minimo ?? 0),
                ];
            }
        }

        $precioModel   = new \App\Models\ConsignacionPrecioModel();
        $erroresPrecio = [];

        foreach ($productos as $idx => $p) {
            if (empty($p['producto_id']) || (float)($p['cantidad'] ?? 0) <= 0) continue;
            $productoId = (int)$p['producto_id'];
            $cant       = (float)$p['cantidad'];
            $precio     = (float)($p['precio_unitario'] ?? 0);
            $info       = $productosInfo[$productoId] ?? ['nombre' => "Producto #{$productoId}", 'minimo' => 0];
            $minimoDb   = $info['minimo'];
            $rec        = $vendedorId ? $precioModel->getPrecioRecomendado($vendedorId, $productoId, $clienteId) : null;
            $piso       = max($minimoDb, $rec ?? 0);

            if ($precio <= 0) {
                $erroresPrecio[] = '• ' . $info['nombre'] . ': No se ha introducido el precio.';
            } elseif ($piso > 0 && $precio < $piso) {
                $etiqueta   = ($rec !== null && $rec > 0) ? 'precio configurado' : 'mínimo permitido';
                $referencia = ($rec !== null && $rec > 0) ? $rec : $minimoDb;
                $erroresPrecio[] = '• ' . $info['nombre'] . ': Precio ' . number_format($precio, 2) . ' es menor al ' . $etiqueta . ' ' . number_format($referencia, 2) . '.';
            }

            $productos[$idx]['precio_minimo'] = $piso;
            $productos[$idx]['subtotal']      = round($cant * $precio, 2);
        }

        if (!empty($erroresPrecio)) {
            return redirect()->back()->withInput()->with('error', 'Revisa los precios:<br>' . implode('<br>', $erroresPrecio));
        }

        $subtotal = 0;
        foreach ($productos as $p) {
            $subtotal += (float)($p['subtotal'] ?? 0);
        }
        $iva   = ($tipoDoc === 'credito_fiscal') ? round($subtotal * 0.13, 2) : 0;
        $total = round($subtotal + $iva, 2);

        $existentes    = $detModel->getPorPedido($id);
        $existentesMap = [];
        foreach ($existentes as $d) {
            $existentesMap[(int)$d->id] = $d;
        }

        $db->transStart();

        $submitIds = [];
        foreach ($productos as $p) {
            if (empty($p['producto_id']) || (float)($p['cantidad'] ?? 0) <= 0) continue;
            $cant      = (float)$p['cantidad'];
            $precio    = (float)$p['precio_unitario'];
            $minimo    = (float)($p['precio_minimo'] ?? 0);
            $detalleId = (int)($p['detalle_id'] ?? 0);
            $sub       = round($cant * $precio, 2);

            if ($detalleId && isset($existentesMap[$detalleId])) {
                $detModel->update($detalleId, [
                    'producto_id'     => (int)$p['producto_id'],
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'precio_minimo'   => $minimo,
                    'subtotal'        => $sub,
                ]);
                $submitIds[] = $detalleId;
            } else {
                $newId = (int)$detModel->insert([
                    'pedido_id'       => $id,
                    'producto_id'     => (int)$p['producto_id'],
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'precio_minimo'   => $minimo,
                    'subtotal'        => $sub,
                ]);
                $submitIds[] = $newId;
            }
        }

        foreach (array_keys($existentesMap) as $existId) {
            if (!in_array($existId, $submitIds)) {
                $detModel->delete($existId);
            }
        }

        $headModel->update($id, [
            'cliente_id'     => $clienteId,
            'tipo_documento' => $tipoDoc,
            'tipo_pago'      => $tipoPago,
            'dias_credito'   => $diasCredito,
            'notas'          => $this->request->getPost('notas'),
            'subtotal'       => $subtotal,
            'iva'            => $iva,
            'total'          => $total,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar la nota de pedido.');
        }

        registrar_bitacora('Editar nota de pedido', 'Pedidos', 'Se editó la nota ' . $pedido->numero . '.', $session->get('id'));
        $this->log($id, 'Nota editada');

        return redirect()->to('/pedidos/' . $id)->with('success', 'Nota de pedido actualizada correctamente.');
    }

    // ──────────────────────────────────────────────
    //  ANULAR (AJAX)
    // ──────────────────────────────────────────────

    public function anular(int $id)
    {
        $chk = requerirPermiso('anular_pedidos');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $session   = session();
        $headModel = new PedidoHeadModel();
        $pedido    = $headModel->find($id);

        if (!$pedido) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);
        }
        if ($pedido->anulada) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya está anulada.']);
        }
        if ($pedido->estado === 'facturada') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede anular una nota ya facturada.']);
        }

        $headModel->update($id, [
            'estado'          => 'anulada',
            'anulada'         => 1,
            'anulada_por'     => $session->get('id'),
            'fecha_anulacion' => date('Y-m-d H:i:s'),
        ]);

        registrar_bitacora('Anular nota de pedido', 'Pedidos', 'Se anuló la nota ' . $pedido->numero . '.', $session->get('id'));
        $this->log($id, 'Nota anulada');

        return $this->response->setJSON(['success' => true, 'message' => 'Nota anulada correctamente.']);
    }

    // ──────────────────────────────────────────────
    //  ASOCIAR FACTURA (AJAX)
    // ──────────────────────────────────────────────

    public function asociarFactura(int $id)
    {
        $chk = requerirPermiso('editar_pedidos');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $session   = session();
        $headModel = new PedidoHeadModel();
        $pedido    = $headModel->find($id);

        if (!$pedido) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);
        }
        if ($pedido->estado === 'anulada') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede asociar factura a una nota anulada.']);
        }

        $body      = $this->request->getJSON(true) ?? [];
        $facturaId = (int)($body['factura_id'] ?? 0);

        if (!$facturaId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Debe indicar una factura válida.']);
        }

        $db     = \Config\Database::connect();
        $fRow   = $db->table('facturas_head')->where('id', $facturaId)->where('anulada', 0)->get()->getRow();
        if (!$fRow) {
            return $this->response->setJSON(['success' => false, 'message' => 'Factura no encontrada o anulada.']);
        }

        $headModel->update($id, [
            'factura_id' => $facturaId,
            'estado'     => 'facturada',
        ]);

        $this->log($id, 'Factura asociada', 'Factura: ' . $fRow->numero_control);
        registrar_bitacora('Asociar factura a pedido', 'Pedidos', 'Pedido ' . $pedido->numero . ' → Factura ' . $fRow->numero_control . '.', $session->get('id'));

        return $this->response->setJSON([
            'success'         => true,
            'message'         => 'Factura asociada correctamente.',
            'factura_numero'  => $fRow->numero_control,
        ]);
    }

    // ──────────────────────────────────────────────
    //  AJAX: precio mínimo de un producto
    // ──────────────────────────────────────────────

    public function getPrecioProducto()
    {
        $productoId = (int)$this->request->getGet('producto_id');
        if (!$productoId) {
            return $this->response->setJSON(['precio_minimo' => 0, 'precio_recomendado' => null]);
        }

        $session  = session();
        $clienteId = (int)($this->request->getGet('cliente_id') ?: 0) ?: null;

        $db  = \Config\Database::connect();
        $row = $db->table('productos')->select('precio_minimo')->where('id', $productoId)->get()->getRow();

        $precioMinimo = $row ? (float)$row->precio_minimo : 0;

        // Buscar el seller del usuario logueado
        $seller = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id')
            ->get()->getRow();

        $precioRecomendado = null;
        if ($seller) {
            $precioRecomendado = (new \App\Models\ConsignacionPrecioModel())
                ->getPrecioRecomendado($seller->id, $productoId, $clienteId);
        }

        return $this->response->setJSON([
            'precio_minimo'      => $precioMinimo,
            'precio_recomendado' => $precioRecomendado,
        ]);
    }

    // ──────────────────────────────────────────────
    //  AJAX: facturas disponibles del cliente
    // ──────────────────────────────────────────────

    public function facturasCliente(int $clienteId)
    {
        $db       = \Config\Database::connect();
        $q        = trim($this->request->getGet('q') ?? '');
        $pedidoId = (int)($this->request->getGet('pedido_id') ?? 0);

        // IDs already claimed by other pedidos
        $linkedIds = $db->table('pedidos_head')
            ->select('factura_id')
            ->where('factura_id IS NOT NULL')
            ->where('anulada', 0);
        if ($pedidoId) {
            $linkedIds->where('id !=', $pedidoId);
        }
        $linkedIds = array_column($linkedIds->get()->getResultArray(), 'factura_id');

        $builder = $db->table('facturas_head')
            ->select('facturas_head.id, facturas_head.numero_control, facturas_head.fecha_emision, facturas_head.total_pagar, facturas_head.tipo_dte')
            ->where('facturas_head.receptor_id', $clienteId)
            ->where('facturas_head.anulada', 0)
            ->orderBy('facturas_head.fecha_emision', 'DESC')
            ->limit(200);

        if (!empty($linkedIds)) {
            $builder->whereNotIn('facturas_head.id', $linkedIds);
        }
        if ($q !== '') {
            $builder->like('facturas_head.numero_control', $q);
        }

        $facturas = $builder->get()->getResult();
        $siglas   = function_exists('dte_siglas') ? dte_siglas() : [];

        $results = [];
        foreach ($facturas as $f) {
            $correlativo = substr($f->numero_control, -6);
            $sigla       = $siglas[$f->tipo_dte] ?? $f->tipo_dte ?? '';
            $results[] = [
                'id'          => (int)$f->id,
                'numero'      => $f->numero_control,
                'correlativo' => $correlativo,
                'sigla'       => $sigla,
                'fecha'       => $f->fecha_emision ? date('d/m/Y', strtotime($f->fecha_emision)) : '—',
                'total'       => number_format((float)$f->total_pagar, 2),
                'text'        => $correlativo . ' — $' . number_format((float)$f->total_pagar, 2),
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    // ──────────────────────────────────────────────
    //  AJAX: crear cliente rápido
    // ──────────────────────────────────────────────

    public function clienteStoreAjax()
    {
        $chk = requerirPermiso('crear_clientes');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso para crear clientes.']);
        }

        $nombre = trim($this->request->getPost('nombre') ?? '');
        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es obligatorio.']);
        }

        $model = new ClienteModel();

        $id = $model->insert([
            'tipo_documento'   => $this->request->getPost('tipo_documento') ?: 'DUI',
            'numero_documento' => trim($this->request->getPost('numero_documento') ?? ''),
            'nrc'              => trim($this->request->getPost('nrc') ?? '') ?: null,
            'nombre'           => $nombre,
            'telefono'         => trim($this->request->getPost('telefono') ?? '') ?: null,
            'correo'           => trim($this->request->getPost('correo') ?? '') ?: null,
            'departamento'     => trim($this->request->getPost('departamento') ?? '') ?: null,
            'municipio'        => trim($this->request->getPost('municipio') ?? '') ?: null,
            'direccion'        => trim($this->request->getPost('direccion') ?? '') ?: null,
        ]);

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo crear el cliente.']);
        }

        return $this->response->setJSON([
            'success' => true,
            'cliente' => ['id' => $id, 'text' => $nombre],
            'csrf'    => csrf_hash(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  HELPER PRIVADO: log interno
    // ──────────────────────────────────────────────

    private function log(int $pedidoId, string $accion, ?string $detalle = null): void
    {
        $session  = session();
        $userId   = $session->get('id');
        $userName = $session->get('user_name') ?? ('Usuario #' . $userId);

        (new PedidoLogModel())->insert([
            'pedido_id'   => $pedidoId,
            'user_id'     => $userId,
            'user_nombre' => $userName,
            'accion'      => $accion,
            'detalle'     => $detalle,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
