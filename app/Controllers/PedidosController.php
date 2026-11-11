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
            'departamentos'   => $db->table('hacienda_departamentos')
                ->where('activo', 1)
                ->orderBy('codigo', 'ASC')
                ->get()
                ->getResult(),
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

        $errorDisponibilidad = $this->_validarDisponibilidadNE(
            $productos,
            (int)$this->request->getPost('consignacion_id') ?: null,
            $this->request->getPost('consignacion_ids')
        );
        if ($errorDisponibilidad) {
            return redirect()->back()->withInput()->with('error', $errorDisponibilidad);
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

        // Con el permiso "ver_documentos_todos_vendedores", el vendedor de la
        // NP puede ser distinto al del usuario que la crea (p.ej. al importar
        // productos de una NE de otro vendedor). Sin el permiso, o si el valor
        // enviado no corresponde a un vendedor real, se mantiene el
        // comportamiento original: siempre el vendedor propio del usuario.
        $vendedorIdFinal = $seller->id ?? null;
        if (tienePermiso('ver_documentos_todos_vendedores')) {
            $vendedorIdPost = (int)($this->request->getPost('vendedor_id') ?: 0);
            if ($vendedorIdPost && $db->table('sellers')->where('id', $vendedorIdPost)->countAllResults() > 0) {
                $vendedorIdFinal = $vendedorIdPost;
            }
        }

        $pedidoId = $headModel->insert([
            'numero'          => $numero,
            'anio'            => $anio,
            'secuencia'       => $secuencia,
            'cliente_id'      => $clienteId,
            'vendedor_id'     => $vendedorIdFinal,
            'consignacion_id'  => (int)$this->request->getPost('consignacion_id') ?: null,
            'consignacion_ids' => $this->_normalizarConsignacionIdsJson($this->request->getPost('consignacion_ids')),
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
    //  VERSIÓN IMPRIMIBLE (doble copia, hoja carta)
    // ──────────────────────────────────────────────

    public function imprimir(int $id)
    {
        $chk = requerirPermiso('ver_pedidos');
        if ($chk !== true) return $chk;

        $headModel = new PedidoHeadModel();
        $detModel  = new PedidoDetalleModel();

        $pedido = $headModel->getConRelaciones($id);
        if (!$pedido) {
            return redirect()->to('/pedidos')->with('error', 'Nota no encontrada.');
        }

        $detalles = $detModel->getPorPedido($id);

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

        // Qué lotes (y de qué NE) le corresponden realmente a esta NP por
        // producto. No se puede repartir a ciegas entre las NE vinculadas en
        // orden de número: si esta NP comparte producto con otra NP más
        // antigua sobre las mismas NE, hay que respetar lo que esa otra NP
        // ya consumió (ver ConsignacionesController::_consumoLotesNPPorProducto,
        // la misma simulación que ya usa el cierre y los reportes).
        $lotesPorProducto = [];
        if (!empty($neIds)) {
            $consignacionesCtrl = new ConsignacionesController();

            foreach ($detalles as $d) {
                $consumo = $consignacionesCtrl->_consumoLotesNPPorProducto((int)$id, (int)$d->producto_id, $neIds);
                foreach ($consumo as $c) {
                    $lotesPorProducto[$d->producto_codigo][] = (object)[
                        'producto_codigo'   => $d->producto_codigo,
                        'ne_numero'         => $c['ne_numero'],
                        'numero_lote'       => $c['numero_lote'],
                        'fecha_vencimiento' => $c['fecha_vencimiento'],
                        'cantidad'          => $c['cantidad'],
                    ];
                }
            }
        }

        return view('pedidos/imprimir', [
            'pedido'           => $pedido,
            'detalles'         => $detalles,
            'lotesPorProducto' => $lotesPorProducto,
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

        // Con el permiso "ver_documentos_todos_vendedores" se puede reasignar
        // el vendedor de la NP. Sin el permiso, o si el valor enviado no
        // corresponde a un vendedor real, se conserva el vendedor actual.
        $vendedorIdFinal = (int)($pedido->vendedor_id ?? 0);
        if (tienePermiso('ver_documentos_todos_vendedores')) {
            $vendedorIdPost = (int)($this->request->getPost('vendedor_id') ?: 0);
            if ($vendedorIdPost && $db->table('sellers')->where('id', $vendedorIdPost)->countAllResults() > 0) {
                $vendedorIdFinal = $vendedorIdPost;
            }
        }

        $vendedorId = $vendedorIdFinal;

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

        // La vinculación a NE no se edita aquí (ver guardar()): se valida
        // contra la que el pedido ya tenía, excluyendo sus propias líneas
        // actuales del cómputo de "ya comprometido" para no bloquearlo
        // contra sí mismo.
        $errorDisponibilidad = $this->_validarDisponibilidadNE(
            $productos,
            $pedido->consignacion_id,
            $pedido->consignacion_ids,
            $id
        );
        if ($errorDisponibilidad) {
            return redirect()->back()->withInput()->with('error', $errorDisponibilidad);
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
            'vendedor_id'    => $vendedorIdFinal,
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
    //  CORREGIR VINCULACIÓN CON NOTA(S) DE ENVÍO
    //  (NP ya facturada — herramienta de reparación para
    //  cuando se vinculó mal contra la(s) NE equivocada(s))
    // ──────────────────────────────────────────────

    public function editarVinculacionNE(int $id)
    {
        $chk = requerirPermiso('editar_pedidos');
        if ($chk !== true) return $chk;

        $headModel = new PedidoHeadModel();
        $detModel  = new PedidoDetalleModel();

        $pedido = $headModel->getConRelaciones($id);
        if (!$pedido) {
            return redirect()->to('/pedidos')->with('error', 'Nota no encontrada.');
        }

        if ($pedido->estado !== 'facturada' || empty($pedido->factura_id)) {
            return redirect()->to('/pedidos/' . $id)->with('error', 'Esta herramienta es solo para notas de pedido ya facturadas.');
        }

        $neIdsActuales = $this->_neIdsDePedido($pedido);
        if (empty($neIdsActuales)) {
            return redirect()->to('/pedidos/' . $id)->with('error', 'Esta nota no está vinculada a ninguna nota de envío.');
        }

        $detalles = $detModel->getPorPedido($id);

        $db          = \Config\Database::connect();
        $nesActuales = $db->table('consignaciones_head ch')
            ->select('ch.id, ch.numero, ch.estado, s.seller AS vendedor_nombre')
            ->join('sellers s', 's.id = ch.vendedor_id', 'left')
            ->whereIn('ch.id', $neIdsActuales)
            ->get()->getResult();

        return view('pedidos/editar_vinculacion', [
            'pedido'      => $pedido,
            'detalles'    => $detalles,
            'nesActuales' => $nesActuales,
        ]);
    }

    public function actualizarVinculacionNE(int $id)
    {
        $chk = requerirPermiso('editar_pedidos');
        if ($chk !== true) return $chk;

        $db      = \Config\Database::connect();
        $session = session();

        $headModel = new PedidoHeadModel();
        $detModel  = new PedidoDetalleModel();

        $pedido = $headModel->find($id);
        if (!$pedido || $pedido->estado !== 'facturada' || empty($pedido->factura_id)) {
            return redirect()->to('/pedidos/' . $id)->with('error', 'Esta herramienta es solo para notas de pedido ya facturadas.');
        }

        $neIdsViejos = $this->_neIdsDePedido($pedido);

        $consignacionIdPost  = (int)$this->request->getPost('consignacion_id') ?: null;
        $consignacionIdsPost = $this->_normalizarConsignacionIdsJson($this->request->getPost('consignacion_ids'));

        $neIdsNuevos = [];
        if ($consignacionIdPost) $neIdsNuevos[] = $consignacionIdPost;
        if ($consignacionIdsPost) {
            $decoded = json_decode($consignacionIdsPost, true);
            if (is_array($decoded)) {
                foreach ($decoded as $nid) {
                    $nid = (int)$nid;
                    if ($nid && !in_array($nid, $neIdsNuevos, true)) $neIdsNuevos[] = $nid;
                }
            }
        }

        if (empty($neIdsNuevos)) {
            return redirect()->back()->with('error', 'Debe quedar al menos una nota de envío vinculada.');
        }

        // Los productos llegan reconstruidos desde las NE seleccionadas (misma
        // lógica que al crear una NP), no como ajuste de las líneas viejas.
        $productosPost = $this->request->getPost('productos') ?? [];

        $productosParaValidar = [];
        foreach ($productosPost as $p) {
            if (empty($p['producto_id']) || (float)($p['cantidad'] ?? 0) <= 0) continue;
            $productosParaValidar[] = ['producto_id' => (int)$p['producto_id'], 'cantidad' => (float)$p['cantidad']];
        }

        if (empty($productosParaValidar)) {
            return redirect()->back()->withInput()->with('error', 'Debe incluir al menos un producto.');
        }

        $errorDisponibilidad = $this->_validarDisponibilidadNE($productosParaValidar, $consignacionIdPost, $consignacionIdsPost, $id);
        if ($errorDisponibilidad) {
            return redirect()->back()->withInput()->with('error', $errorDisponibilidad);
        }

        // NE que hay que reabrir: cualquiera que estuviera vinculada antes o
        // quede vinculada ahora (la vinculación cambió, así que su cierre ya
        // no refleja la realidad) y que esté actualmente cerrada.
        $neIdsAfectados = array_unique(array_merge($neIdsViejos, $neIdsNuevos));
        $nesParaReabrir = $db->table('consignaciones_head')
            ->select('id, numero')
            ->whereIn('id', $neIdsAfectados)
            ->where('estado', 'cerrada')
            ->get()->getResult();

        $db->transStart();

        // Se reemplazan por completo las líneas de productos: se borran las
        // viejas y se insertan las reconstruidas desde las NE seleccionadas.
        $db->table('pedidos_detalles')->where('pedido_id', $id)->delete();

        $subtotal = 0.0;
        foreach ($productosPost as $p) {
            if (empty($p['producto_id']) || (float)($p['cantidad'] ?? 0) <= 0) continue;
            $cant            = (float)$p['cantidad'];
            $precio          = (float)($p['precio_unitario'] ?? 0);
            $sub             = round($cant * $precio, 2);
            $consignacionLinea = (int)($p['consignacion_id'] ?? 0) ?: null;
            $detModel->insert([
                'pedido_id'       => $id,
                'producto_id'     => (int)$p['producto_id'],
                // Fijado explícitamente desde esta herramienta: de qué NE
                // sale esta línea en particular, sin dejarlo al cálculo FIFO.
                'consignacion_id' => $consignacionLinea,
                'cantidad'        => $cant,
                'precio_unitario' => $precio,
                'precio_minimo'   => 0,
                'subtotal'        => $sub,
            ]);
            $subtotal += $sub;
        }

        $iva   = ($pedido->tipo_documento === 'credito_fiscal') ? round($subtotal * 0.13, 2) : 0;
        $total = round($subtotal + $iva, 2);

        $headModel->update($id, [
            'consignacion_id'  => $consignacionIdPost,
            'consignacion_ids' => $consignacionIdsPost,
            'subtotal'         => $subtotal,
            'iva'              => $iva,
            'total'            => $total,
        ]);

        $userId   = $session->get('id');
        $userName = $session->get('user_name') ?? ('Usuario #' . $userId);

        foreach ($nesParaReabrir as $ne) {
            $db->table('consignaciones_head')->where('id', $ne->id)->update(['estado' => 'abierta']);

            $motivo = 'Se corrigió la vinculación de la Nota de Pedido ' . $pedido->numero . ' con esta nota de envío.';

            (new \App\Models\ConsignacionLogModel())->insert([
                'consignacion_id' => (int)$ne->id,
                'user_id'         => $userId,
                'user_nombre'     => $userName,
                'accion'          => 'Nota reabierta automáticamente',
                'detalle'         => $motivo,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            registrar_bitacora('Reabrir consignación', 'Consignaciones', 'La nota ' . $ne->numero . ' se reabrió automáticamente: ' . $motivo, $userId);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Error al corregir la vinculación.');
        }

        registrar_bitacora(
            'Corregir vinculación NE de nota de pedido',
            'Pedidos',
            'Se corrigió la vinculación con nota(s) de envío de la nota ' . $pedido->numero . '.',
            $userId
        );
        $this->log($id, 'Vinculación con NE corregida', implode(', ', array_map(fn($n) => $n->numero, $nesParaReabrir)) ?: null);

        $msg = 'Vinculación corregida correctamente.';
        if (!empty($nesParaReabrir)) {
            $numeros = implode(', ', array_map(fn($n) => $n->numero, $nesParaReabrir));
            $msg .= ' Se reabrió la nota ' . (count($nesParaReabrir) > 1 ? 's de envío ' : 'de envío ') . $numeros . ' para que se pueda(n) volver a cerrar.';
        }

        return redirect()->to('/pedidos/' . $id)->with('success', $msg);
    }

    /**
     * Normaliza el JSON de consignacion_ids a un arreglo de STRINGS antes de
     * guardarlo. Todo el resto del sistema lo busca con
     * JSON_CONTAINS(consignacion_ids, JSON_QUOTE(?)), que solo matchea
     * contra elementos guardados como string — un arreglo de números (p.
     * ej. lo que produce un JSON.stringify ingenuo de IDs numéricos en JS)
     * nunca matchearía, dejando esa NP invisible para esas búsquedas.
     */
    private function _normalizarConsignacionIdsJson(?string $json): ?string
    {
        if (!$json) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded) || empty($decoded)) {
            return null;
        }

        $ids = [];
        foreach ($decoded as $nid) {
            $nid = (int)$nid;
            if ($nid && !in_array((string)$nid, $ids, true)) {
                $ids[] = (string)$nid;
            }
        }

        return !empty($ids) ? json_encode($ids) : null;
    }

    private function _neIdsDePedido(object $pedido): array
    {
        $neIds = [];
        if (!empty($pedido->consignacion_id)) {
            $neIds[] = (int)$pedido->consignacion_id;
        }
        if (!empty($pedido->consignacion_ids)) {
            $decoded = json_decode($pedido->consignacion_ids, true);
            if (is_array($decoded)) {
                foreach ($decoded as $nid) {
                    $nid = (int)$nid;
                    if ($nid && !in_array($nid, $neIds, true)) $neIds[] = $nid;
                }
            }
        }
        return $neIds;
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

        $codActividad    = $this->request->getPost('cod_actividad') ?: null;
        $nrc             = trim($this->request->getPost('nrc') ?? '') ?: null;
        $tipoDocumento   = ClienteModel::normalizarTipoDocumento($this->request->getPost('tipo_documento')) ?: 'DUI';
        $numeroDocumento = trim($this->request->getPost('numero_documento') ?? '');
        $forzarDuplicado = (bool)$this->request->getPost('forzar_duplicado');

        // Mismo criterio tolerante a guiones/espacios que ClienteController::storeAjax().
        // No bloquea: una misma empresa puede tener varias sucursales como fichas
        // de cliente distintas, así que solo se avisa y se deja continuar si se confirma.
        if (!$forzarDuplicado) {
            $coincidencias = [];

            if ($numeroDocumento !== '') {
                $existente = $model->buscarPorDocumento($tipoDocumento, $numeroDocumento);
                if ($existente) {
                    $coincidencias[] = "documento: {$existente->nombre} (#{$existente->id})";
                }
            }

            if ($nrc !== null) {
                $existenteNrc = $model->buscarPorNRC($nrc);
                if ($existenteNrc) {
                    $coincidencias[] = "NRC: {$existenteNrc->nombre} (#{$existenteNrc->id})";
                }
            }

            if (!empty($coincidencias)) {
                return $this->response->setJSON([
                    'success'   => false,
                    'duplicado' => true,
                    'message'   => 'Ya existe un cliente registrado con el mismo ' . implode(' y ', $coincidencias)
                        . '. Si es una sucursal distinta de la misma empresa, puedes continuar de todos modos.',
                ]);
            }
        }

        $id = $model->insert([
            'tipo_documento'     => $tipoDocumento,
            'numero_documento'   => $numeroDocumento,
            'nrc'                => $nrc,
            'gran_contribuyente' => (int)(bool)$this->request->getPost('gran_contribuyente'),
            'exento_iva'         => (int)(bool)$this->request->getPost('exento_iva'),
            'cod_actividad'      => $codActividad,
            'desc_actividad'     => $codActividad
                ? (config('ActividadesEconomicas')->actividades[$codActividad] ?? $this->request->getPost('desc_actividad'))
                : null,
            'nombre'             => $nombre,
            'telefono'           => trim($this->request->getPost('telefono') ?? '') ?: null,
            'correo'             => trim($this->request->getPost('correo') ?? '') ?: null,
            'departamento'       => trim($this->request->getPost('departamento') ?? '') ?: null,
            'municipio'          => trim($this->request->getPost('municipio') ?? '') ?: null,
            'direccion'          => trim($this->request->getPost('direccion') ?? '') ?: null,
            'cuenta_contable_id' => $this->request->getPost('cuenta_contable_id') ?: null,
        ]);

        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo crear el cliente.',
                'errors'  => $model->errors(),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'cliente' => [
                'id'               => $id,
                'text'             => $nombre,
                'nrc'              => $nrc ?: '',
                'numero_documento' => trim($this->request->getPost('numero_documento') ?? ''),
                'tipo_documento'   => ClienteModel::normalizarTipoDocumento($this->request->getPost('tipo_documento')) ?: 'DUI',
            ],
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

    // ──────────────────────────────────────────────
    //  HELPER PRIVADO: no superar la cantidad disponible de la NE
    // ──────────────────────────────────────────────

    /**
     * Verifica que lo pedido por producto no supere lo realmente disponible
     * en la(s) NE vinculada(s) a esta NP, contando lo que ya está
     * comprometido en OTRAS Notas de Pedido activas (vivas o finalizadas,
     * ambas restan contra la NE — solo las anuladas no cuentan).
     * $excluirPedidoId evita que la propia NP (al editarla) se cuente
     * contra sí misma. Devuelve null si todo cuadra, o un mensaje de error.
     */
    private function _validarDisponibilidadNE(array $productos, ?int $consignacionId, ?string $consignacionIdsJson, int $excluirPedidoId = 0): ?string
    {
        $neIds = [];
        if ($consignacionId) $neIds[] = $consignacionId;
        if ($consignacionIdsJson) {
            $decoded = json_decode($consignacionIdsJson, true);
            if (is_array($decoded)) {
                foreach ($decoded as $nid) {
                    $nid = (int)$nid;
                    if ($nid && !in_array($nid, $neIds, true)) $neIds[] = $nid;
                }
            }
        }

        if (empty($neIds)) {
            return null; // NP sin NE vinculada: no aplica esta validación
        }

        $pedidoPorProducto = [];
        foreach ($productos as $p) {
            $pid  = (int)($p['producto_id'] ?? 0);
            $cant = (float)($p['cantidad'] ?? 0);
            if (!$pid || $cant <= 0) continue;
            $pedidoPorProducto[$pid] = ($pedidoPorProducto[$pid] ?? 0.0) + $cant;
        }

        if (empty($pedidoPorProducto)) {
            return null;
        }

        $db = \Config\Database::connect();

        // Capacidad total (suma de las líneas) de estas NE, por producto.
        $capacidadRows = $db->table('consignaciones_detalles')
            ->select('producto_id, SUM(cantidad) AS cantidad')
            ->whereIn('consignacion_id', $neIds)
            ->whereIn('producto_id', array_keys($pedidoPorProducto))
            ->groupBy('producto_id')
            ->get()->getResult();

        $capacidadPorProducto = [];
        foreach ($capacidadRows as $r) {
            $capacidadPorProducto[(int)$r->producto_id] = (float)$r->cantidad;
        }

        // Comprometido por otras NP en cada una de esas NE. La atribución
        // FIFO que ya hace _comprometidoPorProductoNE() por cada NE evita
        // contar dos veces la misma unidad entre NE distintas del conjunto.
        $consignacionesCtrl     = new ConsignacionesController();
        $comprometidoPorProducto = [];
        foreach ($neIds as $neId) {
            $comp = $consignacionesCtrl->_comprometidoPorProductoNE($neId, $excluirPedidoId);
            foreach ($comp as $pid => $cant) {
                $comprometidoPorProducto[$pid] = ($comprometidoPorProducto[$pid] ?? 0.0) + $cant;
            }
        }

        $nombresProductos = [];
        $idsNecesarios     = array_keys($pedidoPorProducto);
        $rows = $db->table('productos')->select('id, descripcion')->whereIn('id', $idsNecesarios)->get()->getResult();
        foreach ($rows as $r) {
            $nombresProductos[(int)$r->id] = $r->descripcion;
        }

        foreach ($pedidoPorProducto as $pid => $cantPedida) {
            $capacidad = $capacidadPorProducto[$pid] ?? 0.0;
            if ($capacidad <= 0.0001) {
                continue; // este producto no viene de las NE vinculadas, no se valida aquí
            }

            $comprometido = $comprometidoPorProducto[$pid] ?? 0.0;
            $disponible   = round($capacidad - $comprometido, 4);

            if ($cantPedida - $disponible > 0.0001) {
                $nombre = $nombresProductos[$pid] ?? "Producto #{$pid}";
                return 'El producto ' . $nombre . ' solo tiene ' . number_format(max(0, $disponible), 2) .
                    ' unidad(es) disponible(s) en la(s) nota(s) de envío vinculada(s) — ya hay ' .
                    number_format($comprometido, 2) . ' comprometida(s) en otra Nota de Pedido activa.';
            }
        }

        return null;
    }
}
