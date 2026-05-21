<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConsignacionHeadModel;
use App\Models\ConsignacionDetalleModel;
use App\Models\ConsignacionPrecioModel;
use App\Models\ConsignacionCierreModel;
use App\Models\ConsignacionCierreDetalleModel;
use App\Models\ConsignacionCierreFacturaModel;
use App\Models\ConsignacionCierreLoteModel;
use App\Models\ConsignacionLoteModel;
use App\Models\ConsignacionDetalleLoteModel;
use App\Models\ConsignacionLogModel;
use App\Models\PacienteModel;
use App\Models\SellerModel;

class ConsignacionesController extends BaseController
{
    // ─────────────────────────────────────────────
    //  LISTADO PRINCIPAL
    // ─────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $headModel     = new ConsignacionHeadModel();
        $session       = session();
        $db            = \Config\Database::connect();
        $puedeVerTodos = tienePermiso('ver_documentos_todos_vendedores');

        $sellerUsuario = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id, sellers.seller')
            ->get()->getRow();

        $filtros = [
            'vendedor_id'  => $this->request->getGet('vendedor_id'),
            'estado'       => $this->request->getGet('estado'),
            'fecha_inicio' => $this->request->getGet('fecha_inicio'),
            'fecha_fin'    => $this->request->getGet('fecha_fin'),
            'lote_estado'  => $this->request->getGet('lote_estado'),
            'origen'       => $this->request->getGet('origen'),
        ];

        // Sin permiso de ver todos: forzar el filtro al vendedor del usuario
        if (!$puedeVerTodos) {
            $filtros['vendedor_id'] = $sellerUsuario->id ?? 0;
        }

        $perPage = 15;

        $consignaciones = $headModel->listar($filtros)->paginate($perPage);
        $pager          = $headModel->pager;

        $sellerModel = new SellerModel();

