<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans; font-size: 9px; color: #333; }
h3 { margin-bottom: 4px; font-size: 12px; }
.header-info { margin-bottom: 10px; font-size: 8px; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 0.5px solid #999; padding: 4px 5px; }
th { background: #1f4e79; color: #fff; font-weight: bold; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.totales { background: #e2efda; font-weight: bold; }
.totales td { border-top: 2px solid #548235; }
@page { margin-top: 55px; margin-bottom: 50px; margin-left: 30px; margin-right: 30px; }
</style>
</head>
<body>

<h3>COMPRAS PERCIBIDAS — AGRUPADO POR PRODUCTO</h3>

<div class="header-info">
    <strong>Período:</strong> <?= date('d/m/Y', strtotime($desde)) ?> al <?= date('d/m/Y', strtotime($hasta)) ?>
    &nbsp;&nbsp;
    <?php if (!empty($proveedor)): ?>
        <strong>Proveedor:</strong> <?= esc($proveedor->nombre) ?> &nbsp;&nbsp;
    <?php endif; ?>
    <strong>Generado:</strong> <?= esc($generado_en) ?>
</div>

<table>
    <thead>
        <tr>
            <th width="10%" class="text-center">Código</th>
            <th width="42%">Descripción</th>
            <th width="8%"  class="text-right">Cant.</th>
            <th width="10%" class="text-right">P. Prom.</th>
            <th width="12%" class="text-right">Total Base</th>
            <th width="9%"  class="text-right">IVA 13%</th>
            <th width="9%"  class="text-right">Total c/IVA</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $tCant = 0; $tBase = 0; $tIva = 0; $tTotal = 0;
    foreach ($productos as $p):
        $base  = (float)$p->total_base;
        $cant  = (float)$p->cantidad_total;
        $prom  = $cant > 0 ? $base / $cant : 0;
        $iva   = round($base * 0.13, 2);
        $total = $base + $iva;
        $tCant  += $cant; $tBase += $base; $tIva += $iva; $tTotal += $total;
    ?>
        <tr>
            <td class="text-center"><?= esc($p->codigo) ?></td>
            <td><?= esc($p->descripcion) ?></td>
            <td class="text-right"><?= number_format($cant, 2) ?></td>
            <td class="text-right">$ <?= number_format($prom, 4) ?></td>
            <td class="text-right">$ <?= number_format($base,  2) ?></td>
            <td class="text-right">$ <?= number_format($iva,   2) ?></td>
            <td class="text-right"><strong>$ <?= number_format($total, 2) ?></strong></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="totales">
            <td colspan="2" class="text-right">TOTALES</td>
            <td class="text-right"><?= number_format($tCant, 2) ?></td>
            <td></td>
            <td class="text-right">$ <?= number_format($tBase,  2) ?></td>
            <td class="text-right">$ <?= number_format($tIva,   2) ?></td>
            <td class="text-right">$ <?= number_format($tTotal, 2) ?></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
