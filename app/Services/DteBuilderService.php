<?php

namespace App\Services;

use App\Models\EmisorModel;
use App\Models\ClienteModel;

class DteBuilderService
{
    protected object $emisor;

    public function __construct()
    {
        $emisorModel  = new EmisorModel();
        $this->emisor = $emisorModel->first();

        if (!$this->emisor) {
            throw new \RuntimeException('No hay datos de emisor configurados en el sistema.');
        }
    }

    // ─────────────────────────────────────────────
    //  FACTURA CONSUMIDOR FINAL (01)
    // ─────────────────────────────────────────────

    public function buildFactura(array $data): array
    {
        $ambiente   = env('hacienda.env', '00');
        $codigoGen  = $this->generarUUID();
        $numControl = $this->siguienteNumeroControl('01');
        $items      = $data['items'] ?? [];

        $cuerpo    = $this->buildCuerpo($items);
        $resumen   = $this->buildResumenFactura($cuerpo, $data);

        $cliente   = (new ClienteModel())->find($data['cliente_id']);

        return [
            'identificacion' => [
                'version'          => 1,
                'ambiente'         => $ambiente,
                'tipoDte'          => '01',
                'numeroControl'    => $numControl,
                'codigoGeneracion' => $codigoGen,
                'tipoModelo'       => 1,
                'tipoOperacion'    => 1,
                'tipoContingencia' => null,
                'motivoContigencia'=> null,
                'fecEmi'           => $data['fecha_emision'],
                'horEmi'           => $data['hora_emision'],
                'tipoMoneda'       => 'USD',
            ],
            'documentoRelacionado' => null,
            'sujetoExcluido'       => null,
            'emisor'               => $this->buildEmisor(),
            'receptor'             => $this->buildReceptorFactura($cliente),
            'otrosDocumentos'      => null,
            'ventaTercero'         => null,
            'cuerpoDocumento'      => $cuerpo,
            'resumen'              => $resumen,
            'extension'            => null,
            'apendice'             => null,
        ];
    }

    // ─────────────────────────────────────────────
    //  NOTA DE REMISIÓN (04)
    // ─────────────────────────────────────────────

    public function buildNR(array $data): array
    {
        $dte = $this->buildFactura($data);
        $dte['identificacion']['tipoDte']      = '04';
        $dte['identificacion']['version']      = 1;
        $dte['identificacion']['numeroControl'] = $this->siguienteNumeroControl('04');
        // Recalcular codigoGeneracion propio
        $dte['identificacion']['codigoGeneracion'] = $this->generarUUID();

        return $dte;
    }

    // ─────────────────────────────────────────────
    //  NOTA DE CRÉDITO (05)
    // ─────────────────────────────────────────────

    /**
     * Construye una Nota de Crédito Electrónica que hace referencia a un DTE
     * original ya emitido.
     *
     * @param array   $data        Misma estructura que buildFactura/buildCCF
     * @param object  $original    Row de facturas_head del documento original
     */
    public function buildNC(array $data, object $original): array
    {
        $ambiente  = env('hacienda.env', '00');
        $items     = $data['items'] ?? [];

        $cuerpo  = $this->buildCuerpo($items);
        // El resumen de NC usa el mismo formato que Factura
        $resumen = $this->buildResumenFactura($cuerpo, $data);
        // NC no incluye pagos (es una corrección, no un cobro)
        $resumen['pagos'] = null;

        $cliente = (new ClienteModel())->find($data['cliente_id'] ?? $original->receptor_id);

        return [
            'identificacion' => [
                'version'          => 3,
                'ambiente'         => $ambiente,
                'tipoDte'          => '05',
                'numeroControl'    => $this->siguienteNumeroControl('05'),
                'codigoGeneracion' => $this->generarUUID(),
                'tipoModelo'       => 1,
                'tipoOperacion'    => 1,
                'tipoContingencia' => null,
                'motivoContigencia'=> null,
                'fecEmi'           => $data['fecha_emision'],
                'horEmi'           => $data['hora_emision'],
                'tipoMoneda'       => 'USD',
            ],
            'documentoRelacionado' => [
                [
                    'tipoDocumento'   => $original->tipo_dte,
                    'tipoGeneracion'  => 2,
                    'numeroDocumento' => $original->numero_control,
                    'fechaEmision'    => $original->fecha_emision,
                ],
            ],
            'emisor'          => $this->buildEmisor(),
            'receptor'        => $this->buildReceptorFactura($cliente),
            'otrosDocumentos' => null,
            'ventaTercero'    => null,
            'cuerpoDocumento' => $cuerpo,
            'resumen'         => $resumen,
            'extension'       => null,
            'apendice'        => null,
        ];
    }

