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
.anulada { color: #999; text-decoration: line-through; }
@page { margin-top: 55px; margin-bottom: 50px; margin-left: 30px; margin-right: 30px; }
</style>
</head>
<body>

<h3>COMPRAS PERCIBIDAS</h3>

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
            <th width="7%"  class="text-center">Fecha</th>
            <th width="28%" >Número Control</th>
            <th width="6%"  class="text-center">Tipo</th>
            <th width="23%" >Proveedor</th>
            <th width="9%"  class="text-right">Base S/IVA</th>
            <th width="8%"  class="text-right">IVA 13%</th>
            <th width="8%"  class="text-right">Retención</th>
            <th width="9%"  class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $tBase = 0; $tIva = 0; $tRet = 0; $tTotal = 0;
    foreach ($compras as $c):
        $esAnulada = (int)$c->anulada === 1;
        $base  = $esAnulada ? 0 : (float)$c->total_gravada;
        $iva   = $esAnulada ? 0 : (float)$c->total_iva;
        $ret   = $esAnulada ? 0 : (float)$c->iva_rete1;
        $total = $esAnulada ? 0 : (float)$c->total_pagar;
        $tBase += $base; $tIva += $iva; $tRet += $ret; $tTotal += $total;
        $cls = $esAnulada ? ' class="anulada"' : '';
    ?>
        <tr>
            <td<?= $cls ?> class="text-center"><?= date('d/m/Y', strtotime($c->fecha_emision)) ?></td>
            <td<?= $cls ?>><?= esc($c->numero_control) ?><?= $esAnulada ? ' [ANULADA]' : '' ?></td>
            <td<?= $cls ?> class="text-center"><?= esc($c->tipo_dte) ?></td>
            <td<?= $cls ?>><?= esc($c->proveedor_nombre) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($base,  2) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($iva,   2) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($ret,   2) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($total, 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="totales">
            <td colspan="4" class="text-right">TOTALES</td>
            <td class="text-right">$ <?= number_format($tBase,  2) ?></td>
            <td class="text-right">$ <?= number_format($tIva,   2) ?></td>
            <td class="text-right">$ <?= number_format($tRet,   2) ?></td>
            <td class="text-right">$ <?= number_format($tTotal, 2) ?></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
