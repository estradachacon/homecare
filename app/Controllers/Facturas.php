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

    $facturaHeadModel = new \App\Models\FacturaHeadModel();

    $facturas = $facturaHeadModel
        ->select('facturas_head.*, clientes.nombre AS cliente_nombre')
        ->join('clientes', 'clientes.id = facturas_head.receptor_id', 'left')
        ->orderBy('fecha_emision', 'DESC')
        ->orderBy("CAST(SUBSTRING(numero_control, -6) AS UNSIGNED)", 'DESC', false)
        ->findAll();

    return view('facturas/index', [
        'facturas' => $facturas
    ]);
}

    public function carga()
    {
        $chk = requerirPermiso('cargar_facturas');
        if ($chk !== true) return $chk;

        return view('facturas/carga_procesado');
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

        $facturaHeadModel = new \App\Models\FacturaHeadModel();


        $facturaDetalleModel = new \App\Models\FacturaDetalleModel();
        $facturaJsonModel    = new FacturaJsonModel();

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($files['archivos'] as $file) {

            if (!$file->isValid()) {
                continue;
            }

            $contenido = file_get_contents($file->getTempName());
            $json = json_decode($contenido, true);
            $clienteModel = new ClienteModel();

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
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Facturas procesadas correctamente'
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
}