    // ─────────────────────────────────────────────
    //  COMPROBANTE DE CRÉDITO FISCAL (03)
    // ─────────────────────────────────────────────

    public function buildCCF(array $data): array
    {
        $ambiente   = env('hacienda.env', '00');
        $codigoGen  = $this->generarUUID();
        $numControl = $this->siguienteNumeroControl('03');
        $items      = $data['items'] ?? [];

        $cuerpo  = $this->buildCuerpo($items);
        $resumen = $this->buildResumenCCF($cuerpo, $data);

        $cliente = (new ClienteModel())->find($data['cliente_id']);

        return [
            'identificacion' => [
                'version'          => 3,
                'ambiente'         => $ambiente,
                'tipoDte'          => '03',
                'numeroControl'    => $numControl,
                'codigoGeneracion' => $codigoGen,
                'tipoModelo'       => 1,
                'tipoOperacion'    => 1,
                'tipoContingencia' => null,
                'motivoContigencia'=> null,
                'fecEmi'           => $data['fecha_emision'],
                'horEmi'           => $data['hora_emision'],
                'tipoMoneda'       => 'USD',
            ],
            'documentoRelacionado' => null,
            'emisor'               => $this->buildEmisor(),
            'receptor'             => $this->buildReceptorCCF($cliente),
            'otrosDocumentos'      => null,
            'ventaTercero'         => null,
            'cuerpoDocumento'      => $cuerpo,
            'resumen'              => $resumen,
            'extension'            => null,
            'apendice'             => null,
        ];
    }

    // ─────────────────────────────────────────────
    //  BLOQUES INTERNOS
    // ─────────────────────────────────────────────

    private function buildEmisor(): array
    {
        $e = $this->emisor;

        return [
            'nit'                 => $e->nit,
            'nrc'                 => $e->nrc,
            'nombre'              => $e->nombre,
            'codActividad'        => $e->cod_actividad,
            'descActividad'       => $e->desc_actividad,
            'nombreComercial'     => $e->nombre_comercial ?: null,
            'tipoEstablecimiento' => $e->tipo_establecimiento,
            'direccion'           => [
                'departamento' => $e->departamento,
                'municipio'    => $e->municipio,
                'complemento'  => $e->complemento,
            ],
            'telefono'       => preg_replace('/[^0-9]/', '', $e->telefono),
            'correo'         => $e->correo,
            'codEstableMH'   => $e->cod_estable_mh,
            'codEstable'     => $e->cod_estable,
            'codPuntoVentaMH'=> $e->cod_punto_venta_mh,
            'codPuntoVenta'  => $e->cod_punto_venta,
        ];
    }

    private function buildReceptorFactura(?object $cliente): array
    {
        if (!$cliente) {
            return [
                'tipoDocumento' => null,
                'numDocumento'  => null,
                'nombre'        => 'Consumidor Final',
                'correo'        => null,
                'telefono'      => null,
            ];
        }

        return [
            'tipoDocumento' => $this->mapTipoDocumento($cliente->tipo_documento),
            'numDocumento'  => $cliente->numero_documento ?: null,
            'nombre'        => $cliente->nombre,
            'correo'        => $cliente->correo ?: null,
            'telefono'      => $cliente->telefono ?: null,
        ];
    }

    private function buildReceptorCCF(?object $cliente): array
    {
        if (!$cliente) {
            throw new \RuntimeException('Para CCF se requiere datos del cliente con NIT y NRC.');
        }

        return [
            'nit'            => $cliente->nit ?? $cliente->numero_documento,
            'nrc'            => $cliente->nrc ?: null,
            'nombre'         => $cliente->nombre,
            'codActividad'   => $cliente->cod_actividad ?? null,
            'descActividad'  => $cliente->desc_actividad ?? null,
            'nombreComercial'=> $cliente->nombre_comercial ?? null,
            'direccion'      => [
                'departamento' => $cliente->departamento ?? '06',
                'municipio'    => $cliente->municipio ?? '14',
                'complemento'  => $cliente->direccion ?? '',
            ],
            'telefono' => $cliente->telefono ?: null,
            'correo'   => $cliente->correo ?: null,
        ];
    }

