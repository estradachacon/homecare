<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 6.5pt;
    color: #222;
    background: #fff;
}

/* ── CABECERA ──────────────────────────────────────────── */
.report-header {
    display: table;
    width: 100%;
    margin-bottom: 8pt;
    border-bottom: 1.5pt solid #1f4e79;
    padding-bottom: 5pt;
}
.report-header-logo  { display: table-cell; width: 22%; vertical-align: middle; }
.report-header-title { display: table-cell; vertical-align: middle; text-align: center; }
.report-header-meta  { display: table-cell; width: 22%; vertical-align: middle; text-align: right; }
.report-header-logo img { max-height: 22mm; max-width: 42mm; }
.report-title  { font-size: 11pt; font-weight: bold; color: #1f4e79; text-transform: uppercase; letter-spacing: 0.5pt; }
.report-sub    { font-size: 7.5pt; color: #444; margin-top: 2pt; }
.report-gen    { font-size: 6pt; color: #888; margin-top: 1pt; }

/* ── SECCIÓN POR VENDEDOR ──────────────────────────────── */
.vendor-section { page-break-before: always; }
.vendor-section:first-child { page-break-before: avoid; }
.vendor-header {
    background: #1f4e79;
    color: #fff;
    padding: 3pt 6pt;
    font-size: 8pt;
    font-weight: bold;
    margin-bottom: 4pt;
    border-radius: 2pt;
}
.vendor-stats {
    font-size: 6pt;
    color: #555;
    margin-bottom: 4pt;
}

/* ── TABLA ─────────────────────────────────────────────── */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
thead th {
    background: #2e5f9e;
    color: #fff;
    font-size: 6pt;
    font-weight: bold;
    padding: 2pt 2.5pt;
    border: 0.4pt solid #1a3a6b;
    text-align: left;
    vertical-align: bottom;
    white-space: nowrap;
    overflow: hidden;
}
thead th.num { text-align: right; }
tbody td {
    font-size: 6pt;
    padding: 1.5pt 2.5pt;
    border: 0.4pt solid #ddd;
    vertical-align: middle;
    overflow: hidden;
}
tbody td.num { text-align: right; }
tbody td.ctr { text-align: center; }

/* ── ESTADOS ───────────────────────────────────────────── */
.row-facturado td { background: #e8f5e9; }
.row-anulada   td { background: #fde8e8; }
.row-cambiado  td { background: #fff8e1; }
.row-devuelto  td { background: #e8eaf6; }

/* ── TOTALES ───────────────────────────────────────────── */
tfoot tr td {
    background: #ddeedd;
    font-weight: bold;
    font-size: 6.5pt;
    padding: 2pt 2.5pt;
    border-top: 1pt solid #548235;
    border: 0.4pt solid #bbb;
}
tfoot .lbl { text-align: right; }
tfoot .num { text-align: right; }

/* ── BADGE ─────────────────────────────────────────────── */
.badge {
    display: inline-block;
    padding: 0.5pt 3pt;
    border-radius: 2pt;
    font-size: 5.5pt;
    font-weight: bold;
    color: #fff;
}
.badge-ok  { background: #388e3c; }
.badge-err { background: #c62828; }
.badge-war { background: #f57f17; color: #333; }
.badge-inf { background: #1565c0; }
.badge-neu { background: #555; }

/* ── ANCHOS COLUMNAS ───────────────────────────────────── */
col.c-fecha  { width: 6.5%; }
col.c-ne     { width: 5.2%; }
col.c-fpedido{ width: 6.5%; }
col.c-npedido{ width: 5%; }
col.c-ffac   { width: 6.5%; }
col.c-dias   { width: 3.2%; }
col.c-doc    { width: 3.2%; }
col.c-ndoc   { width: 5%; }
col.c-cli    { width: 12%; }
col.c-cod    { width: 5.5%; }
col.c-desc   { width: 14%; }
col.c-qty    { width: 4.5%; }
col.c-precio { width: 7%; }
col.c-com    { width: 7%; }
col.c-estado { width: 8.4%; }
</style>
</head>
<body>

<!-- ── CABECERA GLOBAL ───────────────────────────────────────── -->
<div class="report-header">
    <div class="report-header-logo">
        <?php if ($logoBase64): ?>
        <img src="<?= $logoBase64 ?>">
        <?php endif; ?>
    </div>
    <div class="report-header-title">
        <div class="report-title">Reporte Notas de Envío por Producto</div>
        <div class="report-sub">
            Del <?= date('d/m/Y', strtotime($fechaDesde)) ?>
            al <?= date('d/m/Y', strtotime($fechaHasta)) ?>
            &nbsp;·&nbsp; Comisión: <?= number_format($comision, 1) ?>%
            <?php if ($soloVendedor && !empty($grupos)): $vn = array_key_first($grupos); ?>
            &nbsp;·&nbsp; Vendedor: <strong><?= esc($vn) ?></strong>
            <?php endif; ?>
        </div>
        <div class="report-gen">Generado: <?= date('d/m/Y H:i') ?></div>
    </div>
    <div class="report-header-meta">
        <strong style="font-size:8pt;color:#1f4e79;"><?= esc($companyName) ?></strong>
    </div>
</div>

<?php
$siglasFn = function(object $l) use ($siglas): string {
    return $l->tipo_dte ? ($siglas[$l->tipo_dte] ?? $l->tipo_dte) : '';
};
$estadoFn = function(object $l, bool $anu, float $cantFact): array {
    if ($anu) return ['NE ANULADA', 'badge-err', 'row-anulada'];
    if ($l->factura_id && $cantFact > 0) return ['Facturado', 'badge-ok', 'row-facturado'];
    if ($l->factura_id && !$cantFact && !empty($l->pedido_id)) return ['Facturado vía NP', 'badge-ok', 'row-facturado'];
    if (!empty($l->numero_nueva_ne)) return ['Cambio → NE ' . $l->numero_nueva_ne, 'badge-war', 'row-cambiado'];
    if ((float)($l->cantidad_devuelta ?? 0) > 0) return ['Devuelto', 'badge-inf', 'row-devuelto'];
    if ((float)($l->cantidad_stock_vendedor ?? 0) > 0) return ['Stock vendedor', 'badge-neu', ''];
    if ($l->ne_estado === 'cerrada') return ['Cerrada s/factura', 'badge-neu', ''];
    return ['Pendiente', 'badge-neu', ''];
};

$isFirst = true;
foreach ($grupos as $nombreVendedor => $filas):
    $totalP = 0.0; $totalC = 0.0;
    foreach ($filas as $l) {
        $anu = ($l->ne_anulada == 1 || $l->ne_estado === 'anulada');
        $qty = (float)($l->cantidad_facturada ?? 0);
        $pFac = (float)($l->precio_factura ?? 0);
        if (!$anu && $qty > 0 && $pFac > 0 && $l->factura_id) {
            $p = $pFac * $qty; $totalP += $p; $totalC += $p * ($comision / 100);
        }
    }
?>
<div class="vendor-section<?= $isFirst ? '' : '' ?>">

<?php if (!$soloVendedor): ?>
<div class="vendor-header">
    <?= esc($nombreVendedor) ?>
    &nbsp;&nbsp;
    <span style="font-weight:normal;font-size:6.5pt;">
        <?= count($filas) ?> línea(s)
        &nbsp;·&nbsp; Total facturado: $<?= number_format($totalP, 2) ?>
        &nbsp;·&nbsp; Comisión: $<?= number_format($totalC, 2) ?>
    </span>
</div>
<?php endif; ?>

<table>
<colgroup>
    <col class="c-fecha"><col class="c-ne"><col class="c-fpedido"><col class="c-npedido">
    <col class="c-ffac"><col class="c-dias"><col class="c-doc"><col class="c-ndoc">
    <col class="c-cli"><col class="c-cod"><col class="c-desc">
    <col class="c-qty"><col class="c-precio"><col class="c-com"><col class="c-estado">
</colgroup>
<thead>
    <tr>
        <th>Fecha NE</th>
        <th>Nº NE</th>
        <th>F. Pedido</th>
        <th>Nº NP</th>
        <th>F. Factura</th>
        <th class="num">Días</th>
        <th>Doc</th>
        <th>Nº Doc</th>
        <th>Cliente Facturado</th>
        <th>Código</th>
        <th>Descripción</th>
        <th class="num">Cant.</th>
        <th class="num">Precio s/IVA</th>
        <th class="num">Com. <?= number_format($comision, 1) ?>%</th>
        <th>Estado</th>
    </tr>
</thead>
<tbody>
<?php foreach ($filas as $l):
    $anu      = ($l->ne_anulada == 1 || $l->ne_estado === 'anulada');
    $cantFact = (float)($l->cantidad_facturada ?? 0);
    $pFac     = (float)($l->precio_factura ?? 0);
    $precio   = 0.0; $comVal = 0.0;
    if (!$anu && $cantFact > 0 && $pFac > 0 && $l->factura_id) {
        $precio = $pFac * $cantFact;
        $comVal = $precio * ($comision / 100);
    }
    $diasNeFac = '';
    if (!$anu && $l->fecha_factura && $l->fecha_ne) {
        $d = (int)round((strtotime($l->fecha_factura) - strtotime($l->fecha_ne)) / 86400);
        $diasNeFac = $d . 'd';
    }
    [$etiqueta, $badgeCls, $rowCls] = $estadoFn($l, $anu, $cantFact);
    $docSigla = $siglasFn($l);
    $docNum   = $l->numero_control ? substr($l->numero_control, -6) : '';
?>
<tr class="<?= $rowCls ?>">
    <td><?= $l->fecha_ne    ? date('d/m/Y', strtotime($l->fecha_ne))    : '—' ?></td>
    <td style="font-weight:bold"><?= esc($l->numero_ne) ?></td>
    <td><?= $l->fecha_pedido ? date('d/m/Y', strtotime($l->fecha_pedido)) : '—' ?></td>
    <td><?= esc($l->numero_pedido ?? '—') ?></td>
    <td><?= $l->fecha_factura ? date('d/m/Y', strtotime($l->fecha_factura)) : '—' ?></td>
    <td class="num"><?= $diasNeFac ?: '—' ?></td>
    <td class="ctr"><?= esc($docSigla) ?: '—' ?></td>
    <td><?= esc($docNum) ?: '—' ?></td>
    <td><?= esc($l->cliente_facturado ?? '—') ?></td>
    <td style="font-family:monospace"><?= esc($l->producto_codigo) ?></td>
    <td><?= esc($l->producto_descripcion) ?></td>
    <td class="num"><?= $cantFact > 0 ? number_format($cantFact, 2) : '—' ?></td>
    <td class="num"><?= $precio > 0 ? '$ ' . number_format($precio, 2) : '—' ?></td>
    <td class="num"><?= $comVal > 0 ? '$ ' . number_format($comVal, 2) : '—' ?></td>
    <td><span class="badge <?= $badgeCls ?>"><?= esc($etiqueta) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot>
    <tr>
        <td colspan="11" class="lbl">TOTALES</td>
        <td class="num"></td>
        <td class="num">$ <?= number_format($totalP, 2) ?></td>
        <td class="num">$ <?= number_format($totalC, 2) ?></td>
        <td></td>
    </tr>
</tfoot>
</table>
</div>
<?php $isFirst = false; endforeach; ?>

</body>
</html>
