<?php

/**
 * HELPER: Partidas Contables de Ventas (CCF / FAC)
 *
 * Calcula el desglose tributario de una venta y construye las líneas
 * del asiento contable listas para enviarse al endpoint store().
 *
 * CÓMO CONECTARLO AL FLUJO DE VENTAS:
 *   1. Cuando se guarda un CCF o FAC, llama cont_desglose_venta() para
 *      obtener los montos descompuestos.
 *   2. Llama cont_lineas_venta() para obtener el arreglo de líneas.
 *   3. Envía ese arreglo al endpoint POST contabilidad/asientos/store
 *      junto con periodo_id, fecha, descripcion y referencia.
 *      → O usa el endpoint GET contabilidad/asientos/plantilla-venta
 *        para obtener el JSON completo ya armado (modo AJAX).
 *
 * CUENTAS REQUERIDAS EN LA CONFIGURACIÓN CONTABLE:
 *   · cuenta_cxc_id              → Clientes / Cuentas por Cobrar
 *   · cuenta_ventas_id           → Ingresos por Ventas
 *   · cuenta_iva_debito_id       → IVA Débito Fiscal   ← NUEVO
 *   · cuenta_retencion_cobrar_id → Retención 1% por Cobrar ← NUEVO
 *
 *   Los dos últimos se agregan con la migración
 *   2026-05-08-000001_AddCuentasVentaToContConfiguracion.php
 *   Ve a Contabilidad → Configuración para asignarlos.
 */

// ─────────────────────────────────────────────────────────────────────────────
// TASAS FISCALES — El Salvador
// Actualiza aquí si Hacienda cambia las tasas.
// ─────────────────────────────────────────────────────────────────────────────
if (!defined('CONT_IVA_RATE'))   define('CONT_IVA_RATE',   0.13); // 13 % IVA
if (!defined('CONT_RETEN_RATE')) define('CONT_RETEN_RATE', 0.01); // 1 % retención

// ─────────────────────────────────────────────────────────────────────────────
// cont_desglose_venta()
// ─────────────────────────────────────────────────────────────────────────────