    private function buildCuerpo(array $items): array
    {
        $cuerpo = [];

        foreach ($items as $i => $item) {
            $cantidad     = round((float)($item['cantidad'] ?? 1), 6);
            $precioUni    = round((float)($item['precio_uni'] ?? 0), 6);
            $descuento    = round((float)($item['descuento'] ?? 0), 2);
            $tipoItem     = (int)($item['tipo_item'] ?? 1);
            $descripcion  = trim($item['descripcion'] ?? '');

            $ventaBruta   = round($cantidad * $precioUni, 6);
            $ventaGravada = round($ventaBruta - $descuento, 2);
            if ($ventaGravada < 0) $ventaGravada = 0.00;

            $ivaItem = round($ventaGravada * 0.13, 2);

            $cuerpo[] = [
                'numItem'         => $i + 1,
                'tipoItem'        => $tipoItem,
                'numeroDocumento' => null,
                'cantidad'        => $cantidad,
                'codigo'          => $item['codigo'] ?? null,
                'codTributo'      => null,
                'uniMedida'       => (int)($item['uni_medida'] ?? 59),
                'descripcion'     => $descripcion ?: 'Producto/Servicio',
                'precioUni'       => round($precioUni, 2),
                'montoDescu'      => $descuento,
                'ventaNoSuj'      => 0.00,
                'ventaExenta'     => 0.00,
                'ventaGravada'    => $ventaGravada,
                'tributos'        => null,
                'psv'             => 0.00,
                'noGravado'       => 0.00,
                'ivaItem'         => $ivaItem,
            ];
        }

        return $cuerpo;
    }

    private function buildResumenFactura(array $cuerpo, array $data): array
    {
        $totalGravada = 0.00;
        $totalIva     = 0.00;

        foreach ($cuerpo as $item) {
            $totalGravada += $item['ventaGravada'];
            $totalIva     += $item['ivaItem'];
        }

        $totalGravada = round($totalGravada, 2);
        $totalIva     = round($totalIva, 2);
        $subTotal     = $totalGravada;
        $totalOp      = round($totalGravada + $totalIva, 2);

        $condicion = ($data['condicion_operacion'] ?? 'contado') === 'credito' ? 2 : 1;
        $plazo     = $condicion === 2 ? ($data['plazo_credito'] ?? null) : null;

        return [
            'totalNoSuj'          => 0.00,
            'totalExenta'         => 0.00,
            'totalGravada'        => $totalGravada,
            'subTotalVentas'      => $subTotal,
            'descuNoSuj'          => 0.00,
            'descuExenta'         => 0.00,
            'descuGravada'        => 0.00,
            'porcentajeDescuento' => 0.00,
            'totalDescu'          => 0.00,
            'tributos'            => [
                [
                    'codigo'      => '20',
                    'descripcion' => 'Impuesto al Valor Agregado 13%',
                    'valor'       => $totalIva,
                ],
            ],
            'subTotal'             => $subTotal,
            'ivaRete1'             => 0.00,
            'reteRenta'            => 0.00,
            'montoTotalOperacion'  => $totalOp,
            'totalLetras'          => $this->numeroALetras($totalOp),
            'totalIva'             => $totalIva,
            'saldoFavor'           => 0.00,
            'condicionOperacion'   => $condicion,
            'pagos'                => [
                [
                    'codigo'     => '01',
                    'montoPago'  => $totalOp,
                    'referencia' => null,
                    'plazo'      => $plazo ? (string)$plazo : null,
                    'periodo'    => $plazo ? 'días' : null,
                ],
            ],
            'numPagoElectronico' => null,
        ];
    }

