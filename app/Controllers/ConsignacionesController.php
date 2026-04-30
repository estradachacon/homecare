<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConsignacionHeadModel;
use App\Models\ConsignacionDetalleModel;
use App\Models\ConsignacionPrecioModel;
use App\Models\ConsignacionCierreModel;
use App\Models\ConsignacionCierreDetalleModel;
use App\Models\ConsignacionCierreFacturaModel;
use App\Models\ConsignacionLoteModel;
use App\Models\ConsignacionDetalleLoteModel;
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

        $headModel = new ConsignacionHeadModel();

        $filtros = [
            'vendedor_id'  => $this->request->getGet('vendedor_id'),
            'estado'       => $this->request->getGet('estado'),
            'fecha_inicio' => $this->request->getGet('fecha_inicio'),
            'fecha_fin'    => $this->request->getGet('fecha_fin'),
        ];

        $perPage = 15;

        $consignaciones = $headModel->listar($filtros)->paginate($perPage);
        $pager          = $headModel->pager;

        $sellerModel = new SellerModel();

        return view('consignaciones/index', [
            'consignaciones' => $consignaciones,
            'pager'          => $pager,
            'filtros'        => $filtros,
            'vendedores'     => $sellerModel->orderBy('seller', 'ASC')->findAll(),
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

        return view('consignaciones/crear', [
            'numero_sugerido' => $headModel->siguienteNumero(),
            'vendedores'      => $sellerModel->orderBy('seller', 'ASC')->findAll(),
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

        $id = $headModel->insert([
            'numero'          => $this->request->getPost('numero'),
            'vendedor_id'     => $this->request->getPost('vendedor_id'),
            'nombre'          => $this->request->getPost('nombre'),
            'concepto'        => $this->request->getPost('concepto'),
            'fecha'           => $fecha,
            'hora'            => $hora,
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'subtotal'        => $subtotal,
            'doctor_id' => $this->request->getPost('doctor_id') ?: null,
            'cliente_id' => $this->request->getPost('cliente_id') ?: null,
            'observaciones'   => $this->request->getPost('observaciones'),
            'estado'          => 'abierta',
            'created_by'      => $session->get('id'),
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

        return redirect()->to('/consignaciones/' . $id)
            ->with('success', 'Nota de envío creada correctamente.');
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

        return view('consignaciones/detalle', [
            'consignacion'      => $consignacion,
            'detalles'          => $detalles,
            'cierre'            => $cierre,
            'facturasPorDetalle' => $facturasPorDetalle,
            'mapCierreDetalle'  => $mapCierreDetalle,
            'lotesPorDetalle'   => $lotesPorDetalle,
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
        $lotesPorDetalle = [];

        foreach ($detalles as $d) {
            $lotesPorDetalle[$d->id] = $detalleLoteModel->getPorDetalle($d->id);
        }

        return view('consignaciones/cerrar', [
            'aprobada'        => ($consignacion->aprobacion_estado ?? '') === 'aprobada',
            'consignacion'    => $consignacion,
            'detalles'        => $detalles,
            'lotesPorDetalle' => $lotesPorDetalle,
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

        $consignacion = $headModel->find($id);
        if (!$consignacion || $consignacion->estado !== 'abierta') {
            return redirect()->to('/consignaciones/' . $id)
                ->with('error', 'Esta nota no puede cerrarse.');
        }

        $detalles    = $detModel->getPorConsignacion($id);
        $lineas      = $this->request->getPost('lineas') ?? [];
        $obsGenerales = $this->request->getPost('observaciones_cierre');

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

        $detalleLoteModel = new ConsignacionDetalleLoteModel();
        $lotesPorDetalle = [];

        foreach ($detalles as $d) {
            $lotesPorDetalle[$d->id] = $detalleLoteModel->getPorDetalle($d->id);
        }

        return view('consignaciones/editar', [
            'consignacion'    => $consignacion,
            'detalles'        => $detalles,
            'vendedores'      => $sellerModel->orderBy('seller', 'ASC')->findAll(),
            'doctor'          => $doctor,
            'cliente'         => $cliente,
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

        $headModel->update($id, [
            'vendedor_id'   => $this->request->getPost('vendedor_id'),
            'nombre'        => $this->request->getPost('nombre'),
            'doctor_id'     => $this->request->getPost('doctor_id') ?: null,
            'cliente_id'    => $this->request->getPost('cliente_id') ?: null,
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
        $lotes      = (new ConsignacionLoteModel())->getPorProducto($productoId);

        $results = array_map(function ($l) {
            $text = $l->numero_lote;

            if (!empty($l->fecha_vencimiento)) {
                $text .= ' (vence: ' . $l->fecha_vencimiento . ')';
            }

            if (!empty($l->manufactura)) {
                $text .= ' (manuf: ' . $l->manufactura . ')';
            }

            return [
                'id'   => $l->id,
                'text' => $text,
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
        $chk = requerirPermiso('crear_consignaciones');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso.']);
        }

        $db = \Config\Database::connect();

        $detRow = $db->table('consignaciones_detalles cd')
            ->select('cd.cantidad, ch.estado')
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

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lotes guardados correctamente.',
            'detalle_id' => $detalleId,
            'lotes' => $lotes,
            'total_lotes' => count($lotes),
            'total_asignado' => $totalAsignado
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

        $builder = $db->table('facturas_head')
            ->select('id, numero_control, fecha_emision, total_pagar')
            ->where('vendedor_id', $vendedorId)
            ->where('anulada', 0)
            ->orderBy('fecha_emision', 'DESC')
            ->limit(100);

        if ($q !== '') {
            $builder->like('numero_control', $q);
        }

        $facturas = $builder->get()->getResult();

        $results = [];
        foreach ($facturas as $f) {
            $results[] = [
                'id'   => $f->id,
                'text' => $f->numero_control . ' — $' . number_format($f->total_pagar, 2),
            ];
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
}
