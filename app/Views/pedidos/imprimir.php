<?php
if (!function_exists('_pedNumLetras')) {
    function _pedNumLetras(int $n): string {
        if ($n === 0) return 'CERO';
        $u = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE',
              'DIEZ','ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE',
              'DIECIOCHO','DIECINUEVE','VEINTE'];
        $d = ['','','VEINTI','TREINTA','CUARENTA','CINCUENTA','SESENTA','SETENTA','OCHENTA','NOVENTA'];
        $c = ['','CIENTO','DOSCIENTOS','TRESCIENTOS','CUATROCIENTOS','QUINIENTOS',
              'SEISCIENTOS','SETECIENTOS','OCHOCIENTOS','NOVECIENTOS'];
        if ($n <= 20) return $u[$n];
        if ($n < 30)  return $d[2] . ($n > 20 ? $u[$n - 20] : '');
        if ($n < 100) return $d[intdiv($n,10)] . ($n % 10 ? ' Y ' . $u[$n % 10] : '');
        if ($n === 100) return 'CIEN';
        if ($n < 1000) return $c[intdiv($n,100)] . ($n % 100 ? ' ' . _pedNumLetras($n % 100) : '');
        if ($n < 2000) return 'MIL' . ($n % 1000 ? ' ' . _pedNumLetras($n % 1000) : '');
        if ($n < 1000000) { $m = intdiv($n,1000); $r = $n % 1000; return _pedNumLetras($m) . ' MIL' . ($r ? ' ' . _pedNumLetras($r) : ''); }
        $m = intdiv($n,1000000); $r = $n % 1000000;
        return _pedNumLetras($m) . ($m === 1 ? ' MILLÓN' : ' MILLONES') . ($r ? ' ' . _pedNumLetras($r) : '');
    }
    function pedMontoLetras(float $monto): string {
        $e = (int)floor(abs($monto));
        $c = (int)round((abs($monto) - $e) * 100);
        return _pedNumLetras($e) . ' CON ' . str_pad($c, 2, '0', STR_PAD_LEFT) . '/100 USD';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Pedido <?= esc($pedido->numero) ?></title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 10px;
        color: #000;
        background: #fff;

        /* Mejora la reproducción de colores y negros al imprimir */
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* ── Simulación de hoja en pantalla ─────────── */
    @media screen {
        body {
            background: #909090;
            padding: 16px;
        }

        .page-wrapper {
            background: #fff;
            width: 215.9mm;
            min-height: 279.4mm;
            margin: 0 auto;
            padding: 8mm 12mm;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .45);
        }
    }

    /* ── Cada copia = exactamente mitad de la hoja ── */
    .copia {
        height: 130mm;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* ── Separador de corte ─────────────────────── */
    .separator {
        height: 3mm;
        display: flex;
        align-items: center;
        gap: 5px;
        color: #000;
        font-size: 8px;
        font-weight: 500;
    }

    .separator::before,
    .separator::after {
        content: '';
        flex: 1;
        border-top: 1.5px dashed #000;
    }

    /* ── Etiqueta de tipo de copia ──────────────── */
    .tipo-copia {
        flex-shrink: 0;
        text-align: center;
        font-size: 7.5px;
        font-weight: bold;
        text-transform: uppercase;
        color: #000;
        letter-spacing: .9px;
        border: 1px dashed #000;
        padding: 1.5px 0;
        margin-bottom: 3px;
    }

    /* ── Header: empresa | Nº pedido | estados ──── */
    .hdr {
        flex-shrink: 0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        border-bottom: 1.5px solid #000;
        padding-bottom: 4px;
        margin-bottom: 5px;
    }

    .hdr-empresa {
        font-size: 12px;
        font-weight: bold;
        line-height: 1.3;
    }

    .hdr-empresa small {
        display: block;
        font-size: 8.5px;
        font-weight: 500;
        color: #000;
        margin-top: 1px;
    }

    .hdr-ne {
        text-align: center;
        flex-shrink: 0;
    }

    .hdr-ne .titulo {
        font-size: 7.5px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #000;
        font-weight: 500;
    }

    .hdr-ne .numero {
        font-size: 15px;
        font-weight: bold;
        border: 1.5px solid #000;
        padding: 2px 10px;
        display: inline-block;
        margin-top: 2px;
    }

    .hdr-meta {
        text-align: right;
        font-size: 8.5px;
        color: #000;
        font-weight: 500;
        flex-shrink: 0;
    }

    .hdr-meta .badges {
        margin-top: 3px;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
    }

    /* ── Badges ─────────────────────────────────── */
    .badge {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 3px;
        font-size: 7.5px;
        font-weight: bold;
        text-transform: uppercase;
        border: 1px solid #000;
        color: #000;
        background: #fff;
    }

    /* ── Info grid ──────────────────────────────── */
    .info-grid {
        flex-shrink: 0;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        border: 1px solid #000;
        margin-bottom: 5px;
    }

    .info-item {
        padding: 3px 6px;
        border-right: 1px solid #000;
    }

    .info-item:nth-child(3n) {
        border-right: none;
    }

    .info-item.row2 {
        border-top: 1px solid #000;
    }

    .info-item label {
        display: block;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        color: #000;
        margin-bottom: 1px;
    }

    .info-item span {
        font-size: 9px;
        font-weight: 500;
        color: #000;
    }

    /* ── Tabla de productos ────────────────────── */
    .tabla-wrap {
        flex-shrink: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
    }

    thead th {
        background: #000;
        color: #fff;
        padding: 2px 5px;
        font-size: 11px;
        font-weight: bold;
        text-align: left;
    }

    tbody td {
        border-bottom: 1px solid #000;
        padding: 2px 5px;
        font-size: 11px;
        font-weight: 500;
        color: #000;
    }

    /* Se mantiene el alternado, pero en tonos muy suaves */
    tbody tr:nth-child(even) {
        background: #f5f5f5;
    }

    /* ── Información de lotes ──────────────────── */
    .fila-lotes td {
        background: #fff !important;
        border-bottom: 1px solid #000;
        padding: 2px 5px 4px 10px !important;
    }

    .lote-tag-wrap {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-right: 8px;
        margin-bottom: 2px;
    }

    .lote-tag {
        display: inline-block;
        background: #fff;
        border: 1px solid #000;
        border-radius: 3px;
        padding: 1px 5px;
        font-size: 11px;
        color: #000;
        font-weight: 500;
    }

    .lote-tag strong {
        color: #000;
        font-weight: bold;
    }

    .ne-ref {
        display: inline-block;
        background: #000;
        color: #fff;
        border-radius: 2px;
        padding: 1px 5px;
        font-size: 10.5px;
        font-weight: bold;
        letter-spacing: .2px;
    }

    .tar {
        text-align: right;
    }

    .tac {
        text-align: center;
    }

    /* ── Total ──────────────────────────────────── */
    .totales {
        flex-shrink: 0;
        text-align: right;
        border-top: 1.5px solid #000;
        padding-top: 3px;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: bold;
        color: #000;
    }

    /* ── Observaciones ─────────────────────────── */
    .obs-box {
        flex-shrink: 0;
        border: 1px solid #000;
        padding: 2px 6px;
        margin-bottom: 4px;
        font-size: 13px;
        font-weight: 500;
        color: #000;
    }

    .obs-box label {
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10px;
        color: #000;
    }

    /* ── Firmas ─────────────────────────────────── */
    .firmas {
        margin-top: auto;
        flex-shrink: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        padding-top: 3px;
    }

    .firma-box {
        border-top: 1.5px solid #000;
        padding-top: 4px;
        text-align: center;
        font-size: 10px;
        font-weight: 500;
        color: #000;
        display: flex;
        flex-direction: column;
    }

    .firma-espacio {
        height: 18mm;
    }

    .firma-nombre {
        padding-top: 2px;
    }

    /* ── Impresión ──────────────────────────────── */
    @page {
        size: letter portrait;
        margin: 8mm 12mm;
    }

    @media print {

        body {
            background: #fff;
            color: #000;
        }

        .no-print {
            display: none !important;
        }

        .page-wrapper {
            padding: 0;
            box-shadow: none;
        }

        /*
         * Refuerzo específico para impresión:
         * evita que el navegador convierta elementos
         * importantes en tonos grises demasiado claros.
         */

        .tipo-copia,
        .hdr-empresa,
        .hdr-empresa small,
        .hdr-ne .titulo,
        .hdr-ne .numero,
        .hdr-meta,
        .info-item label,
        .info-item span,
        tbody td,
        .lote-tag,
        .lote-tag strong,
        .totales,
        .obs-box,
        .obs-box label,
        .firma-box,
        .separator {
            color: #000 !important;
        }

        .badge {
            color: #000 !important;
            background: #fff !important;
            border-color: #000 !important;
        }

        .ne-ref {
            color: #fff !important;
            background: #000 !important;
        }

        .fila-lotes td {
            background: #fff !important;
        }

        .separator::before,
        .separator::after {
            border-top-color: #000 !important;
        }
    }
</style>
</head>
<body>

<!-- Barra de acciones (no se imprime) -->
<div class="no-print" style="background:#555; padding:8px 16px; text-align:right; margin-bottom:12px;">
    <button onclick="window.print()"
        style="padding:7px 20px; background:#fff; color:#333; border:none; cursor:pointer; border-radius:4px; font-weight:bold;">
        🖨 Imprimir
    </button>
    <button onclick="window.close()"
        style="padding:7px 18px; background:#888; color:#fff; border:none; cursor:pointer; border-radius:4px; margin-left:8px;">
        Cerrar
    </button>
</div>

<?php
$nombreEmpresa  = setting('company_name') ?? 'HomeCare';
$subtotalLetras = pedMontoLetras((float)$pedido->total);
$logoFile       = setting('logo');
$logoUrl        = ($logoFile && file_exists(FCPATH . 'upload/settings/' . $logoFile))
                    ? base_url('upload/settings/' . $logoFile)
                    : null;

$estClass = match($pedido->estado) { 'facturada' => 'b-facturada', 'anulada' => 'b-anulada', default => 'b-pendiente' };
$estLabel = match($pedido->estado) { 'facturada' => 'Facturada',   'anulada' => 'Anulada',   default => 'Pendiente'   };

$docLabel = ['factura' => 'Factura', 'credito_fiscal' => 'Crédito Fiscal', 'nota_remision' => 'Nota de Remisión'];
$docTexto = $docLabel[$pedido->tipo_documento] ?? $pedido->tipo_documento;

$pagoTexto = $pedido->tipo_pago === 'credito'
    ? 'Crédito — ' . (int)$pedido->dias_credito . ' días'
    : 'Contado';

$copias = ['Original – Cliente', 'Copia – Archivo'];
?>

<div class="page-wrapper">

<?php foreach ($copias as $idx => $tipoCopia): ?>

    <?php if ($idx > 0): ?>
        <div class="separator">&#9986;&nbsp;CORTAR</div>
    <?php endif; ?>

    <div class="copia">

        <!-- Etiqueta de copia -->
        <div class="tipo-copia"><?= $tipoCopia ?></div>

        <!-- Header: empresa | número de pedido | fecha y estado -->
        <div class="hdr">
            <div class="hdr-empresa" style="display:flex;align-items:center;gap:7px;">
                <?php if ($logoUrl): ?>
                    <img src="<?= esc($logoUrl) ?>" alt="logo"
                         style="height:32px;max-width:55px;object-fit:contain;flex-shrink:0;">
                <?php endif; ?>
                <div>
                    <?= esc($nombreEmpresa) ?>
                    <small>Nota de Pedido</small>
                </div>
            </div>
            <div class="hdr-ne">
                <div class="titulo">Nota de Pedido</div>
                <div class="numero"><?= esc($pedido->numero) ?></div>
            </div>
            <div class="hdr-meta">
                Generada: <?= date('d/m/Y H:i', strtotime($pedido->created_at)) ?>
                <div class="badges">
                    <span class="badge <?= $estClass ?>"><?= $estLabel ?></span>
                </div>
            </div>
        </div>

        <!-- Info: cliente / vendedor / fecha + documento / pago / factura -->
        <div class="info-grid">
            <div class="info-item">
                <label>Cliente</label>
                <span><?= esc($pedido->cliente_nombre) ?></span>
            </div>
            <div class="info-item">
                <label>Vendedor</label>
                <span><?= esc($pedido->vendedor_nombre) ?></span>
            </div>
            <div class="info-item">
                <label>Fecha</label>
                <span><?= date('d/m/Y', strtotime($pedido->created_at)) ?></span>
            </div>
            <div class="info-item row2">
                <label>Documento</label>
                <span><?= esc($docTexto) ?></span>
            </div>
            <div class="info-item row2">
                <label>Forma de pago</label>
                <span><?= esc($pagoTexto) ?></span>
            </div>
            <div class="info-item row2">
                <label>Factura asociada</label>
                <span><?= $pedido->factura_numero ? esc($pedido->factura_numero) : '—' ?></span>
            </div>
        </div>

        <!-- Tabla de productos -->
        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:65px">Código</th>
                        <th>Descripción</th>
                        <th class="tac" style="width:52px">Cant.</th>
                        <th class="tar" style="width:76px">P. Unit.</th>
                        <th class="tar" style="width:76px">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $d):
                        $lotes = $lotesPorProducto[$d->producto_codigo] ?? [];
                    ?>
                    <tr>
                        <td><?= esc($d->producto_codigo) ?></td>
                        <td><?= esc($d->producto_nombre) ?></td>
                        <td class="tac"><?= number_format($d->cantidad, 2) ?></td>
                        <td class="tar">$<?= number_format($d->precio_unitario, 2) ?></td>
                        <td class="tar">$<?= number_format($d->subtotal, 2) ?></td>
                    </tr>
                    <?php if (!empty($lotes)): ?>
                    <tr class="fila-lotes">
                        <td colspan="5">
                            <span style="font-size:11px; font-weight:bold; text-transform:uppercase; color:#666; margin-right:4px;">Lotes:</span>
                            <?php foreach ($lotes as $lote): ?>
                                <span class="lote-tag-wrap">
                                    <span class="lote-tag">
                                        <strong><?= esc($lote->numero_lote) ?></strong>
                                        <?php if (!empty($lote->fecha_vencimiento)): ?>· Vence: <?= esc($lote->fecha_vencimiento) ?><?php endif; ?>
                                        · Cant: <?= number_format($lote->cantidad, 2) ?>
                                    </span>
                                    <?php if (!empty($lote->ne_numero)): ?><span class="ne-ref"><?= esc($lote->ne_numero) ?></span><?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totales">
                <?php if ($pedido->iva > 0): ?>
                <div style="display:flex;justify-content:space-between;font-weight:500;font-size:9.5px;">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($pedido->subtotal, 2) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-weight:500;font-size:9.5px;">
                    <span>IVA (13%):</span>
                    <span>$<?= number_format($pedido->iva, 2) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;align-items:baseline;">
                    <span style="font-size:11px;font-weight:normal;color:#555;">Son: <?= esc($subtotalLetras) ?></span>
                    <span>TOTAL:&nbsp;&nbsp;$<?= number_format($pedido->total, 2) ?></span>
                </div>
            </div>

            <?php if ($pedido->notas): ?>
            <div class="obs-box">
                <label>Notas: </label><?= esc($pedido->notas) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Firmas — pegadas al fondo de la copia via margin-top: auto -->
        <div class="firmas">
            <div class="firma-box">
                <div>Entregado por</div>
            </div>
            <div class="firma-box">
                <div>Recibido por</div>
            </div>
            <br>
        </div>

    </div><!-- /.copia -->

<?php endforeach; ?>

</div><!-- /.page-wrapper -->

</body>
</html>
