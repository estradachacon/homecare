<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ConsignacionHeadModel;
use App\Models\ConsignacionDetalleModel;
use App\Models\ConsignacionPrecioModel;
use App\Models\ConsignacionCierreModel;
use App\Models\ConsignacionCierreDetalleModel;
use App\Models\ConsignacionCierreFacturaModel;
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

        return view('consignaciones/detalle', [
            'consignacion' => $consignacion,
            'detalles'     => $detalles,
            'cierre'       => $cierre,
            'facturasPorDetalle' => $facturasPorDetalle,
            'mapCierreDetalle' => $mapCierreDetalle,
        ]);
    }

    // ─────────────────────────────────────────────
    //  VISTA IMPRIMIBLE
    // ─────────────────────────────────────────────

    public function imprimir(int $id)
    {
        $chk = requerirPermiso('ver_consignaciones');
        if ($chk !== true) return $chk;

        $headModel = new ConsignacionHeadModel();
        $detModel  = new ConsignacionDetalleModel();

        $consignacion = $headModel->getConVendedor($id);
        if (!$consignacion) {
            return redirect()->to('/consignaciones')->with('error', 'Nota no encontrada.');
        }

        return view('consignaciones/print', [
            'consignacion' => $consignacion,
            'detalles'     => $detModel->getPorConsignacion($id),
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

        return view('consignaciones/cerrar', [
            'consignacion' => $consignacion,
            'detalles'     => $detalles,
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
                    $detModel->insert([
                        'consignacion_id' => $nuevoId,
                        'producto_id'     => $det->producto_id,
                        'cantidad'        => $cantStock,
                        'precio_unitario' => $det->precio_unitario,
                        'subtotal'        => round($cantStock * $det->precio_unitario, 2),
                    ]);
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
            $msg .= ' Se generó la nueva nota ' . $db->table('consignaciones_head')->where('id', $nuevoId)->get()->getRow()->numero ?? '' . ' con el stock restante.';
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

        $facturas = $db->table('facturas_head')
            ->select('id, numero_control, fecha_emision, total_pagar')
            ->where('vendedor_id', $vendedorId)
            ->where('anulada', 0)
            ->orderBy('fecha_emision', 'DESC')
            ->limit(200)
            ->get()
            ->getResult();

        $results = [];
        foreach ($facturas as $f) {
            $results[] = [
                'id'   => $f->id,
                'text' => $f->numero_control . ' — $' . number_format($f->total_pagar, 2),
            ];
        }

        return $this->response->setJSON(['results' => $results]);
    }
}
