<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProveedorModel;
use App\Models\CompraHeadModel;    
use App\Models\CompraDetalleModel;    

class ComprasController extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_compras');
        if ($chk !== true) return $chk;

        return view('compras/index');
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

            foreach ($json['cuerpoDocumento'] as $item) {

                $detalle = [
                    'compra_id' => $compraId,
                    'num_item' => $item['numItem'] ?? null,
                    'tipo_item' => $item['tipoItem'] ?? null,
                    'codigo' => trim($item['codigo'] ?? ''),
                    'descripcion' => strtok(trim($item['descripcion'] ?? ''), "\n"),
                    'cantidad' => $item['cantidad'] ?? 0,
                    'unidad_medida' => $item['uniMedida'] ?? null,
                    'precio_unitario' => $item['precioUni'] ?? 0,
                    'monto_descuento' => $item['montoDescu'] ?? 0,
                    'iva_item' => $item['ivaItem'] ?? 0,
                    'producto_id' => null // por ahora no usamos productos
                ];

                if (!$compraDetalleModel->insert($detalle)) {

                    $db->transRollback();

                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error insertando detalle',
                        'errors' => $compraDetalleModel->errors(),
                        'data' => $detalle
                    ]);
                }
            }
        }

        $procesadas++;
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
        'message' => "Compras procesadas correctamente",
        'total' => $procesadas
    ]);
}
}
