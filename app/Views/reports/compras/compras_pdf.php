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

<?php
$siglas = [
    '01' => 'FAC',
    '03' => 'CCF',
    '05' => 'NC',
    '06' => 'ND',
    '07' => 'CR',
    '08' => 'LC',
    '09' => 'DL',
    '11' => 'CF',
    '14' => 'FSE',
];
?>

<table>
    <thead>
        <tr>
            <th width="14%" class="text-center">Documento</th>
            <th width="10%" class="text-center">Fecha</th>
            <th width="31%">Proveedor</th>
            <th width="11%" class="text-right">Base S/IVA</th>
            <th width="12%"  class="text-right">IVA 13%</th>
            <th width="9%"  class="text-right">Retención</th>
            <th width="13%"  class="text-right">Total</th>
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

        $sigla      = $siglas[$c->tipo_dte] ?? $c->tipo_dte;
        $correlativo = str_pad(substr($c->numero_control, -6), 6, '0', STR_PAD_LEFT);
        $docLabel   = $sigla . ' ' . $correlativo . ($esAnulada ? ' [AN]' : '');
        $cls = $esAnulada ? ' class="anulada"' : '';
    ?>
        <tr>
            <td<?= $cls ?> class="text-center"><?= esc($docLabel) ?></td>
            <td<?= $cls ?> class="text-center"><?= date('d/m/Y', strtotime($c->fecha_emision)) ?></td>
            <td<?= $cls ?>><?= esc($c->proveedor_nombre) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($base,  2) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($iva,   2) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($ret,   2) ?></td>
            <td<?= $cls ?> class="text-right">$ <?= number_format($total, 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tbody>
        <tr>
            <td width="14%" style="border:none; padding:2px 0;"></td>
            <td width="10%" style="border:none; padding:2px 0;"></td>
            <td width="38%" style="border-left:none; border-right:none; border-top:2px solid #548235; padding:4px 5px; font-weight:bold; background:#e2efda;">TOTALES</td>
            <td width="11%" class="text-right" style="border-top:2px solid #548235; background:#e2efda; font-weight:bold;">$ <?= number_format($tBase,  2) ?></td>
            <td width="9%"  class="text-right" style="border-top:2px solid #548235; background:#e2efda; font-weight:bold;">$ <?= number_format($tIva,   2) ?></td>
            <td width="9%"  class="text-right" style="border-top:2px solid #548235; background:#e2efda; font-weight:bold;">$ <?= number_format($tRet,   2) ?></td>
            <td width="9%"  class="text-right" style="border-top:2px solid #548235; background:#e2efda; font-weight:bold;">$ <?= number_format($tTotal, 2) ?></td>
        </tr>
    </tbody>
</table>

</body>
</html>