        return view('consignaciones/index', [
            'consignaciones'  => $consignaciones,
            'pager'           => $pager,
            'filtros'         => $filtros,
            'vendedores'      => $sellerModel->orderBy('seller', 'ASC')->findAll(),
            'puede_ver_todos' => $puedeVerTodos,
            'seller_usuario'  => $sellerUsuario,
        ]);
    }

    // ─────────────────────────────────────────────
    //  FORMULARIO CREAR
    // ─────────────────────────────────────────────

    public function crear()
    {
        $chk = requerirPermiso('crear_consignaciones');
        if ($chk !== true) return $chk;

        $headModel   = new ConsignacionHeadModel();
        $sellerModel = new SellerModel();
        $session     = session();

        $db     = \Config\Database::connect();
        $seller = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id, sellers.seller')
            ->get()->getRow();

        return view('consignaciones/crear', [
            'numero_sugerido' => $headModel->siguienteNumero(),
            'vendedor_id'     => $seller->id     ?? null,
            'vendedor_nombre' => $seller->seller  ?? null,
            'vendedores'      => $seller ? [] : $sellerModel->orderBy('seller', 'ASC')->findAll(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GUARDAR NUEVA NOTA
    // ─────────────────────────────────────────────

    public function guardar()
    {
        $chk = requerirPermiso('crear_consignaciones');
        if ($chk !== true) return $chk;

        $db      = \Config\Database::connect();
        $session = session();

        $productos = $this->request->getPost('productos') ?? [];

        if (empty($productos)) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe agregar al menos un producto.');
        }

        $headModel = new ConsignacionHeadModel();
        $detModel  = new ConsignacionDetalleModel();

        $fecha = $this->request->getPost('fecha') ?: date('Y-m-d');
        $hora  = $this->request->getPost('hora')  ?: date('H:i');

        $subtotal = 0;
        foreach ($productos as $p) {
            $subtotal += (float)($p['subtotal'] ?? 0);
        }

        $db->transStart();

        $pacienteId = (int)$this->request->getPost('paciente_id') ?: null;
        $nombre     = null;
        if ($pacienteId) {
            $pRow   = (new PacienteModel())->select('nombre')->find($pacienteId);
            $nombre = $pRow ? $pRow->nombre : null;
        }

        $id = $headModel->insert([
            'numero'           => $this->request->getPost('numero'),
            'vendedor_id'      => $this->request->getPost('vendedor_id'),
            'nombre'           => $nombre,
            'paciente_id'      => $pacienteId,
            'concepto'         => $this->request->getPost('concepto'),
            'fecha'            => $fecha,
            'hora'             => $hora,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'subtotal'         => $subtotal,
            'doctor_id'        => $this->request->getPost('doctor_id') ?: null,
            'cliente_id'       => $this->request->getPost('cliente_id') ?: null,
            'observaciones'    => $this->request->getPost('observaciones'),
            'estado'           => 'abierta',
            'created_by'       => $session->get('id'),
        ]);

        foreach ($productos as $p) {
            if (empty($p['producto_id']) || empty($p['cantidad'])) continue;
            $cant   = (float)$p['cantidad'];
            $precio = (float)$p['precio_unitario'];
            $detModel->insert([
                'consignacion_id' => $id,
                'producto_id'     => (int)$p['producto_id'],
                'cantidad'        => $cant,
                'precio_unitario' => $precio,
                'subtotal'        => round($cant * $precio, 2),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Error al guardar la nota de envío.');
        }

        registrar_bitacora(
            'Crear nota de envío',
            'Consignaciones',
            'Se creó la nota de envío ' . $this->request->getPost('numero') . '.',
            $session->get('id')
        );

        $this->registrarLog($id, 'Nota creada');

        return redirect()->to('/consignaciones/' . $id)
            ->with('success', 'Nota de envío creada correctamente.');
    }

    // ─────────────────────────────────────────────
    //  FORMULARIO CREAR — STOCK DE EMERGENCIA
    // ─────────────────────────────────────────────

    public function crearEmergencia()
    {
        $chk = requerirPermiso('crear_consignacion_emergencia');
        if ($chk !== true) return $chk;

        $headModel   = new ConsignacionHeadModel();
        $sellerModel = new SellerModel();
        $session     = session();

        $db     = \Config\Database::connect();
        $seller = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id, sellers.seller')
            ->get()->getRow();

        return view('consignaciones/crear_emergencia', [
            'numero_sugerido' => $headModel->siguienteNumero(),
            'vendedor_id'     => $seller->id     ?? null,
            'vendedor_nombre' => $seller->seller  ?? null,
            'vendedores'      => $seller ? [] : $sellerModel->orderBy('seller', 'ASC')->findAll(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GUARDAR — STOCK DE EMERGENCIA
    // ─────────────────────────────────────────────

    public function guardarEmergencia()
    {
        $chk = requerirPermiso('crear_consignacion_emergencia');
        if ($chk !== true) return $chk;

        $db      = \Config\Database::connect();
        $session = session();
        $userId  = $session->get('id');
        $now     = date('Y-m-d H:i:s');

        $productos = $this->request->getPost('productos') ?? [];

        if (empty($productos)) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe agregar al menos un producto.');
        }

        // Validar que cada producto tenga lotes con suma exacta
        foreach ($productos as $idx => $p) {
            if (empty($p['producto_id']) || empty($p['cantidad'])) continue;

            $cantReq  = (float)$p['cantidad'];
            $lotes    = $p['lotes'] ?? [];

            if (empty($lotes)) {
                return redirect()->back()->withInput()
                    ->with('error', "El producto #" . ($idx + 1) . " no tiene lotes asignados.");
            }

            $cantAsignada = 0;
            foreach ($lotes as $l) {
                $cantAsignada += (float)($l['cantidad'] ?? 0);
            }

            if (abs($cantAsignada - $cantReq) > 0.001) {
                return redirect()->back()->withInput()
                    ->with('error', "Producto #" . ($idx + 1) . ": la suma de lotes ({$cantAsignada}) no coincide con la cantidad requerida ({$cantReq}).");
            }
        }

        $headModel    = new ConsignacionHeadModel();
        $detModel     = new ConsignacionDetalleModel();
        $loteModel    = new ConsignacionLoteModel();
        $detLoteModel = new ConsignacionDetalleLoteModel();

        $fecha = $this->request->getPost('fecha') ?: date('Y-m-d');
        $hora  = $this->request->getPost('hora')  ?: date('H:i');

        $subtotal = 0;
        foreach ($productos as $p) {
            $subtotal += (float)($p['subtotal'] ?? 0);
        }

        $db->transStart();

        $pacienteId = (int)$this->request->getPost('paciente_id') ?: null;
        $nombre     = null;
        if ($pacienteId) {
            $pRow   = (new PacienteModel())->select('nombre')->find($pacienteId);
            $nombre = $pRow ? $pRow->nombre : null;
        }

        $id = $headModel->insert([
            'numero'                => $this->request->getPost('numero'),
            'vendedor_id'           => $this->request->getPost('vendedor_id'),
            'nombre'                => $nombre,
            'paciente_id'           => $pacienteId,
            'concepto'              => $this->request->getPost('concepto'),
            'tipo_nota_id'          => $this->request->getPost('tipo_nota_id') ?: null,
            'fecha'                 => $fecha,
            'hora'                  => $hora,
            'fecha_generacion'      => $now,
            'subtotal'              => $subtotal,
            'doctor_id'             => $this->request->getPost('doctor_id') ?: null,
            'cliente_id'            => $this->request->getPost('cliente_id') ?: null,
            'observaciones'         => $this->request->getPost('observaciones'),
            'estado'                => 'abierta',
            'origen'                => 'emergencia',
            'created_by'            => $userId,
            'lotes_autorizados_por' => $userId,
            'lotes_autorizados_at'  => $now,
            'aprobacion_estado'     => 'aprobada',
            'aprobado_por'          => $userId,
            'aprobado_at'           => $now,
        ]);

        foreach ($productos as $p) {
            if (empty($p['producto_id']) || empty($p['cantidad'])) continue;

            $cant      = (float)$p['cantidad'];
            $precio    = (float)$p['precio_unitario'];
            $detalleId = $detModel->insert([
                'consignacion_id' => $id,
                'producto_id'     => (int)$p['producto_id'],
                'cantidad'        => $cant,
                'precio_unitario' => $precio,
                'subtotal'        => round($cant * $precio, 2),
            ]);

            $lotesArr = [];
            foreach ($p['lotes'] ?? [] as $l) {
                if (!empty($l['nuevo']) && $l['nuevo'] == '1') {
                    $nroLote = trim($l['numero_lote'] ?? '');
                    if ($nroLote === '') continue;
                    $loteId = $loteModel->insert([
                        'producto_id'       => (int)$p['producto_id'],
                        'numero_lote'       => $nroLote,
                        'fecha_vencimiento' => $l['vencimiento'] ?: null,
                        'manufactura'       => $l['manufactura'] ?: null,
                        'activo'            => 1,
                    ]);
                } else {
                    $loteId = (int)($l['lote_id'] ?? 0);
                }
                if (!$loteId) continue;
                $lotesArr[] = ['lote_id' => $loteId, 'cantidad' => (float)$l['cantidad']];
            }

            $detLoteModel->reemplazarPorDetalle($detalleId, $lotesArr);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Error al guardar la nota de emergencia.');
        }

        $numero = $this->request->getPost('numero');
        registrar_bitacora(
            'Crear NE Stock de Emergencia',
            'Consignaciones',
            "Se creó la NE emergencia {$numero}.",
            $userId
        );

        $this->registrarLog($id, 'NE Emergencia creada');

        return redirect()->to('/consignaciones/' . $id)
            ->with('success', 'NE Stock de Emergencia creada correctamente.');
    }

    // ─────────────────────────────────────────────
    //  DETALLE / VIEW
    // ─────────────────────────────────────────────

    public function show(int $id)
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $headModel  = new ConsignacionHeadModel();
        $detModel   = new ConsignacionDetalleModel();
        $cierreModel = new ConsignacionCierreModel();
        $cierreFacturaModel = new ConsignacionCierreFacturaModel();

        $consignacion = $headModel->getConVendedor($id);
        if (!$consignacion) {
            return redirect()->to('/consignaciones')->with('error', 'Nota no encontrada.');
        }

        $detalles = $detModel->getPorConsignacion($id);
        $cierre   = $cierreModel->getPorConsignacion($id);

        $cierreDetalleModel = new ConsignacionCierreDetalleModel();
        $facturasPorDetalle = [];
        $mapCierreDetalle = [];
        $lotesCierrePorDetalle = [];

        if ($cierre) {

            $cierreDetalles = $cierreDetalleModel
                ->where('cierre_id', $cierre->id)
                ->findAll();

            foreach ($cierreDetalles as $cd) {
                $mapCierreDetalle[$cd->detalle_id] = $cd;
            }

            foreach ($detalles as $d) {
                if (isset($mapCierreDetalle[$d->id])) {
                    $cd = $mapCierreDetalle[$d->id];

                    $facturasPorDetalle[$d->id] =
                        $cierreFacturaModel
                        ->select('facturas_head.numero_control')
                        ->join('facturas_head', 'facturas_head.id = consignaciones_cierres_facturas.factura_id', 'left')
                        ->where('consignaciones_cierres_facturas.detalle_id', $cd->id)
                        ->where('consignaciones_cierres_facturas.cierre_id', $cierre->id)
                        ->findAll();
                } else {
                    $facturasPorDetalle[$d->id] = [];
                }
            }

            $lotesCierre = (new ConsignacionCierreLoteModel())->getPorCierre((int)$cierre->id);
            foreach ($lotesCierre as $loteCierre) {
                $detalleId = (int)$loteCierre->detalle_id;
                $tipo      = $loteCierre->tipo;

                if (!isset($lotesCierrePorDetalle[$detalleId])) {
                    $lotesCierrePorDetalle[$detalleId] = [];
                }
                if (!isset($lotesCierrePorDetalle[$detalleId][$tipo])) {
                    $lotesCierrePorDetalle[$detalleId][$tipo] = [];
                }

                $lotesCierrePorDetalle[$detalleId][$tipo][] = $loteCierre;
            }
        } else {
            // 👇 si no hay cierre, todos vacíos
            foreach ($detalles as $d) {
                $facturasPorDetalle[$d->id] = [];
            }
        }

        // Lotes asignados por línea
        $detalleLoteModel = new ConsignacionDetalleLoteModel();
        $lotesPorDetalle  = [];
        foreach ($detalles as $d) {
            $lotesPorDetalle[$d->id] = $detalleLoteModel->getPorDetalle($d->id);
        }

        $logModel = new ConsignacionLogModel();
        $log      = $logModel->where('consignacion_id', $id)->orderBy('created_at', 'ASC')->findAll();

        $notasPedido = (new \App\Models\PedidoHeadModel())->getPorConsignacion($id);

        return view('consignaciones/detalle', [
            'consignacion'          => $consignacion,
            'detalles'              => $detalles,
            'cierre'                => $cierre,
            'facturasPorDetalle'    => $facturasPorDetalle,
            'mapCierreDetalle'      => $mapCierreDetalle,
            'lotesCierrePorDetalle' => $lotesCierrePorDetalle,
            'lotesPorDetalle'       => $lotesPorDetalle,
            'log'                   => $log,
            'notasPedido'           => $notasPedido,
        ]);
    }

    // ─────────────────────────────────────────────
    //  VISTA IMPRIMIBLE
    // ─────────────────────────────────────────────

    public function imprimir(int $id)
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $session = session();
        $user    = $session->get('id') ? $session->get() : null;

        $headModel = new ConsignacionHeadModel();
        $detModel  = new ConsignacionDetalleModel();

        $consignacion = $headModel->getConVendedor($id);
        if (!$consignacion) {
            return redirect()->to('/consignaciones')->with('error', 'Nota no encontrada.');
        }

        $detalles         = $detModel->getPorConsignacion($id);
        $detalleLoteModel = new ConsignacionDetalleLoteModel();
        $lotesPorDetalle  = [];
        foreach ($detalles as $d) {
            $lotesPorDetalle[$d->id] = $detalleLoteModel->getPorDetalle($d->id);
        }

        return view('consignaciones/print', [
            'consignacion'    => $consignacion,
            'detalles'        => $detalles,
            'lotesPorDetalle' => $lotesPorDetalle,
            'user'            => $user,
        ]);
    }

    // ─────────────────────────────────────────────
    //  FORMULARIO CERRAR
    // ─────────────────────────────────────────────

    public function cerrar(int $id)
    {
        $chk = requerirPermiso('cerrar_consignaciones');
        if ($chk !== true) return $chk;

        $headModel = new ConsignacionHeadModel();
        $detModel  = new ConsignacionDetalleModel();

        $consignacion = $headModel->getConVendedor($id);

        if (!$consignacion || $consignacion->estado !== 'abierta') {
            return redirect()->to('/consignaciones/' . $id)
                ->with('error', 'Esta nota no puede cerrarse.');
        }

        $detalles = $detModel->getPorConsignacion($id);

        $detalleLoteModel = new ConsignacionDetalleLoteModel();
        $lotesPorDetalle  = [];
        foreach ($detalles as $d) {
            $lotesPorDetalle[$d->id] = $detalleLoteModel->getPorDetalle($d->id);
        }

        // Sugerencias de factura: NPs asociadas que ya tienen factura emitida
        $db = \Config\Database::connect();
        $rows = $db->query(
            "SELECT ph.id AS np_id, ph.numero AS np_numero, ph.factura_id,
                    fh.numero_control AS factura_numero,
                    fh.fecha_emision, fh.total_pagar,
                    SUBSTRING_INDEX(fh.numero_control, '-', -1) AS correlativo,
                    c.nombre AS cliente_nombre,
                    pd.producto_id, pd.cantidad AS np_cantidad
             FROM pedidos_head ph
             INNER JOIN facturas_head fh ON fh.id = ph.factura_id
             INNER JOIN pedidos_detalles pd ON pd.pedido_id = ph.id
             LEFT JOIN clientes c ON c.id = fh.receptor_id
             WHERE ph.anulada = 0
               AND ph.factura_id IS NOT NULL
               AND (ph.consignacion_id = ?
                    OR (ph.consignacion_ids IS NOT NULL
                        AND JSON_CONTAINS(ph.consignacion_ids, ?)))",
            [$id, (string)$id]
        )->getResultObject();

        $sugerenciasPorProducto = [];
        foreach ($rows as $row) {
            $pid  = $row->producto_id;
            $fid  = $row->factura_id;
            $corr = substr($row->correlativo, -6);
            $text = "#{$corr}";
            if (!empty($row->cliente_nombre)) $text .= ' · ' . $row->cliente_nombre;
            $text .= ' · ' . date('d/m/Y', strtotime($row->fecha_emision));
            $text .= ' · $' . number_format($row->total_pagar, 2);
            if (!isset($sugerenciasPorProducto[$pid][$fid])) {
                $sugerenciasPorProducto[$pid][$fid] = [
                    'factura_id'     => $row->factura_id,
                    'factura_texto'  => $text,
                    'np_numero'      => $row->np_numero,
                    'np_id'          => $row->np_id,
                    'np_cantidad'    => (float)$row->np_cantidad,
                ];
            }
        }
        foreach ($sugerenciasPorProducto as &$arr) {
            $arr = array_values($arr);
        }
        unset($arr);

        return view('consignaciones/cerrar', [
            'aprobada'               => ($consignacion->aprobacion_estado ?? '') === 'aprobada',
            'consignacion'           => $consignacion,
            'detalles'               => $detalles,
            'lotesPorDetalle'        => $lotesPorDetalle,
            'sugerenciasPorProducto' => $sugerenciasPorProducto,
        ]);
    }

    // ─────────────────────────────────────────────
    //  PROCESAR CIERRE
    // ─────────────────────────────────────────────

    public function procesarCierre(int $id)
    {
        $chk = requerirPermiso('cerrar_consignaciones');
        if ($chk !== true) return $chk;

        $db      = \Config\Database::connect();
        $session = session();

        $headModel         = new ConsignacionHeadModel();
        $detModel          = new ConsignacionDetalleModel();
        $cierreModel       = new ConsignacionCierreModel();
        $cierreDetModel    = new ConsignacionCierreDetalleModel();
        $cierreFactModel   = new ConsignacionCierreFacturaModel();
        $cierreLoteModel   = new ConsignacionCierreLoteModel();

        $consignacion = $headModel->find($id);
        if (!$consignacion || $consignacion->estado !== 'abierta') {
            return redirect()->to('/consignaciones/' . $id)
                ->with('error', 'Esta nota no puede cerrarse.');
        }

        $detalles    = $detModel->getPorConsignacion($id);
        $lineas      = $this->request->getPost('lineas') ?? [];
        $obsGenerales = $this->request->getPost('observaciones_cierre');
        $distribucionesLotes = [];

        foreach ($detalles as $det) {
            $lin = $lineas[$det->id] ?? [];

            $cantFact  = (float)($lin['cantidad_facturada'] ?? 0);
            $cantDev   = (float)($lin['cantidad_devuelta'] ?? 0);
            $cantStock = (float)($lin['cantidad_stock_vendedor'] ?? 0);

            if ($cantFact < 0 || $cantDev < 0 || $cantStock < 0) {
                return redirect()->back()->withInput()
                    ->with('error', 'No se permiten cantidades negativas en el producto ' . $det->producto_nombre . '.');
            }

            $suma = $cantFact + $cantDev + $cantStock;

            if (abs($suma - (float)$det->cantidad) > 0.01) {
                return redirect()->back()->withInput()
                    ->with('error', 'La distribución del producto ' . $det->producto_nombre . ' no coincide con la cantidad original.');
            }
        }

        foreach ($detalles as $det) {
            $lin = $lineas[$det->id] ?? [];

            $cantFact  = (float)($lin['cantidad_facturada'] ?? 0);
            $cantStock = (float)($lin['cantidad_stock_vendedor'] ?? 0);
            $lotesOriginales = (new ConsignacionDetalleLoteModel())->getPorDetalle($det->id);

            $distFacturado = $this->resolverDistribucionLotesCierre($det, $lin, 'lotes_facturados', $cantFact, $lotesOriginales, 'facturada');
            if (!$distFacturado['success']) {
                return redirect()->back()->withInput()->with('error', $distFacturado['message']);
            }

            $distStock = $this->resolverDistribucionLotesCierre($det, $lin, 'lotes_stock', $cantStock, $lotesOriginales, 'en stock del vendedor');
            if (!$distStock['success']) {
                return redirect()->back()->withInput()->with('error', $distStock['message']);
            }

            $consumoLotes = $this->validarConsumoLotesCierre($lotesOriginales, $distFacturado['lotes'], $distStock['lotes'], $det->producto_nombre);
            if (!$consumoLotes['success']) {
                return redirect()->back()->withInput()->with('error', $consumoLotes['message']);
            }

            $distribucionesLotes[$det->id] = [
                'facturado'      => $distFacturado['lotes'],
                'stock_vendedor' => $distStock['lotes'],
            ];
        }

        $db->transStart();

        $nuevoId = null;

        $hayStockVendedor = false;
        foreach ($lineas as $lin) {
            if ((float)($lin['cantidad_stock_vendedor'] ?? 0) > 0) {
                $hayStockVendedor = true;
                break;
            }
        }

        // Si hay stock que queda con el vendedor → crear nueva nota
        if ($hayStockVendedor) {
            $nuevoNumero = $headModel->siguienteNumero();
            $nuevoSubtotal = 0;

            foreach ($detalles as $det) {
                $lin = $lineas[$det->id] ?? [];
                $cantStock = (float)($lin['cantidad_stock_vendedor'] ?? 0);
                if ($cantStock > 0) {
                    $nuevoSubtotal += $cantStock * $det->precio_unitario;
                }
            }

            $nuevoId = $headModel->insert([
                'numero'           => $nuevoNumero,
                'vendedor_id'      => $consignacion->vendedor_id,
                'nombre'           => $consignacion->nombre,
                'concepto'         => 'Traslado desde ' . $consignacion->numero,
                'fecha'            => date('Y-m-d'),
                'hora'             => date('H:i'),
                'fecha_generacion' => date('Y-m-d H:i:s'),
                'subtotal'         => round($nuevoSubtotal, 2),
                'estado'           => 'abierta',
                'created_by'       => $session->get('id'),
            ]);

            foreach ($detalles as $det) {
                $lin = $lineas[$det->id] ?? [];
                $cantStock = (float)($lin['cantidad_stock_vendedor'] ?? 0);
                if ($cantStock > 0) {
                    $nuevoDetalleId = $detModel->insert([
                        'consignacion_id' => $nuevoId,
                        'producto_id'     => $det->producto_id,
                        'cantidad'        => $cantStock,
                        'precio_unitario' => $det->precio_unitario,
                        'subtotal'        => round($cantStock * $det->precio_unitario, 2),
                    ]);

                    $lotesOriginales = (new ConsignacionDetalleLoteModel())->getPorDetalle($det->id);
                    $lotesParaNuevo  = [];

                    if (!empty($lotesOriginales)) {

                        // Caso 1: traslado completo del producto
                        if (abs($cantStock - (float)$det->cantidad) < 0.001) {
                            foreach ($lotesOriginales as $l) {
                                $lotesParaNuevo[] = [
                                    'lote_id'  => $l->lote_id,
                                    'cantidad' => (float)$l->cantidad,
                                ];
                            }
                        }

                        // Caso 2: solo tenía un lote, traslado parcial automático
                        elseif (count($lotesOriginales) === 1) {
                            $lotesParaNuevo[] = [
                                'lote_id'  => $lotesOriginales[0]->lote_id,
                                'cantidad' => $cantStock,
                            ];
                        }

                        // Caso 3: varios lotes y traslado parcial: usar selección del usuario
                        else {
                            $lotesPost = $lin['lotes_stock'] ?? [];
                            $totalLotesPost = 0;

                            $lotesPermitidos = [];
                            foreach ($lotesOriginales as $lo) {
                                $lotesPermitidos[(int)$lo->lote_id] = (float)$lo->cantidad;
                            }

                            foreach ($lotesPost as $loteId => $cantidadLote) {
                                $loteId = (int)$loteId;
                                $cantidadLote = (float)$cantidadLote;

                                if (!isset($lotesPermitidos[$loteId])) {
                                    $db->transRollback();
                                    return redirect()->back()->withInput()
                                        ->with('error', 'Se recibió un lote inválido para el producto ' . $det->producto_nombre . '.');
                                }

                                if ($cantidadLote - $lotesPermitidos[$loteId] > 0.001) {
                                    $db->transRollback();
                                    return redirect()->back()->withInput()
                                        ->with('error', 'La cantidad del lote supera la cantidad original en el producto ' . $det->producto_nombre . '.');
                                }

                                if ($cantidadLote <= 0) {
                                    continue;
                                }

                                $totalLotesPost += $cantidadLote;

                                $lotesParaNuevo[] = [
                                    'lote_id'  => (int)$loteId,
                                    'cantidad' => $cantidadLote,
                                ];
                            }

                            if (abs($totalLotesPost - $cantStock) > 0.001) {
                                $db->transRollback();
                                return redirect()->back()
                                    ->withInput()
                                    ->with('error', 'La distribución de lotes no coincide con el stock vendedor del producto ' . $det->producto_nombre . '.');
                            }
                        }

                        if (!empty($lotesParaNuevo)) {
                            (new ConsignacionDetalleLoteModel())->reemplazarPorDetalle($nuevoDetalleId, $lotesParaNuevo);
                        }
                    }
                }
            }
        }

        // Crear registro de cierre
        $cierreId = $cierreModel->insert([
            'consignacion_id'       => $id,
            'nueva_consignacion_id' => $nuevoId,
            'observaciones'         => $obsGenerales,
            'created_by'            => $session->get('id'),
        ]);

        // Manejar fotos devolucion
        $fotos = $this->request->getFiles();

        foreach ($detalles as $det) {
            $lin = $lineas[$det->id] ?? [];

            $cantFact  = (float)($lin['cantidad_facturada']     ?? 0);
            $cantDev   = (float)($lin['cantidad_devuelta']       ?? 0);
            $cantStock = (float)($lin['cantidad_stock_vendedor'] ?? 0);

            $fotoNombre = null;
            $fileKey    = 'foto_' . $det->id;

            if (isset($fotos[$fileKey]) && $fotos[$fileKey]->isValid() && !$fotos[$fileKey]->hasMoved()) {
                $fotoNombre = $fotos[$fileKey]->getRandomName();
                $fotos[$fileKey]->move(WRITEPATH . 'uploads/consignaciones/', $fotoNombre);
            }

            $cierreDetId = $cierreDetModel->insert([
                'cierre_id'               => $cierreId,
                'detalle_id'              => $det->id,
                'producto_id'             => $det->producto_id,
                'cantidad_facturada'      => $cantFact,
                'cantidad_devuelta'       => $cantDev,
                'cantidad_stock_vendedor' => $cantStock,
                'doc_devolucion'          => $lin['doc_devolucion'] ?? null,
                'foto_devolucion'         => $fotoNombre,
                'comentario_devolucion'   => $lin['comentario_devolucion'] ?? null,
            ]);

            // Facturas asociadas a esta línea
            $cierreLoteModel->registrarDistribucion(
                $cierreId,
                $cierreDetId,
                $det->id,
                $det->producto_id,
                'facturado',
                $distribucionesLotes[$det->id]['facturado'] ?? []
            );

            $cierreLoteModel->registrarDistribucion(
                $cierreId,
                $cierreDetId,
                $det->id,
                $det->producto_id,
                'stock_vendedor',
                $distribucionesLotes[$det->id]['stock_vendedor'] ?? []
            );

            $facturasLinea = $lin['facturas'] ?? [];
            foreach ($facturasLinea as $facturaId) {
                if (!empty($facturaId)) {
                    $cierreFactModel->insert([
                        'cierre_id'  => $cierreId,
                        'detalle_id' => $cierreDetId,
                        'factura_id' => (int)$facturaId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // Marcar nota original como cerrada
        $headModel->update($id, ['estado' => 'cerrada']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()
                ->with('error', 'Error al procesar el cierre.');
        }

        registrar_bitacora(
            'Cerrar nota de envío',
            'Consignaciones',
            'Se cerró la nota ' . $consignacion->numero . ($nuevoId ? ' y se generó traslado.' : '.'),
            $session->get('id')
        );
        $this->registrarLog($id, 'Nota cerrada', $nuevoId ? 'Se generó nota de traslado.' : null);

        $msg = 'Nota cerrada correctamente.';
        if ($nuevoId) {
            $nuevaNota = $db->table('consignaciones_head')
                ->where('id', $nuevoId)
                ->get()
                ->getRow();

            $msg .= ' Se generó la nueva nota ' . ($nuevaNota->numero ?? '') . ' con el stock restante.';
        }

        return redirect()->to('/consignaciones/' . $id)->with('success', $msg);
    }

    // ─────────────────────────────────────────────
    //  ANULAR
    // ─────────────────────────────────────────────

    public function anular(int $id)
    {
        $chk = requerirPermiso('anular_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $session   = session();
        $headModel = new ConsignacionHeadModel();
        $nota      = $headModel->find($id);

        if (!$nota) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);
        }

        if ($nota->estado === 'cerrada') {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede anular una nota cerrada.']);
        }

        if ($nota->anulada) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ya estaba anulada.']);
        }

        $headModel->update($id, [
            'estado'          => 'anulada',
            'anulada'         => 1,
            'anulada_por'     => $session->get('id'),
            'fecha_anulacion' => date('Y-m-d H:i:s'),
        ]);

        registrar_bitacora(
            'Anular nota de envío',
            'Consignaciones',
            'Se anuló la nota ' . $nota->numero . '.',
            $session->get('id')
        );
        $this->registrarLog($id, 'Nota anulada');

        return $this->response->setJSON(['success' => true, 'message' => 'Nota anulada correctamente.']);
    }

    // ─────────────────────────────────────────────
    //  EDITAR NOTA (sólo cuando está abierta)
    // ─────────────────────────────────────────────

    public function editar(int $id)
    {
        $chk = requerirPermiso('crear_consignaciones');
        if ($chk !== true) return $chk;

        $headModel   = new ConsignacionHeadModel();
        $detModel    = new ConsignacionDetalleModel();
        $sellerModel = new SellerModel();

        $consignacion = $headModel->getConVendedor($id);

        if (!$consignacion || $consignacion->estado !== 'abierta') {
            return redirect()->to('/consignaciones/' . $id)
                ->with('error', 'Solo se pueden editar notas con estado Abierta.');
        }

        $detalles = $detModel->getPorConsignacion($id);

        $db = \Config\Database::connect();

        $doctor = null;
        if (!empty($consignacion->doctor_id)) {
            $doctor = $db->table('doctores')
                ->where('id', $consignacion->doctor_id)
                ->get()
                ->getRow();
        }

        $cliente = null;
        if (!empty($consignacion->cliente_id)) {
            $cliente = $db->table('clientes')
                ->where('id', $consignacion->cliente_id)
                ->get()
                ->getRow();
        }

        $paciente = null;
        if (!empty($consignacion->paciente_id)) {
            $paciente = (new PacienteModel())->find($consignacion->paciente_id);
        }

        // Cargar nombre de tipo de nota para mostrar en el select si aplica
        if (!empty($consignacion->tipo_nota_id)) {
            $tipo = $db->table('tipo_notas')->select('nombre')->where('id', $consignacion->tipo_nota_id)->get()->getRow();
            $consignacion->tipo_nota_nombre = $tipo ? $tipo->nombre : null;
        }

        $detalleLoteModel = new ConsignacionDetalleLoteModel();
        $lotesPorDetalle  = [];

        foreach ($detalles as $d) {
            $lotesPorDetalle[$d->id] = $detalleLoteModel->getPorDetalle($d->id);
        }

        return view('consignaciones/editar', [
            'consignacion'    => $consignacion,
            'detalles'        => $detalles,
            'vendedores'      => $sellerModel->orderBy('seller', 'ASC')->findAll(),
            'doctor'          => $doctor,
            'cliente'         => $cliente,
            'paciente'        => $paciente,
            'lotesPorDetalle' => $lotesPorDetalle,
        ]);
    }

    public function actualizar(int $id)
    {
        $chk = requerirPermiso('crear_consignaciones');
        if ($chk !== true) return $chk;

        $db      = \Config\Database::connect();
        $session = session();

        $headModel        = new ConsignacionHeadModel();
        $detModel         = new ConsignacionDetalleModel();
        $detalleLoteModel = new ConsignacionDetalleLoteModel();

        $consignacion = $headModel->find($id);
        if (!$consignacion || $consignacion->estado !== 'abierta') {
            return redirect()->to('/consignaciones/' . $id)
                ->with('error', 'No se puede editar esta nota.');
        }

        $productos = $this->request->getPost('productos') ?? [];
        if (empty($productos)) {
            return redirect()->back()->withInput()
                ->with('error', 'Debe agregar al menos un producto.');
        }

        // Mapa de detalles existentes: id => objeto
        $existingDetalles = $detModel->getPorConsignacion($id);
        $existingMap = [];
        foreach ($existingDetalles as $d) {
            $existingMap[(int)$d->id] = $d;
        }

        $subtotal            = 0;
        $submittedDetalleIds = [];

        $db->transStart();

        foreach ($productos as $p) {
            if (empty($p['producto_id']) || empty($p['cantidad'])) continue;

            $cant      = (float)$p['cantidad'];
            $precio    = (float)$p['precio_unitario'];
            $sub       = round($cant * $precio, 2);
            $detalleId = (int)($p['detalle_id'] ?? 0);
            $subtotal += $sub;

            if ($detalleId && isset($existingMap[$detalleId])) {
                $existing    = $existingMap[$detalleId];
                $qtyChanged  = abs($cant - (float)$existing->cantidad) > 0.001;
                $prodChanged = (int)$p['producto_id'] !== (int)$existing->producto_id;

                $detModel->update($detalleId, [
                    'producto_id'     => (int)$p['producto_id'],
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'subtotal'        => $sub,
                ]);

                // Limpiar lotes si cantidad o producto cambiaron
                if ($qtyChanged || $prodChanged) {
                    $detalleLoteModel->reemplazarPorDetalle($detalleId, []);
                }

                $submittedDetalleIds[] = $detalleId;
            } else {
                // Producto nuevo agregado en el edit
                $newId = (int)$detModel->insert([
                    'consignacion_id' => $id,
                    'producto_id'     => (int)$p['producto_id'],
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'subtotal'        => $sub,
                ]);
                $submittedDetalleIds[] = $newId;
            }
        }

        // Eliminar filas borradas por el usuario (y sus lotes)
        foreach (array_keys($existingMap) as $existingId) {
            if (!in_array($existingId, $submittedDetalleIds)) {
                $detalleLoteModel->reemplazarPorDetalle($existingId, []);
                $detModel->delete($existingId);
            }
        }

        $pacienteId = (int)$this->request->getPost('paciente_id') ?: null;
        $nombre     = null;
        if ($pacienteId) {
            $pRow   = (new PacienteModel())->select('nombre')->find($pacienteId);
            $nombre = $pRow ? $pRow->nombre : null;
        }

        $headModel->update($id, [
            'vendedor_id'   => $this->request->getPost('vendedor_id'),
            'nombre'        => $nombre,
            'paciente_id'   => $pacienteId,
            'doctor_id'     => $this->request->getPost('doctor_id') ?: null,
            'cliente_id'    => $this->request->getPost('cliente_id') ?: null,
            'tipo_nota_id'  => $this->request->getPost('tipo_nota_id') ?: null,
            'concepto'      => $this->request->getPost('concepto'),
            'fecha'         => $this->request->getPost('fecha') ?: date('Y-m-d'),
            'hora'          => $this->request->getPost('hora') ?: date('H:i'),
            'subtotal'      => $subtotal,
            'observaciones' => $this->request->getPost('observaciones'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('error', 'Error al actualizar la nota de envío.');
        }

        registrar_bitacora(
            'Editar nota de envío',
            'Consignaciones',
            'Se editó la nota de envío ' . $consignacion->numero . '.',
            $session->get('id')
        );

        $this->registrarLog($id, 'Nota editada');

        return redirect()->to('/consignaciones/' . $id)
            ->with('success', 'Nota de envío actualizada correctamente.');
    }

    // ─────────────────────────────────────────────
    //  APROBACIÓN
    // ─────────────────────────────────────────────

    public function aprobar(int $id)
    {
        $chk = requerirPermiso('aprobar_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $session   = session();
        $headModel = new ConsignacionHeadModel();
        $nota      = $headModel->find($id);

        if (!$nota) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);
        }
        if ($nota->estado !== 'abierta') {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se pueden aprobar notas abiertas.']);
        }

        $headModel->update($id, [
            'aprobacion_estado' => 'aprobada',
            'aprobado_por'      => $session->get('id'),
            'aprobado_at'       => date('Y-m-d H:i:s'),
            'rechazo_motivo'    => null,
        ]);

        registrar_bitacora('Aprobar consignación', 'Consignaciones', 'Aprobó nota ' . $nota->numero . '.', $session->get('id'));
        $this->registrarLog($id, 'Nota aprobada');

        return $this->response->setJSON(['success' => true, 'message' => 'Nota aprobada.']);
    }

    public function rechazar(int $id)
    {
        $chk = requerirPermiso('aprobar_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $session = session();
        $body    = $this->request->getJSON(true) ?? [];
        $motivo  = trim($body['motivo'] ?? '');

        if ($motivo === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Debe indicar un motivo de rechazo.']);
        }

        $headModel = new ConsignacionHeadModel();
        $nota      = $headModel->find($id);

        if (!$nota) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);
        }

        $headModel->update($id, [
            'aprobacion_estado' => 'rechazada',
            'aprobado_por'      => $session->get('id'),
            'aprobado_at'       => date('Y-m-d H:i:s'),
            'rechazo_motivo'    => $motivo,
        ]);

        registrar_bitacora('Rechazar consignación', 'Consignaciones', 'Rechazó nota ' . $nota->numero . '. Motivo: ' . $motivo, $session->get('id'));
        $this->registrarLog($id, 'Nota rechazada', 'Motivo: ' . $motivo);

        return $this->response->setJSON(['success' => true, 'message' => 'Nota rechazada.']);
    }

    // ─────────────────────────────────────────────
    //  LOTES: CATÁLOGO
    // ─────────────────────────────────────────────

    public function lotes()
    {
        $chk = requerirPermiso('gestionar_lotes_consignaciones');
        if ($chk !== true) return $chk;

        $loteModel = new ConsignacionLoteModel();
        $filtros   = [
            'producto_id' => $this->request->getGet('producto_id'),
            'activo'      => $this->request->getGet('activo') ?? '',
        ];

        $lotes = $loteModel->listarConProducto($filtros)->paginate(20);
        $pager = $loteModel->pager;

        return view('consignaciones/lotes/index', [
            'lotes'   => $lotes,
            'pager'   => $pager,
            'filtros' => $filtros,
        ]);
    }

    public function guardarLote()
    {
        $chk = requerirPermiso('gestionar_lotes_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $loteModel  = new ConsignacionLoteModel();
        $productoId = (int)$this->request->getPost('producto_id');
        $numero     = trim($this->request->getPost('numero_lote') ?? '');
        $editId     = (int)$this->request->getPost('id');

        if (!$productoId || $numero === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Producto y número de lote son obligatorios.']);
        }

        $data = [
            'producto_id'       => $productoId,
            'numero_lote'       => $numero,
            'fecha_vencimiento' => $this->request->getPost('fecha_vencimiento') ?: null,
            'manufactura'       => $this->request->getPost('manufactura') ?: null,
            'descripcion'       => $this->request->getPost('descripcion') ?: null,
            'activo'            => 1,
        ];

        if ($editId) {
            $loteModel->update($editId, $data);
        } else {
            $loteModel->insert($data);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function eliminarLote(int $id)
    {
        $chk = requerirPermiso('gestionar_lotes_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        (new ConsignacionLoteModel())->update($id, ['activo' => 0]);

        return $this->response->setJSON(['success' => true]);
    }

    // AJAX: lotes disponibles para un producto
    public function lotesPorProducto()
    {
        $productoId = (int)$this->request->getGet('producto_id');
        $q          = trim((string)$this->request->getGet('q'));
        $lotes      = (new ConsignacionLoteModel())->getPorProducto($productoId, $q);

        $results = array_map(function ($l) {
            $text = $l->numero_lote;

            if (!empty($l->fecha_vencimiento)) {
                $text .= ' (vence: ' . $l->fecha_vencimiento . ')';
            }

            if (!empty($l->manufactura)) {
                $text .= ' (manuf: ' . $l->manufactura . ')';
            }

            return [
                'id'     => $l->id,
                'text'   => $text,
                'numero' => $l->numero_lote,
            ];
        }, $lotes);

        return $this->response->setJSON(['results' => $results]);
    }

    // ─────────────────────────────────────────────
    //  LOTES POR DETALLE (asignación)
    // ─────────────────────────────────────────────

    public function detalleLotes(int $detalleId)
    {
        $lotes = (new ConsignacionDetalleLoteModel())->getPorDetalle($detalleId);
        return $this->response->setJSON(['success' => true, 'lotes' => $lotes]);
    }

    public function guardarDetalleLotes(int $detalleId)
    {
        $chk = requerirPermiso('gestionar_lotes_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso para gestionar lotes.']);
        }

        $db = \Config\Database::connect();

        $detRow = $db->table('consignaciones_detalles cd')
            ->select('cd.cantidad, ch.estado, ch.lotes_autorizados_por, ch.id AS consignacion_id')
            ->join('consignaciones_head ch', 'ch.id = cd.consignacion_id')
            ->where('cd.id', $detalleId)
            ->get()
            ->getRow();

        if (!$detRow || $detRow->estado !== 'abierta') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo se pueden asignar lotes a notas abiertas.'
            ]);
        }

        if (empty($detRow->lotes_autorizados_por)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Los lotes de esta nota aún no han sido autorizados para edición. Solicite autorización.'
            ]);
        }

        $body  = $this->request->getJSON(true) ?? [];
        $lotes = $body['lotes'] ?? [];

        $totalAsignado = 0;

        foreach ($lotes as $lote) {
            $cantidad = (float)($lote['cantidad'] ?? 0);

            if (empty($lote['lote_id']) || $cantidad <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Todos los lotes deben tener lote y cantidad válida.'
                ]);
            }

            $totalAsignado += $cantidad;
        }

        $cantidadDetalle = (float)$detRow->cantidad;

        if (abs($totalAsignado - $cantidadDetalle) > 0.001) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La cantidad asignada en lotes debe ser exactamente ' .
                    number_format($cantidadDetalle, 2) .
                    '. Actualmente asignó ' .
                    number_format($totalAsignado, 2) . '.'
            ]);
        }

        (new ConsignacionDetalleLoteModel())->reemplazarPorDetalle($detalleId, $lotes);

        // Construir detalle legible con los números de lote y cantidades asignadas
        $parts = [];
        foreach ($lotes as $l) {
            $num = $l['numero_lote'] ?? ($l['lote_id'] ?? 'Lote');
            $cant = number_format((float)($l['cantidad'] ?? 0), 2);
            $parts[] = "$num: $cant";
        }
        $detalleText = !empty($parts) ? implode('; ', $parts) : ('Detalle #' . $detalleId);

        $this->registrarLog((int)$detRow->consignacion_id, 'Lotes actualizados', $detalleText);

        return $this->response->setJSON([
            'success'        => true,
            'message'        => 'Lotes guardados correctamente.',
            'detalle_id'     => $detalleId,
            'lotes'          => $lotes,
            'total_lotes'    => count($lotes),
            'total_asignado' => $totalAsignado,
        ]);
    }

    // ─────────────────────────────────────────────
    //  PRECIOS: LISTADO
    // ─────────────────────────────────────────────

    public function precios()
    {
        $chk = requerirPermiso('ver_precios_consignaciones');
        if ($chk !== true) return $chk;

        $precioModel = new ConsignacionPrecioModel();
        $sellerModel = new SellerModel();

        $filtros = ['vendedor_id' => $this->request->getGet('vendedor_id')];

        $precios  = $precioModel->listarConNombres($filtros)->findAll();
        $vendedores = $sellerModel->orderBy('seller', 'ASC')->findAll();

        return view('consignaciones/precios/index', [
            'precios'    => $precios,
            'vendedores' => $vendedores,
            'filtros'    => $filtros,
        ]);
    }

    // ─────────────────────────────────────────────
    //  PRECIOS: GUARDAR (AJAX)
    // ─────────────────────────────────────────────

    public function guardarPrecio()
    {
        $chk = requerirPermiso('gestionar_precios_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $precioModel = new ConsignacionPrecioModel();

        $vendedorId  = (int)$this->request->getPost('vendedor_id');
        $productoId  = (int)$this->request->getPost('producto_id');
        $clienteId   = $this->request->getPost('cliente_id') ?: null;
        $precio      = (float)$this->request->getPost('precio');
        $editId      = (int)$this->request->getPost('id');

        if (!$vendedorId || !$productoId || $precio <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos.']);
        }

        if ($editId) {
            $precioModel->update($editId, [
                'vendedor_id' => $vendedorId,
                'cliente_id'  => $clienteId,
                'producto_id' => $productoId,
                'precio'      => $precio,
            ]);
        } else {
            $precioModel->insert([
                'vendedor_id' => $vendedorId,
                'cliente_id'  => $clienteId,
                'producto_id' => $productoId,
                'precio'      => $precio,
                'activo'      => 1,
            ]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PRECIOS: ELIMINAR (AJAX)
    // ─────────────────────────────────────────────

    public function eliminarPrecio(int $id)
    {
        $chk = requerirPermiso('gestionar_precios_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $precioModel = new ConsignacionPrecioModel();
        $precioModel->delete($id);

        return $this->response->setJSON(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  AJAX: precio recomendado
    // ─────────────────────────────────────────────

    public function getPrecioAjax()
    {
        $vendedorId = (int)$this->request->getGet('vendedor_id');
        $productoId = (int)$this->request->getGet('producto_id');
        $clienteId  = $this->request->getGet('cliente_id') ?: null;

        $precioModel = new ConsignacionPrecioModel();
        $precio      = $precioModel->getPrecioRecomendado($vendedorId, $productoId, $clienteId);

        return $this->response->setJSON(['precio' => $precio]);
    }

    // ─────────────────────────────────────────────
    //  AJAX: facturas del vendedor
    // ─────────────────────────────────────────────

    public function facturasVendedor(int $vendedorId)
    {
        $db = \Config\Database::connect();
        $q  = trim($this->request->getGet('q') ?? '');

        $sql = "SELECT fh.id, fh.numero_control, fh.fecha_emision, fh.total_pagar,
                       SUBSTRING_INDEX(fh.numero_control, '-', -1) AS correlativo,
                       c.nombre AS cliente_nombre
                FROM facturas_head fh
                LEFT JOIN clientes c ON c.id = fh.receptor_id
                WHERE fh.vendedor_id = ?
                  AND fh.anulada = 0";

        $params = [$vendedorId];

        if ($q !== '') {
            $sql   .= " AND (fh.numero_control LIKE ? OR c.nombre LIKE ?)";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }

        $sql .= " ORDER BY fh.fecha_emision DESC LIMIT 100";

        $facturas = $db->query($sql, $params)->getResult();

        $results = [];
        foreach ($facturas as $f) {
            $corr   = substr($f->correlativo, -6);
            $text   = "#{$corr}";
            if (!empty($f->cliente_nombre)) $text .= ' · ' . $f->cliente_nombre;
            $text  .= ' · ' . date('d/m/Y', strtotime($f->fecha_emision));
            $text  .= ' · $' . number_format($f->total_pagar, 2);
            $results[] = ['id' => $f->id, 'text' => $text];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    // ─────────────────────────────────────────────
    //  REPORTES
    // ─────────────────────────────────────────────

    public function reportes()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        return view('consignaciones/reportes/index');
    }

    public function reporteNotas()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $db          = \Config\Database::connect();
        $sellerModel = new SellerModel();

        $filtros = [
            'fecha_inicio'      => $this->request->getGet('fecha_inicio')      ?: date('Y-m-01'),
            'fecha_fin'         => $this->request->getGet('fecha_fin')         ?: date('Y-m-d'),
            'vendedor_id'       => $this->request->getGet('vendedor_id')       ?: '',
            'estado'            => $this->request->getGet('estado')            ?: '',
            'aprobacion_estado' => $this->request->getGet('aprobacion_estado') ?: '',
        ];

        $q = $db->table('consignaciones_head ch')
            ->select('ch.*, s.seller AS vendedor_nombre, d.nombre AS doctor_nombre, c.nombre AS cliente_nombre')
            ->join('sellers s',  's.id = ch.vendedor_id', 'left')
            ->join('doctores d', 'd.id = ch.doctor_id',  'left')
            ->join('clientes c', 'c.id = ch.cliente_id', 'left')
            ->where('ch.fecha >=', $filtros['fecha_inicio'])
            ->where('ch.fecha <=', $filtros['fecha_fin']);

        if ($filtros['vendedor_id'])       $q->where('ch.vendedor_id', $filtros['vendedor_id']);
        if ($filtros['estado'])            $q->where('ch.estado', $filtros['estado']);
        if ($filtros['aprobacion_estado']) $q->where('ch.aprobacion_estado', $filtros['aprobacion_estado']);

        $notas = $q->orderBy('ch.fecha', 'DESC')->orderBy('ch.id', 'DESC')->get()->getResult();

        return view('consignaciones/reportes/notas', [
            'notas'      => $notas,
            'filtros'    => $filtros,
            'vendedores' => $sellerModel->orderBy('seller', 'ASC')->findAll(),
        ]);
    }

    public function reporteProductos()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $db          = \Config\Database::connect();
        $sellerModel = new SellerModel();

        $filtros = [
            'fecha_inicio' => $this->request->getGet('fecha_inicio') ?: date('Y-m-01'),
            'fecha_fin'    => $this->request->getGet('fecha_fin')    ?: date('Y-m-d'),
            'vendedor_id'  => $this->request->getGet('vendedor_id')  ?: '',
            'estado'       => $this->request->getGet('estado')       ?: '',
        ];

        $sql    = "
            SELECT p.codigo AS producto_codigo,
                   p.descripcion AS producto_nombre,
                   COUNT(DISTINCT ch.id) AS total_notas,
                   SUM(cd.cantidad) AS total_enviado,
                   SUM(cd.subtotal) AS valor_enviado,
                   COALESCE(SUM(ccd.cantidad_facturada), 0)      AS total_facturado,
                   COALESCE(SUM(ccd.cantidad_devuelta), 0)       AS total_devuelto,
                   COALESCE(SUM(ccd.cantidad_stock_vendedor), 0) AS total_stock
            FROM consignaciones_detalles cd
            INNER JOIN productos p ON p.id = cd.producto_id
            INNER JOIN consignaciones_head ch ON ch.id = cd.consignacion_id
            LEFT JOIN consignaciones_cierres cc ON cc.consignacion_id = ch.id
            LEFT JOIN consignaciones_cierres_detalles ccd
                   ON ccd.detalle_id = cd.id AND ccd.cierre_id = cc.id
            WHERE ch.fecha >= ? AND ch.fecha <= ?";
        $binds  = [$filtros['fecha_inicio'], $filtros['fecha_fin']];

        if ($filtros['vendedor_id']) { $sql .= ' AND ch.vendedor_id = ?'; $binds[] = $filtros['vendedor_id']; }
        if ($filtros['estado'])      { $sql .= ' AND ch.estado = ?';      $binds[] = $filtros['estado']; }

        $sql .= ' GROUP BY p.id, p.codigo, p.descripcion ORDER BY total_enviado DESC';

        $productos = $db->query($sql, $binds)->getResult();

        return view('consignaciones/reportes/productos', [
            'productos'  => $productos,
            'filtros'    => $filtros,
            'vendedores' => $sellerModel->orderBy('seller', 'ASC')->findAll(),
        ]);
    }

    public function reportePacientes()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $db          = \Config\Database::connect();
        $sellerModel = new SellerModel();

        $filtros = [
            'fecha_inicio' => $this->request->getGet('fecha_inicio') ?: date('Y-m-01'),
            'fecha_fin'    => $this->request->getGet('fecha_fin')    ?: date('Y-m-d'),
            'vendedor_id'  => $this->request->getGet('vendedor_id')  ?: '',
        ];

        $sql   = "
            SELECT ch.nombre AS paciente,
                   d.nombre AS doctor_nombre,
                   s.seller AS vendedor_nombre,
                   COUNT(DISTINCT ch.id) AS total_notas,
                   SUM(cd.cantidad)      AS total_productos,
                   SUM(ch.subtotal)      AS total_valor,
                   MIN(ch.fecha)         AS primera_fecha,
                   MAX(ch.fecha)         AS ultima_fecha
            FROM consignaciones_head ch
            INNER JOIN consignaciones_detalles cd ON cd.consignacion_id = ch.id
            LEFT JOIN sellers s  ON s.id = ch.vendedor_id
            LEFT JOIN doctores d ON d.id = ch.doctor_id
            WHERE ch.nombre IS NOT NULL AND ch.nombre != ''
              AND ch.fecha >= ? AND ch.fecha <= ?";
        $binds = [$filtros['fecha_inicio'], $filtros['fecha_fin']];

        if ($filtros['vendedor_id']) { $sql .= ' AND ch.vendedor_id = ?'; $binds[] = $filtros['vendedor_id']; }

        $sql .= ' GROUP BY ch.nombre, d.nombre, s.seller ORDER BY total_notas DESC, ch.nombre ASC';

        $pacientes = $db->query($sql, $binds)->getResult();

        return view('consignaciones/reportes/pacientes', [
            'pacientes'  => $pacientes,
            'filtros'    => $filtros,
            'vendedores' => $sellerModel->orderBy('seller', 'ASC')->findAll(),
        ]);
    }

    public function reporteDoctores()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $db          = \Config\Database::connect();
        $sellerModel = new SellerModel();

        $filtros = [
            'fecha_inicio' => $this->request->getGet('fecha_inicio') ?: date('Y-m-01'),
            'fecha_fin'    => $this->request->getGet('fecha_fin')    ?: date('Y-m-d'),
            'vendedor_id'  => $this->request->getGet('vendedor_id')  ?: '',
        ];

        $sql   = "
            SELECT d.nombre AS doctor_nombre,
                   COUNT(DISTINCT ch.id)     AS total_notas,
                   COUNT(DISTINCT ch.nombre) AS total_pacientes,
                   SUM(ch.subtotal)          AS total_valor,
                   MIN(ch.fecha)             AS primera_fecha,
                   MAX(ch.fecha)             AS ultima_fecha
            FROM consignaciones_head ch
            INNER JOIN doctores d ON d.id = ch.doctor_id
            WHERE ch.fecha >= ? AND ch.fecha <= ?";
        $binds = [$filtros['fecha_inicio'], $filtros['fecha_fin']];

        if ($filtros['vendedor_id']) { $sql .= ' AND ch.vendedor_id = ?'; $binds[] = $filtros['vendedor_id']; }

        $sql .= ' GROUP BY d.id, d.nombre ORDER BY total_notas DESC';

        $doctores = $db->query($sql, $binds)->getResult();

        return view('consignaciones/reportes/doctores', [
            'doctores'   => $doctores,
            'filtros'    => $filtros,
            'vendedores' => $sellerModel->orderBy('seller', 'ASC')->findAll(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  AUTORIZAR EDICIÓN DE LOTES
    // ─────────────────────────────────────────────

    public function autorizarLotes(int $id)
    {
        $chk = requerirPermiso('autorizar_lotes_consignacion');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso para autorizar lotes.']);
        }

        $session   = session();
        $headModel = new ConsignacionHeadModel();
        $nota      = $headModel->find($id);

        if (!$nota) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nota no encontrada.']);
        }

        if ($nota->estado !== 'abierta') {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se pueden autorizar notas abiertas.']);
        }

        if (!empty($nota->lotes_autorizados_por)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Los lotes ya fueron autorizados anteriormente.']);
        }

        $headModel->update($id, [
            'lotes_autorizados_por' => $session->get('id'),
            'lotes_autorizados_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->registrarLog($id, 'Lotes autorizados', 'Se habilitó la edición de lotes.');

        return $this->response->setJSON(['success' => true, 'message' => 'Lotes autorizados correctamente.']);
    }

    // ─────────────────────────────────────────────
    //  HELPER: LOG INTERNO
    // ─────────────────────────────────────────────

    private function resolverDistribucionLotesCierre(object $det, array $lin, string $campo, float $cantidad, array $lotesOriginales, string $etiqueta): array
    {
        if ($cantidad <= 0 || empty($lotesOriginales)) {
            return ['success' => true, 'lotes' => []];
        }

        if (abs($cantidad - (float)$det->cantidad) < 0.001) {
            $lotes = [];
            foreach ($lotesOriginales as $lote) {
                $lotes[] = [
                    'lote_id'  => (int)$lote->lote_id,
                    'cantidad' => (float)$lote->cantidad,
                ];
            }

            return ['success' => true, 'lotes' => $lotes];
        }

        if (count($lotesOriginales) === 1) {
            return [
                'success' => true,
                'lotes'   => [[
                    'lote_id'  => (int)$lotesOriginales[0]->lote_id,
                    'cantidad' => $cantidad,
                ]],
            ];
        }

        $lotesPermitidos = [];
        foreach ($lotesOriginales as $lote) {
            $lotesPermitidos[(int)$lote->lote_id] = (float)$lote->cantidad;
        }

        $lotesPost = $lin[$campo] ?? [];
        $lotes = [];
        $total = 0;

        foreach ($lotesPost as $loteId => $cantidadLote) {
            $loteId       = (int)$loteId;
            $cantidadLote = (float)$cantidadLote;

            if (!isset($lotesPermitidos[$loteId])) {
                return [
                    'success' => false,
                    'message' => 'Se recibio un lote invalido para la cantidad ' . $etiqueta . ' del producto ' . $det->producto_nombre . '.',
                ];
            }

            if ($cantidadLote <= 0) {
                continue;
            }

            if ($cantidadLote - $lotesPermitidos[$loteId] > 0.001) {
                return [
                    'success' => false,
                    'message' => 'La cantidad del lote supera la cantidad original en la parte ' . $etiqueta . ' del producto ' . $det->producto_nombre . '.',
                ];
            }

            $total += $cantidadLote;
            $lotes[] = [
                'lote_id'  => $loteId,
                'cantidad' => $cantidadLote,
            ];
        }

        if (abs($total - $cantidad) > 0.001) {
            return [
                'success' => false,
                'message' => 'La distribucion de lotes no coincide con la cantidad ' . $etiqueta . ' del producto ' . $det->producto_nombre . '.',
            ];
        }

        return ['success' => true, 'lotes' => $lotes];
    }

    private function validarConsumoLotesCierre(array $lotesOriginales, array $lotesFacturados, array $lotesStock, string $productoNombre): array
    {
        if (empty($lotesOriginales)) {
            return ['success' => true];
        }

        $permitidos = [];
        foreach ($lotesOriginales as $lote) {
            $permitidos[(int)$lote->lote_id] = (float)$lote->cantidad;
        }

        $consumo = [];
        foreach ([$lotesFacturados, $lotesStock] as $grupo) {
            foreach ($grupo as $lote) {
                $loteId = (int)$lote['lote_id'];
                $consumo[$loteId] = ($consumo[$loteId] ?? 0) + (float)$lote['cantidad'];
            }
        }

        foreach ($consumo as $loteId => $cantidad) {
            if (!isset($permitidos[$loteId]) || $cantidad - $permitidos[$loteId] > 0.001) {
                return [
                    'success' => false,
                    'message' => 'La suma de lotes facturados y en stock supera la cantidad original de un lote en el producto ' . $productoNombre . '.',
                ];
            }
        }

        return ['success' => true];
    }

    private function registrarLog(int $consignacionId, string $accion, ?string $detalle = null): void
    {
        $session  = session();
        $userId   = $session->get('id');
        $userName = $session->get('user_name') ?? ('Usuario #' . $userId);

        (new ConsignacionLogModel())->insert([
            'consignacion_id' => $consignacionId,
            'user_id'         => $userId,
            'user_nombre'     => $userName,
            'accion'          => $accion,
            'detalle'         => $detalle,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function reporteClientes()
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $db          = \Config\Database::connect();
        $sellerModel = new SellerModel();

        $filtros = [
            'fecha_inicio' => $this->request->getGet('fecha_inicio') ?: date('Y-m-01'),
            'fecha_fin'    => $this->request->getGet('fecha_fin')    ?: date('Y-m-d'),
            'vendedor_id'  => $this->request->getGet('vendedor_id')  ?: '',
        ];

        $sql   = "
            SELECT c.nombre AS cliente_nombre,
                   COUNT(DISTINCT ch.id)         AS total_notas,
                   COUNT(DISTINCT cf.factura_id) AS total_facturas,
                   COALESCE(SUM(fh.total_pagar), 0) AS total_facturado,
                   MIN(ch.fecha) AS primera_fecha,
                   MAX(ch.fecha) AS ultima_fecha
            FROM consignaciones_head ch
            INNER JOIN clientes c ON c.id = ch.cliente_id
            LEFT JOIN consignaciones_cierres cc ON cc.consignacion_id = ch.id
            LEFT JOIN consignaciones_cierres_facturas cf ON cf.cierre_id = cc.id
            LEFT JOIN facturas_head fh ON fh.id = cf.factura_id AND fh.anulada = 0
            WHERE ch.fecha >= ? AND ch.fecha <= ?";
        $binds = [$filtros['fecha_inicio'], $filtros['fecha_fin']];

        if ($filtros['vendedor_id']) { $sql .= ' AND ch.vendedor_id = ?'; $binds[] = $filtros['vendedor_id']; }

        $sql .= ' GROUP BY c.id, c.nombre ORDER BY total_facturado DESC';

        $clientes = $db->query($sql, $binds)->getResult();

        return view('consignaciones/reportes/clientes', [
            'clientes'   => $clientes,
            'filtros'    => $filtros,
            'vendedores' => $sellerModel->orderBy('seller', 'ASC')->findAll(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  AJAX: buscar NEs elegibles para importar en pedido
    //  Criterios: mismo vendedor del usuario en sesión,
    //  estado = 'abierta', aprobacion_estado = 'aprobada',
    //  lotes_autorizados_por IS NOT NULL
    // ─────────────────────────────────────────────

    public function searchParaPedidoAjax()
    {
        $chk = requerirPermiso('crear_pedidos');
        if ($chk !== true) return $this->response->setJSON(['results' => []]);

        $session = session();
        $db      = \Config\Database::connect();

        $seller = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id')
            ->get()->getRow();

        if (!$seller) {
            return $this->response->setJSON(['results' => []]);
        }

        $q = trim($this->request->getGet('q') ?? '');

        $builder = $db->table('consignaciones_head')
            ->select('consignaciones_head.id, consignaciones_head.numero, pacientes.nombre AS paciente_nombre')
            ->join('pacientes', 'pacientes.id = consignaciones_head.paciente_id', 'left')
            ->where('consignaciones_head.vendedor_id', $seller->id)
            ->where('consignaciones_head.estado', 'abierta')
            ->where('consignaciones_head.aprobacion_estado', 'aprobada')
            ->where('consignaciones_head.lotes_autorizados_por IS NOT NULL', null, false)
            ->orderBy('consignaciones_head.id', 'DESC')
            ->limit(50);

        if ($q !== '') {
            $builder->groupStart()
                ->like('consignaciones_head.numero', $q)
                ->orLike('pacientes.nombre', $q)
                ->groupEnd();
        }

        $rows    = $builder->get()->getResult();
        $results = [];
        foreach ($rows as $row) {
            $label = $row->numero;
            if (!empty($row->paciente_nombre)) {
                $label .= ' — ' . $row->paciente_nombre;
            }
            $results[] = ['id' => $row->id, 'text' => $label];
        }

        return $this->response->setJSON(['results' => $results]);
    }

    // ─────────────────────────────────────────────
    //  AJAX: productos de una NE para importar en pedido
    // ─────────────────────────────────────────────

    public function productosParaPedidoAjax(int $id)
    {
        $chk = requerirPermiso('crear_pedidos');
        if ($chk !== true) return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);

        $session = session();
        $db      = \Config\Database::connect();

        $seller = $db->table('sellers')
            ->join('users', 'users.seller_id = sellers.id')
            ->where('users.id', $session->get('id'))
            ->select('sellers.id')
            ->get()->getRow();

        if (!$seller) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuario sin vendedor asignado.']);
        }

        $ne = $db->table('consignaciones_head')
            ->where('id', $id)
            ->where('vendedor_id', $seller->id)
            ->where('estado', 'abierta')
            ->where('aprobacion_estado', 'aprobada')
            ->where('lotes_autorizados_por IS NOT NULL', null, false)
            ->get()->getRow();

        if (!$ne) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nota de envío no válida o no disponible.']);
        }

        // Cliente
        $clienteRow = $ne->cliente_id
            ? $db->table('clientes')->select('id, nombre, nrc')->where('id', $ne->cliente_id)->get()->getRow()
            : null;

        // Doctor
        $doctorRow = $ne->doctor_id
            ? $db->table('doctores')->select('id, nombre')->where('id', $ne->doctor_id)->get()->getRow()
            : null;

        // Paciente
        $pacienteRow = $ne->paciente_id
            ? $db->table('pacientes')->select('id, nombre, identificacion')->where('id', $ne->paciente_id)->get()->getRow()
            : null;

        // Detalles con lotes
        $detalles = $db->table('consignaciones_detalles cd')
            ->select('cd.id AS detalle_id, cd.producto_id, cd.cantidad, cd.precio_unitario, p.codigo, p.descripcion')
            ->join('productos p', 'p.id = cd.producto_id')
            ->where('cd.consignacion_id', $id)
            ->get()->getResult();

        $productos = [];
        foreach ($detalles as $d) {
            $loteRows = $db->table('consignacion_detalle_lotes cdl')
                ->select('cl.numero_lote, cl.fecha_vencimiento, cdl.cantidad')
                ->join('consignacion_lotes cl', 'cl.id = cdl.lote_id')
                ->where('cdl.detalle_id', $d->detalle_id)
                ->get()->getResult();

            $lotes = [];
            foreach ($loteRows as $l) {
                $lotes[] = [
                    'numero_lote'      => $l->numero_lote,
                    'fecha_vencimiento' => $l->fecha_vencimiento,
                    'cantidad'         => (float)$l->cantidad,
                ];
            }

            $productos[] = [
                'producto_id'     => (int)$d->producto_id,
                'producto_text'   => trim(($d->codigo ?? '') . ' — ' . $d->descripcion),
                'cantidad'        => (float)$d->cantidad,
                'precio_unitario' => (float)$d->precio_unitario,
                'lotes'           => $lotes,
            ];
        }

        return $this->response->setJSON([
            'success'   => true,
            'numero'    => $ne->numero,
            'cliente'   => $clienteRow ? [
                'id'   => (int)$clienteRow->id,
                'text' => $clienteRow->nombre,
                'nrc'  => $clienteRow->nrc ?? '',
            ] : null,
            'doctor'    => $doctorRow ? [
                'id'   => (int)$doctorRow->id,
                'text' => $doctorRow->nombre,
            ] : null,
            'paciente'  => $pacienteRow ? [
                'id'   => (int)$pacienteRow->id,
                'text' => $pacienteRow->nombre . (!empty($pacienteRow->identificacion) ? ' | ' . $pacienteRow->identificacion : ''),
            ] : null,
            'productos' => $productos,
        ]);
    }
}