if (!function_exists('cont_desglose_venta')) {
    /**
     * Descompone los montos de una venta en sus componentes tributarios.
     *
     * ┌─────────────────────────────────────────────────────────────────┐
     * │  CCF (Crédito Fiscal — tipo 03)                                 │
     * │  El documento trae la venta SIN IVA (base imponible).           │
     * │  → Pasa $monto = venta_sin_iva                                  │
     * │  → El IVA se calcula: monto × 0.13                             │
     * │  → venta_con_iva = monto + iva                                  │
     * ├─────────────────────────────────────────────────────────────────┤
     * │  FAC (Factura Consumidor Final — tipo 01)                       │
     * │  El precio al cliente ya incluye el IVA.                        │
     * │  → Pasa $monto = venta_con_iva (precio total del documento)     │
     * │  → Se descompone: venta_sin_iva = monto ÷ 1.13                 │
     * │  → iva = monto − venta_sin_iva                                  │
     * └─────────────────────────────────────────────────────────────────┘
     *
     * La retención (1 % sobre venta_sin_iva) ya debe venir calculada
     * desde el formulario o el documento DTE. Pasa 0.0 si no aplica.
     *
     * @param string $tipoDte   'CCF' | 'FAC'  (o '03' | '01' como código DTE)
     * @param float  $monto     CCF → venta sin IVA  |  FAC → total con IVA
     * @param float  $retencion Monto de retención ya calculado (0 si no aplica)
     *
     * @return array{
     *   tipo_dte: string,
     *   venta_sin_iva: float,
     *   iva: float,
     *   venta_con_iva: float,
     *   retencion: float,
     *   valor_a_recibir: float
     * }
     *
     * @throws \InvalidArgumentException si el tipo de documento no es válido
     */
    function cont_desglose_venta(string $tipoDte, float $monto, float $retencion = 0.0): array
    {
        switch (strtoupper($tipoDte)) {

            // ── CCF: el emisor declara la base (sin IVA) ──────────────────
            case 'CCF':
            case '03':
                $ventaSinIva = round($monto, 2);
                $iva         = round($ventaSinIva * CONT_IVA_RATE, 2);
                $ventaConIva = round($ventaSinIva + $iva, 2);
                $sigla       = 'CCF';
                break;

            // ── FAC: precio al público con IVA incluido ───────────────────
            case 'FAC':
            case '01':
                $ventaConIva = round($monto, 2);
                // Dividir entre 1.13 para extraer la base imponible
                $ventaSinIva = round($ventaConIva / (1 + CONT_IVA_RATE), 2);
                // El IVA es el residuo (evita errores de redondeo por doble cálculo)
                $iva         = round($ventaConIva - $ventaSinIva, 2);
                $sigla       = 'FAC';
                break;

            default:
                throw new \InvalidArgumentException(
                    "cont_desglose_venta: tipo_dte '{$tipoDte}' no reconocido. Use 'CCF' o 'FAC'."
                );
        }

        // La retención reduce lo que el cliente paga de contado.
        // valor_a_recibir = lo que ingresa a caja / banco hoy.
        $valorARecibir = round($ventaConIva - $retencion, 2);

        return [
            'tipo_dte'        => $sigla,
            'venta_sin_iva'   => $ventaSinIva,   // base imponible
            'iva'             => $iva,            // 13 % IVA
            'venta_con_iva'   => $ventaConIva,   // total del documento
            'retencion'       => $retencion,      // 1 % retenido (0 si no aplica)
            'valor_a_recibir' => $valorARecibir, // efectivo que ingresa
        ];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// _cont_cuentas_venta()   [función interna — no llames esto directamente]
// ─────────────────────────────────────────────────────────────────────────────

if (!function_exists('_cont_cuentas_venta')) {
    /**
     * Lee las cuentas contables de ventas desde cont_configuracion.
     *
     * ┌─────────────────────────────────────────────────────────────────────┐
     * │  QUÉ PONER EN CADA CUENTA (Contabilidad → Configuración)            │
     * │                                                                     │
     * │  cuenta_cxc_id              → La cuenta de activo corriente         │
     * │    donde se registra lo que el cliente nos debe pagar.              │
     * │    Ejemplos de nombre: "Clientes", "Deudores Comerciales",          │
     * │    "Cuentas por Cobrar — Clientes".                                 │
     * │    Si la venta es al contado, puede ser Caja o Banco en su lugar;  │
     * │    en ese caso ajusta cont_lineas_venta() según corresponda.        │
     * │                                                                     │
     * │  cuenta_ventas_id           → Cuenta de ingresos (resultado).       │
     * │    Ejemplos: "Ingresos por Ventas", "Ventas Gravadas".              │
     * │    Si manejas cuentas separadas por tipo (CCF vs FAC), debes        │
     * │    agregar cuenta_ventas_ccf_id y cuenta_ventas_fac_id a la tabla   │
     * │    (nueva migración) y ajustar esta función para leerlas.           │
     * │                                                                     │
     * │  cuenta_iva_debito_id       → Cuenta de pasivo corriente.           │
     * │    El IVA que cobramos al cliente lo debemos entregar a Hacienda.  │
     * │    Ejemplos: "IVA Débito Fiscal", "Impuesto IVA por Pagar".        │
     * │    Se liquida mensualmente contra IVA Crédito Fiscal de compras.   │
     * │                                                                     │
     * │  cuenta_retencion_cobrar_id → Cuenta de activo corriente.           │
     * │    El 1 % que el cliente retiene se registra como un crédito       │
     * │    fiscal que recuperamos vía declaración de IVA.                   │
     * │    Ejemplos: "Retención IVA 1% por Cobrar", "Crédito Fiscal        │
     * │    Retenido por Clientes".                                          │
     * └─────────────────────────────────────────────────────────────────────┘
     *
     * @return array|null  null si no hay configuración guardada
     */
    function _cont_cuentas_venta(): ?array
    {
        $cfg = (new \App\Models\ContConfiguracionModel())->getConfig();

        // Si la tabla está vacía, devolvemos null para que cont_lineas_venta()
        // lo informe como error antes de intentar insertar con cuenta_id = null.
        if (empty((array)$cfg)) {
            return null;
        }

        return [
            // ── DÉBITOS ─────────────────────────────────────────────────
            // Lo que el cliente nos debe pagar (activo).
            // Campo en cont_configuracion: cuenta_cxc_id
            'cxc'              => $cfg->cuenta_cxc_id              ?? null,

            // Retención 1% retenida por el cliente (activo recuperable).
            // Campo en cont_configuracion: cuenta_retencion_cobrar_id
            // ← Asígnalo en Contabilidad → Configuración después de migrar.
            'retencion_cobrar' => $cfg->cuenta_retencion_cobrar_id ?? null,

            // ── CRÉDITOS ────────────────────────────────────────────────
            // Ingreso por venta (base sin IVA).
            // Campo en cont_configuracion: cuenta_ventas_id
            'ventas'           => $cfg->cuenta_ventas_id           ?? null,

            // IVA que debemos a Hacienda (pasivo).
            // Campo en cont_configuracion: cuenta_iva_debito_id
            // ← Asígnalo en Contabilidad → Configuración después de migrar.
            'iva_debito'       => $cfg->cuenta_iva_debito_id       ?? null,
        ];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// cont_lineas_venta()
// ─────────────────────────────────────────────────────────────────────────────

if (!function_exists('cont_lineas_venta')) {
    /**
     * Construye las líneas del asiento contable para una venta.
     *
     * Recibe el arreglo de cont_desglose_venta() y devuelve las líneas
     * en el formato exacto que espera ContAsientosController::store().
     *
     * ESTRUCTURA DEL ASIENTO GENERADO:
     * ┌────────────────────────────────────────┬──────────┬──────────┐
     * │ Cuenta                                 │  Debe    │  Haber   │
     * ├────────────────────────────────────────┼──────────┼──────────┤
     * │ Clientes / CxC          (cxc)          │ val_rec  │          │
     * │ Retención 1% por Cobrar (ret_cobrar) * │ reten    │          │
     * │ Ingresos por Ventas     (ventas)       │          │ sin_iva  │
     * │ IVA Débito Fiscal       (iva_debito)   │          │ iva      │
     * └────────────────────────────────────────┴──────────┴──────────┘
     *   * Solo aparece si $desglose['retencion'] > 0
     *
     * @param array  $desglose  Resultado de cont_desglose_venta()
     * @param string $ref       Número del documento (ej: "CCF-00001")
     *
     * @return array{
     *   ok: bool,
     *   lineas: array,
     *   totales: array{debe: float, haber: float},
     *   cuadra: bool,
     *   errores: string[]
     * }
     */
    function cont_lineas_venta(array $desglose, string $ref = '', ?int $ventasOverrideId = null, ?int $cxcOverrideId = null): array
    {
        $errores  = [];
        $cuentas  = _cont_cuentas_venta();

        // Sin configuración no podemos continuar
        if ($cuentas === null) {
            return [
                'ok'      => false,
                'lineas'  => [],
                'totales' => ['debe' => 0, 'haber' => 0],
                'cuadra'  => false,
                'errores' => ['No hay configuración contable guardada. Ve a Contabilidad → Configuración.'],
            ];
        }

        // Validar cuentas obligatorias; los overrides eximen la cuenta de configuración
        $requeridas = ['iva_debito' => 'IVA Débito Fiscal (cuenta_iva_debito_id)'];
        if ($cxcOverrideId === null) {
            $requeridas['cxc']    = 'Clientes / CxC (cuenta_cxc_id)';
        }
        if ($ventasOverrideId === null) {
            $requeridas['ventas'] = 'Ingresos por Ventas (cuenta_ventas_id)';
        }
        if ($desglose['retencion'] > 0) {
            $requeridas['retencion_cobrar'] = 'Retención 1% por Cobrar (cuenta_retencion_cobrar_id)';
        }

        foreach ($requeridas as $key => $label) {
            if (empty($cuentas[$key])) {
                $errores[] = "Cuenta sin configurar: {$label}";
            }
        }

        if (!empty($errores)) {
            return [
                'ok'      => false,
                'lineas'  => [],
                'totales' => ['debe' => 0, 'haber' => 0],
                'cuadra'  => false,
                'errores' => $errores,
            ];
        }

        $tipo  = $desglose['tipo_dte'];
        $label = $ref ? " {$ref}" : '';
        $lineas = [];

        // ── DÉBITO 1: Clientes / Cuentas por Cobrar ───────────────────────
        $cuentaCxcId = $cxcOverrideId ?? (int)$cuentas['cxc'];
        $lineas[] = [
            'cuenta_id'   => $cuentaCxcId,
            'descripcion' => "Venta {$tipo}{$label} — valor a recibir",
            'debe'        => $desglose['valor_a_recibir'],
            'haber'       => 0.0,
        ];

        // ── DÉBITO 2: Retención IVA 1% por Cobrar (solo si aplica) ────────
        // El cliente retiene el 1 % de la venta sin IVA y lo entera a Hacienda
        // en nombre del emisor. Para nosotros es un activo: un crédito fiscal
        // que recuperamos declarando el anticipo en la liquidación mensual.
        if ($desglose['retencion'] > 0) {
            $lineas[] = [
                'cuenta_id'   => (int)$cuentas['retencion_cobrar'],
                'descripcion' => "Retención IVA 1% {$tipo}{$label}",
                'debe'        => $desglose['retencion'],
                'haber'       => 0.0,
            ];
        }

        // ── CRÉDITO 1: Ingresos por Ventas ────────────────────────────────
        $cuentaVentasId = $ventasOverrideId ?? (int)$cuentas['ventas'];
        $lineas[] = [
            'cuenta_id'   => $cuentaVentasId,
            'descripcion' => "Ingresos por venta {$tipo}{$label}",
            'debe'        => 0.0,
            'haber'       => $desglose['venta_sin_iva'],
        ];

        // ── CRÉDITO 2: IVA Débito Fiscal ──────────────────────────────────
        // El 13 % de IVA recaudado a nombre del fisco.
        // Es un pasivo: lo debemos declarar y pagar mensualmente a Hacienda,
        // neto del IVA Crédito Fiscal de compras del mismo período.
        $lineas[] = [
            'cuenta_id'   => (int)$cuentas['iva_debito'],
            'descripcion' => "IVA Débito Fiscal 13% {$tipo}{$label}",
            'debe'        => 0.0,
            'haber'       => $desglose['iva'],
        ];

        $totalDebe  = round(array_sum(array_column($lineas, 'debe')), 2);
        $totalHaber = round(array_sum(array_column($lineas, 'haber')), 2);
        $cuadra     = abs($totalDebe - $totalHaber) < 0.01;

        if (!$cuadra) {
            $errores[] = "La partida no cuadra: Debe {$totalDebe} ≠ Haber {$totalHaber}";
        }

        return [
            'ok'      => $cuadra,
            'lineas'  => $lineas,
            'totales' => ['debe' => $totalDebe, 'haber' => $totalHaber],
            'cuadra'  => $cuadra,
            'errores' => $errores,
        ];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// cont_asiento_venta_json()
// ─────────────────────────────────────────────────────────────────────────────

if (!function_exists('cont_asiento_venta_json')) {
    /**
     * Punto de entrada principal: genera el payload JSON completo
     * para crear un asiento de venta directamente en el store().
     *
     * Úsalo desde el controller que maneja CCF / FAC:
     *
     *   $json = cont_asiento_venta_json(
     *       tipoDte   : 'CCF',
     *       monto     : 1000.00,   // venta sin IVA
     *       retencion : 10.00,     // o 0.0 si no aplica
     *       referencia: 'CCF-00001',
     *       periodoId : 5,
     *       fecha     : '2026-05-08',
     *       descripcion: 'Venta CCF cliente XYZ'
     *   );
     *
     *   if ($json['ok']) {
     *       // Envía $json['payload'] al store() o guárdalo directamente
     *   }
     *
     * @param string $tipoDte     'CCF' | 'FAC'
     * @param float  $monto       Base (sin IVA en CCF, total en FAC)
     * @param float  $retencion   Monto de retención (0 si no aplica)
     * @param string $referencia  Número del documento
     * @param int    $periodoId   ID del período contable abierto
     * @param string $fecha       Fecha del documento (Y-m-d)
     * @param string $descripcion Glosa general del asiento
     *
     * @return array{ok: bool, payload: array, desglose: array, errores: string[]}
     */
    function cont_asiento_venta_json(
        string $tipoDte,
        float  $monto,
        float  $retencion        = 0.0,
        string $referencia       = '',
        int    $periodoId        = 0,
        string $fecha            = '',
        string $descripcion      = '',
        ?int   $ventasOverrideId = null,
        ?int   $cxcOverrideId    = null
    ): array {
        // 1. Descomponer montos
        $desglose = cont_desglose_venta($tipoDte, $monto, $retencion);

        // 2. Construir líneas contables
        $resultado = cont_lineas_venta($desglose, $referencia, $ventasOverrideId, $cxcOverrideId);

        if (!$resultado['ok']) {
            return [
                'ok'       => false,
                'payload'  => [],
                'desglose' => $desglose,
                'errores'  => $resultado['errores'],
            ];
        }

        // 3. Armar el payload listo para ContAsientosController::store()
        $payload = [
            // ── Cabecera del asiento ─────────────────────────────────────
            'periodo_id'  => $periodoId,
            'fecha'       => $fecha ?: date('Y-m-d'),
            // La descripción general del asiento (aparece en el libro diario)
            'descripcion' => $descripcion ?: "Venta {$desglose['tipo_dte']} {$referencia}",
            // Tipo de asiento: ajusta si tienes tipos distintos en tu catálogo
            // Valores posibles según ContAsientosHeadModel: 'DIARIO', 'APERTURA',
            // 'AJUSTE', 'CIERRE', 'VENTA', etc. — usa el que corresponda.
            'tipo'        => 'VENTA',
            'referencia'  => $referencia,
            // ── Líneas (partida doble) ───────────────────────────────────
            'lineas'      => $resultado['lineas'],
        ];

        return [
            'ok'       => true,
            'payload'  => $payload,
            'desglose' => $desglose,
            'errores'  => [],
        ];
    }
}
