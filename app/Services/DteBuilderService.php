<?php

namespace App\Services;

use App\Models\EmisorModel;
use App\Models\ClienteModel;
use App\Models\ProductoModel;

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
        $codigoGen  = array_key_exists('codigo_generacion', $data)
            ? $data['codigo_generacion']
            : $this->generarUUID();
        $numControl = $this->siguienteNumeroControl('01');
        $items      = $data['items'] ?? [];

        $cuerpo    = $this->buildCuerpo($items, '01');
        $resumen   = $this->buildResumenFactura($cuerpo, $data);

        $cliente   = (new ClienteModel())->find($data['cliente_id']);

        return [
            'identificacion' => [
                'version'          => 1,
                'ambiente'         => $ambiente,
                'tipoDte'          => '01',
                'numeroControl'    => $numControl,
                'codigoGeneracion' => $codigoGen,
                'selloRecibido'     => null,
                'tipoModelo'       => 1,
                'tipoOperacion'    => 1,
                'tipoContingencia' => null,
                'motivoContin'     => null,
                'fecEmi'           => $data['fecha_emision'],
                'horEmi'           => $data['hora_emision'],
                'tipoMoneda'       => 'USD',
            ],
            'documentoRelacionado' => null,
            'emisor'               => $this->buildEmisor(),
            'receptor'             => $this->buildReceptorFactura($cliente),
            'otrosDocumentos'      => null,
            'ventaTercero'         => null,
            'cuerpoDocumento'      => $cuerpo,
            'resumen'              => $resumen,
            'extension'            => null,
            'apendice'             => $this->buildApendice($resumen['condicionOperacion']),
            'firmaElectronica'     => null,
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
                'motivoContin'     => null,
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
        $codigoGen  = $data['codigo_generacion'] ?? $this->generarUUID();
        $numControl = $this->siguienteNumeroControl('03');
        $items      = $data['items'] ?? [];

        $cuerpo  = $this->buildCuerpo($items);
        $resumen = $this->buildResumenCCF($cuerpo, $data);

        // Strip internal ivaItem and add tributos ["20"] para el JSON final
        $cuerpoSalida = array_map(function ($item) {
            unset($item['ivaItem']);
            $item['tributos'] = ['20'];
            return $item;
        }, $cuerpo);

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
                'motivoContin'     => null,
                'fecEmi'           => $data['fecha_emision'],
                'horEmi'           => $data['hora_emision'],
                'tipoMoneda'       => 'USD',
                'selloRecibido'    => null,
            ],
            'documentoRelacionado' => null,
            'emisor'               => $this->buildEmisor(),
            'receptor'             => $this->buildReceptorCCF($cliente),
            'otrosDocumentos'      => null,
            'ventaTercero'         => null,
            'cuerpoDocumento'      => $cuerpoSalida,
            'resumen'              => $resumen,
            'extension'            => null,
            'apendice'             => $this->buildApendice($resumen['condicionOperacion']),
            'firmaElectronica'     => null,
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
            'tipoEstablecimiento' => '02',
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

    private function buildReceptorFactura(?object $cliente): ?array
    {
        if (!$cliente) {
            return null;
        }

        $direccion = null;
        if (!empty($cliente->departamento) && !empty($cliente->municipio) && !empty($cliente->direccion)) {
            $direccion = [
                'departamento' => $cliente->departamento,
                'municipio'    => str_pad((string) $cliente->municipio, 2, '0', STR_PAD_LEFT),
                'complemento'  => $cliente->direccion,
            ];
        }

        $tipoDocumento = $this->mapTipoDocumento($cliente->tipo_documento);

        return [
            'tipoDocumento' => $tipoDocumento,
            'numDocumento'  => $cliente->numero_documento ?: null,
            'nrc'           => $tipoDocumento === '36' ? ($cliente->nrc ?: null) : null,
            'nombre'        => $cliente->nombre,
            'codActividad'  => $cliente->cod_actividad ?? null,
            'descActividad' => $cliente->desc_actividad ?? null,
            'direccion'     => $direccion,
            'telefono'      => $cliente->telefono ? preg_replace('/[^0-9]/', '', $cliente->telefono) : null,
            'correo'        => $cliente->correo ?: null,
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

    private function buildCuerpo(array $items, string $tipoDte = '03'): array
    {
        $cuerpo = [];

        foreach ($items as $i => $item) {
            $cantidad    = round(max((float)($item['cantidad'] ?? 1), 0.00000001), 8);
            $precioUni   = round(max((float)($item['precio_uni'] ?? 0), 0), 8);
            $descuento   = round(max((float)($item['descuento'] ?? 0), 0), 8);
            $tipoItem    = (int)($item['tipo_item'] ?? 1);
            $tipoItem    = in_array($tipoItem, [1, 2, 3, 4], true) ? $tipoItem : 1;
            $descripcion = trim((string)($item['descripcion'] ?? ''));
            $descripcion = preg_replace("/\r\n|\r|\n/", "\r\n", $descripcion);
            $descripcion = mb_substr($descripcion ?: 'Producto/Servicio', 0, 1000);
            $codigo      = trim((string)($item['codigo'] ?? ''));
            if ($codigo === '' && !empty($item['producto_id'])) {
                $producto = (new ProductoModel())->find($item['producto_id']);
                $codigo = $producto->codigo ?? '';
            }
            $codigo      = $codigo !== '' ? mb_substr($codigo, 0, 25) : null;
            $codTributo  = $tipoItem === 4 ? (string)($item['cod_tributo'] ?? 'A8') : null;
            $uniMedida   = $tipoItem === 4 ? 99 : (int)($item['uni_medida'] ?? 59);

            $ventaBruta   = round($cantidad * $precioUni, 8);
            $ventaGravada = round($ventaBruta - $descuento, 8);
            if ($ventaGravada < 0) $ventaGravada = 0.00;

            $ivaItem = $tipoDte === '01'
                ? round($ventaGravada - ($ventaGravada / 1.13), 8)
                : round($ventaGravada * 0.13, 8);

            $cuerpo[] = [
                'numItem'         => $i + 1,
                'tipoItem'        => $tipoItem,
                'numeroDocumento' => null,
                'codigo'          => $codigo,
                'codTributo'      => $codTributo,
                'descripcion'     => $descripcion,
                'cantidad'        => $cantidad,
                'uniMedida'       => $uniMedida,
                'precioUni'       => $precioUni,
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
        $totalOp      = $totalGravada;

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
            'tributos'            => null,
            'subTotal'             => $subTotal,
            'ivaRete1'             => 0.00,
            'reteRenta'            => 0.00,
            'montoTotalOperacion'  => $totalOp,
            'totalNoGravado'       => 0.00,
            'totalPagar'           => $totalOp,
            'totalLetras'          => $this->numeroALetras($totalOp),
            'totalIva'             => $totalIva,
            'saldoFavor'           => 0.00,
            'condicionOperacion'   => $condicion,
            'pagos'                => null,
            'numPagoElectronico' => null,
        ];
    }

    private function buildApendice(int $condicionOperacion): array
    {
        return [
            [
                'campo'    => 'sucursal',
                'etiqueta' => 'Sucursal',
                'valor'    => 'Oficinas Centrales',
            ],
            [
                'campo'    => 'condicion_operacion',
                'etiqueta' => 'Condicion de la operacion',
                'valor'    => $condicionOperacion === 2 ? 'Credito' : 'Contado',
            ],
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

        $pagos = null;
        if ($condicion === 2) {
            $plazo = $data['plazo_credito'] ?? null;
            $pagos = [
                [
                    'codigo'     => '01',
                    'montoPago'  => $totalOp,
                    'referencia' => null,
                    'plazo'      => $plazo ? (string)$plazo : null,
                    'periodo'    => $plazo ? 'días' : null,
                ],
            ];
        }

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
            'subTotal'            => $subTotal,
            'ivaPerci1'           => 0.00,
            'ivaRete1'            => 0.00,
            'reteRenta'           => 0.00,
            'montoTotalOperacion' => $totalOp,
            'totalNoGravado'      => 0.00,
            'totalPagar'          => $totalOp,
            'totalLetras'         => $this->numeroALetras($totalOp),
            'saldoFavor'          => 0.00,
            'condicionOperacion'  => $condicion,
            'pagos'               => $pagos,
            'numPagoElectronico'  => null,
        ];
    }

    // ─────────────────────────────────────────────
    //  CORRELATIVO
    // ─────────────────────────────────────────────

    public function siguienteNumeroControl(string $tipoDte): string
    {
        $e       = $this->emisor;
        $serie   = "{$e->cod_estable_mh}{$e->cod_punto_venta_mh}";
        $serie   = preg_replace('/[^A-Z0-9]/', '', strtoupper($serie));
        $prefijo = "DTE-{$tipoDte}-{$serie}-";
        $ambiente = env('hacienda.env', '00');
        $anioDte = (new \DateTimeImmutable('now', new \DateTimeZone('America/El_Salvador')))->format('y');

        $db  = \Config\Database::connect();
        $row = $db->table('facturas_head')
            ->where('ambiente', $ambiente)
            ->where('tipo_dte', $tipoDte)
            ->where("numero_control REGEXP '^DTE-{$tipoDte}-{$serie}-{$anioDte}[0-9]{13}$'", null, false)
            ->orderBy("CAST(SUBSTRING_INDEX(numero_control, '-', -1) AS UNSIGNED)", 'DESC', false)
            ->limit(1)
            ->get()->getRow();

        $siguiente = 1;
        if ($row) {
            // El número secuencial es la última parte separada por '-'
            $parts       = explode('-', $row->numero_control);
            $correlativo = (string) end($parts);
            $siguiente   = ((int) substr($correlativo, 2)) + 1;
        }

        return $prefijo . $anioDte . str_pad($siguiente, 13, '0', STR_PAD_LEFT);
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
            '13'    => '13',
            '36'    => '36',
            '02'    => '02',
            '03'    => '03',
            '37'    => '37',
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

        return 'Son ' . strtoupper($texto) . ' CON ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100 USD';
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
