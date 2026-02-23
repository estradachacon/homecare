<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FacturaJsonModel;
use App\Models\ClienteModel;

class Facturas extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_facturas');
        if ($chk !== true) return $chk;

        $model = new \App\Models\FacturaHeadModel();

        $model->select('facturas_head.*, clientes.nombre AS cliente_nombre, sellers.seller AS vendedor')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left');

        // ================= FILTROS =================

        $clienteId = $this->request->getGet('cliente_id');
        $sellerId  = $this->request->getGet('seller_id');
        $estado = $this->request->getGet('estado');
        $tipoDte = $this->request->getGet('tipo_dte');
        $fecha = $this->request->getGet('fecha');

        if (is_numeric($clienteId)) {
            $model->where('facturas_head.receptor_id', $clienteId);
        }

        if (is_numeric($sellerId)) {
            $model->where('facturas_head.vendedor_id', $sellerId);
        }

        if ($estado === 'activa') {
            $model->where('facturas_head.anulada', 0);
        }

        if ($estado === 'anulada') {
            $model->where('facturas_head.anulada', 1);
        }

        if ($estado === 'pagada') {
            $model->where('facturas_head.anulada', 0)
                ->where('facturas_head.saldo', 0);
        }

        if (is_numeric($tipoDte)) {
            $model->where('facturas_head.tipo_dte', $tipoDte);
        }

        if (!empty($fecha)) {
            $model->where('facturas_head.fecha_emision', $fecha);
        }

        // ==========================================

        $model->orderBy('fecha_emision', 'DESC')
            ->orderBy("CAST(SUBSTRING(numero_control, -6) AS UNSIGNED)", 'DESC', false);

        $facturas = $model->paginate(10);
        $pager = $model->pager;

        if ($this->request->isAJAX()) {
            return view('facturas/tbody_row', compact('facturas'));
        }

        return view('facturas/index', compact('facturas', 'pager'));
    }

    public function carga()
    {
        $chk = requerirPermiso('cargar_facturas');
        if ($chk !== true) return $chk;

        return view('facturas/carga_procesado');
    }
    public function procesarCarga()
    {
        helper(['form']);
        $user_id = session()->get('user_id');

        $files = $this->request->getFiles();
        $sellerIds = $this->request->getPost('seller_ids');

        if (!isset($files['archivos'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron archivos'
            ]);
        }

        $facturaHeadModel = new \App\Models\FacturaHeadModel();
        $facturaHeadModel->resetQuery();

        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();
        $facturaJsonModel    = new FacturaJsonModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $facturasInsertadas = 0;
        $controles = [];
        $totalOperacion = 0;

        foreach ($files['archivos'] as $index => $file) {

            if (!$file->isValid()) {
                continue;
            }

            $contenido = file_get_contents($file->getTempName());
            $json = json_decode($contenido, true);
            $clienteModel = new ClienteModel();
            $vendedorId = $sellerIds[$index] ?? null;

            if (!$json) {
                continue;
            }
            // =============================
            // PROCESAR CLIENTE (RECEPTOR)
            // =============================
            $clienteId = null;

            if (!empty($json['receptor'])) {

                $receptor = $json['receptor'];

                $nrc = $receptor['nrc'] ?? null;

                // Detectar tipo y número automáticamente
                if (!empty($receptor['tipoDocumento']) && !empty($receptor['numDocumento'])) {

                    // Persona natural
                    $tipoDocumento   = $receptor['tipoDocumento'];
                    $numeroDocumento = $receptor['numDocumento'];
                } elseif (!empty($receptor['nit'])) {

                    // Empresa
                    $tipoDocumento   = '36'; // NIT
                    $numeroDocumento = $receptor['nit'];
                } else {

                    $tipoDocumento   = null;
                    $numeroDocumento = null;
                }

                if ($tipoDocumento && $numeroDocumento) {

                    // Buscar por tipo + número
                    $cliente = $clienteModel->buscarPorDocumento($tipoDocumento, $numeroDocumento);

                    // Si no existe y tiene NRC, buscar por NRC
                    if (!$cliente && $nrc) {
                        $cliente = $clienteModel->buscarPorNRC($nrc);
                    }

                    // Si no existe → crearlo
                    if (!$cliente) {

                        $clienteId = $clienteModel->insert([
                            'tipo_documento'   => $tipoDocumento,
                            'numero_documento' => $numeroDocumento,
                            'nrc'              => $nrc,
                            'nombre'           => $receptor['nombre'] ?? null,
                            'telefono'         => $receptor['telefono'] ?? null,
                            'correo'           => $receptor['correo'] ?? null,
                            'departamento'     => $receptor['direccion']['departamento'] ?? null,
                            'municipio'        => $receptor['direccion']['municipio'] ?? null,
                            'direccion'        => $receptor['direccion']['complemento'] ?? null,
                        ]);
                    } else {

                        $clienteId = $cliente->id;

                        // 🔥 OPCIONAL PRO
                        // actualizar datos si vienen nuevos
                        $clienteModel->update($clienteId, [
                            'telefono' => $receptor['telefono'] ?? $cliente->telefono,
                            'correo'   => $receptor['correo'] ?? $cliente->correo,
                        ]);
                    }

                    if (!$clienteId && $cliente) {
                        $clienteId = $cliente->id;
                    }
                }
            }
            // INSERTAR HEAD
            $dataHead = [
                'ambiente'          => $json['identificacion']['ambiente'] ?? null,
                'tipo_dte'          => $json['identificacion']['tipoDte'] ?? null,
                'numero_control'    => $json['identificacion']['numeroControl'] ?? null,
                'codigo_generacion' => $json['identificacion']['codigoGeneracion'] ?? null,
                'fecha_emision'     => $json['identificacion']['fecEmi'] ?? null,
                'hora_emision'      => $json['identificacion']['horEmi'] ?? null,
                'tipo_moneda'       => $json['identificacion']['tipoMoneda'] ?? null,
                'receptor_id'       => $clienteId,
                'vendedor_id'       => $vendedorId,
                'saldo'              => $json['resumen']['totalPagar'] ?? 0,

                'total_gravada'         => $json['resumen']['totalGravada'] ?? 0,
                'sub_total'             => $json['resumen']['subTotal'] ?? 0,
                'total_iva'             => $json['resumen']['totalIva'] ?? 0,
                'monto_total_operacion' => $json['resumen']['montoTotalOperacion'] ?? 0,
                'total_pagar'           => $json['resumen']['totalPagar'] ?? 0,
                'condicion_operacion'   => $json['resumen']['condicionOperacion'] ?? null,
            ];

            $existe = $facturaHeadModel
                ->where('numero_control', $dataHead['numero_control'])
                ->first();

            if ($existe) {
                continue; // evita duplicados silenciosamente
            }

            if (!$facturaHeadModel->insert($dataHead)) {

                $db->transRollback();

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error insertando factura',
                    'errors'  => $facturaHeadModel->errors()
                ]);
            }

            $facturaId = $facturaHeadModel->getInsertID();

            $facturasInsertadas++;
            $controles[] = substr($dataHead['numero_control'], -6);
            $totalOperacion += $dataHead['total_pagar'];

            if (!$facturaId) {
                continue;
            }

            // GUARDAR JSON ORIGINAL
            $facturaJsonModel->guardarJson(
                $facturaId,
                $contenido // guardamos el string original completo
            );

            // INSERTAR DETALLES
            if (!empty($json['cuerpoDocumento'])) {

                foreach ($json['cuerpoDocumento'] as $item) {

                    $facturaDetalleModel->insert([
                        'factura_id'      => $facturaId,
                        'num_item'        => $item['numItem'] ?? null,
                        'tipo_item'       => $item['tipoItem'] ?? null,
                        'codigo'          => $item['codigo'] ?? null,
                        'descripcion'     => $item['descripcion'] ?? null,
                        'cantidad'        => $item['cantidad'] ?? 0,
                        'unidad_medida'   => $item['uniMedida'] ?? null,
                        'precio_unitario' => $item['precioUni'] ?? 0,
                        'monto_descuento' => $item['montoDescu'] ?? 0,
                        'venta_no_sujeta' => $item['ventaNoSuj'] ?? 0,
                        'venta_exenta'    => $item['ventaExenta'] ?? 0,
                        'venta_gravada'   => $item['ventaGravada'] ?? 0,
                        'iva_item'        => $item['ivaItem'] ?? 0,
                    ]);
                }
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al procesar las facturas'
            ]);
        }

        // Registrar bitácora
        registrar_bitacora(
            'Carga masiva de facturas',
            'Facturas',
            sprintf(
                'Cargó %d factura(s) desde archivos JSON. Total procesado: $%s. Controles: %s',
                $facturasInsertadas,
                number_format($totalOperacion, 2),
                implode(', ', $controles)
            ),
            $user_id
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Facturas procesadas correctamente'
        ]);
    }
    public function detalle($id)
    {
        $facturaHeadModel    = new \App\Models\FacturaHeadModel();
        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();

        // Cabecera
        $factura = $facturaHeadModel
            ->select('facturas_head.*, clientes.nombre AS cliente, sellers.seller AS vendedor')
            ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
            ->join('sellers', 'sellers.id = facturas_head.vendedor_id', 'left')
            ->where('facturas_head.id', $id)
            ->first();

        if (!$factura) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Detalles
        $detalles = $facturaDetalleModel
            ->where('factura_id', $id)
            ->findAll();

        return view('facturas/detalle', [
            'factura'  => $factura,
            'detalles' => $detalles
        ]);
    }
    public function validarNumeroControl()
    {
        $numeroControl = $this->request->getPost('numero_control');

        if (!$numeroControl) {
            return $this->response->setJSON([
                'existe' => false
            ]);
        }

        $facturaHeadModel = new \App\Models\FacturaHeadModel();

        $existe = $facturaHeadModel
            ->where('numero_control', $numeroControl)
            ->first();

        return $this->response->setJSON([
            'existe' => $existe ? true : false
        ]);
    }
    public function anular($id)
    {
        if (!tienePermiso('anular_factura')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para anular facturas.'
            ]);
        }

        $user_id = session()->get('user_id');

        $facturaModel = new \App\Models\FacturaHeadModel();

        $factura = $facturaModel->find($id);

        if (!$factura) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Factura no encontrada.'
            ]);
        }

        if ($factura->anulada == 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'La factura ya está anulada.'
            ]);
        }

        // Marcar como anulada
        $facturaModel->update($id, [
            'anulada' => 1,
            'saldo'   => 0
        ]);

        // Bitácora
        registrar_bitacora(
            'Anulación de factura',
            'Facturas',
            'Anuló factura Nº ' . substr($factura->numero_control, -6) .
                ' por monto $' . number_format($factura->total_pagar, 2),
            $user_id
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Factura anulada correctamente.'
        ]);
    }
}