    private function buildResumenCCF(array $cuerpo, array $data): array
    {
        $totalGravada = 0.00;
        $totalIva     = 0.00;

        foreach ($cuerpo as $item) {
            $totalGravada += $item['ventaGravada'];
            $totalIva     += $item['ivaItem'];
        }

        $totalGravada = round($totalGravada, 2);
        $totalIva     = round($totalIva, 2);
        $subTotal     = $totalGravada;
        $totalOp      = round($totalGravada + $totalIva, 2);

        $condicion = ($data['condicion_operacion'] ?? 'contado') === 'credito' ? 2 : 1;
        $plazo     = $condicion === 2 ? ($data['plazo_credito'] ?? null) : null;

        return [
            'totalNoSuj'          => 0.00,
            'totalExenta'         => 0.00,
            'totalGravada'        => $totalGravada,
            'subTotalVentas'      => $subTotal,
            'descuNoSuj'          => 0.00,
            'descuExenta'         => 0.00,
            'descuGravada'        => 0.00,
            'porcentajeDescuento' => 0.00,
            'totalDescu'          => 0.00,
            'tributos'            => [
                [
                    'codigo'      => '20',
                    'descripcion' => 'Impuesto al Valor Agregado 13%',
                    'valor'       => $totalIva,
                ],
            ],
            'subTotal'             => $subTotal,
            'ivaPerci1'            => 0.00,
            'ivaRete1'             => 0.00,
            'reteRenta'            => 0.00,
            'montoTotalOperacion'  => $totalOp,
            'totalLetras'          => $this->numeroALetras($totalOp),
            'totalIva'             => $totalIva,
            'saldoFavor'           => 0.00,
            'condicionOperacion'   => $condicion,
            'pagos'                => [
                [
                    'codigo'     => '01',
                    'montoPago'  => $totalOp,
                    'referencia' => null,
                    'plazo'      => $plazo ? (string)$plazo : null,
                    'periodo'    => $plazo ? 'días' : null,
                ],
            ],
            'numPagoElectronico' => null,
        ];
    }

    // ─────────────────────────────────────────────
    //  CORRELATIVO
    // ─────────────────────────────────────────────

    public function siguienteNumeroControl(string $tipoDte): string
    {
        $e      = $this->emisor;
        $prefijo = "DTE-{$tipoDte}-{$e->cod_estable_mh}{$e->cod_punto_venta_mh}-";

        $db  = \Config\Database::connect();
        $row = $db->table('facturas_head')
            ->where('emitido', 1)
            ->where('tipo_dte', $tipoDte)
            ->like('numero_control', $prefijo, 'after')
            ->orderBy('numero_control', 'DESC')
            ->limit(1)
            ->get()->getRow();

        $siguiente = 1;
        if ($row) {
            // El número secuencial es la última parte separada por '-'
            $parts     = explode('-', $row->numero_control);
            $siguiente = ((int) end($parts)) + 1;
        }

        return $prefijo . str_pad($siguiente, 15, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────
    //  UTILIDADES
    // ─────────────────────────────────────────────

    private function generarUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    private function mapTipoDocumento(?string $tipo): ?string
    {
        return match (strtoupper((string)$tipo)) {
            'DUI'   => '13',
            'NIT'   => '36',
            'PASAP' => '03',
            'CARNÉ RESIDENTE' => '02',
            default => '13',
        };
    }

    public function numeroALetras(float $numero): string
    {
        $entero   = (int) $numero;
        $centavos = (int) round(($numero - $entero) * 100);

        $texto = $this->enteroALetras($entero);

        return strtoupper($texto) . ' ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100 DÓLARES';
    }

    private function enteroALetras(int $n): string
    {
        if ($n === 0) return 'CERO';

        $unidades  = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
                      'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS',
                      'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $decenas   = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas  = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
                      'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        $resultado = '';

        if ($n >= 1000000) {
            $mill = intdiv($n, 1000000);
            $resultado .= ($mill === 1 ? 'UN MILLÓN ' : $this->enteroALetras($mill) . ' MILLONES ');
            $n %= 1000000;
        }

        if ($n >= 1000) {
            $miles = intdiv($n, 1000);
            $resultado .= ($miles === 1 ? 'MIL ' : $this->enteroALetras($miles) . ' MIL ');
            $n %= 1000;
        }

        if ($n >= 100) {
            if ($n === 100) {
                $resultado .= 'CIEN ';
            } else {
                $resultado .= $centenas[intdiv($n, 100)] . ' ';
            }
            $n %= 100;
        }

        if ($n >= 20) {
            $dec = intdiv($n, 10);
            $uni = $n % 10;
            if ($uni === 0) {
                $resultado .= $decenas[$dec] . ' ';
            } elseif ($dec === 2) {
                $resultado .= 'VEINTI' . strtolower($unidades[$uni]) . ' ';
            } else {
                $resultado .= $decenas[$dec] . ' Y ' . $unidades[$uni] . ' ';
            }
        } elseif ($n > 0) {
            $resultado .= $unidades[$n] . ' ';
        }

        return trim($resultado);
    }
}
