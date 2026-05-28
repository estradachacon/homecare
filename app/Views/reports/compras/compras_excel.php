<meta charset="UTF-8">

<h3>COMPRAS PERCIBIDAS</h3>
<br>
<table border="0">
    <tr>
        <td><strong>Desde:</strong></td>
        <td><?= date('d/m/Y', strtotime($desde)) ?></td>
        <td><strong>Hasta:</strong></td>
        <td><?= date('d/m/Y', strtotime($hasta)) ?></td>
        <td><strong>Proveedor:</strong></td>
        <td><?= !empty($proveedor) ? esc($proveedor->nombre) : 'Todos' ?></td>
        <td><strong>Generado:</strong></td>
        <td><?= esc($generado_en) ?></td>
    </tr>
</table>
<br>
<style>td { white-space: nowrap; }</style>
<table border="1">
    <tr style="background:#1f4e79;color:white;font-weight:bold;">
        <th>Fecha</th>
        <th>Número Control</th>
        <th>Tipo DTE</th>
        <th>Proveedor</th>
        <th>Base S/IVA</th>
        <th>IVA 13%</th>
        <th>Retención</th>
        <th>Total</th>
        <th>Saldo</th>
        <th>Estado</th>
    </tr>
    <?php
    $tBase = 0; $tIva = 0; $tRet = 0; $tTotal = 0;
    foreach ($compras as $c):
        $esAnulada = (int)$c->anulada === 1;
        $base  = $esAnulada ? 0 : (float)$c->total_gravada;
        $iva   = $esAnulada ? 0 : (float)$c->total_iva;
        $ret   = $esAnulada ? 0 : (float)$c->iva_rete1;
        $total = $esAnulada ? 0 : (float)$c->total_pagar;
        $saldo = $esAnulada ? 0 : (float)$c->saldo;
        $tBase += $base; $tIva += $iva; $tRet += $ret; $tTotal += $total;
    ?>
    <tr>
        <td><?= date('d/m/Y', strtotime($c->fecha_emision)) ?></td>
        <td><?= esc($c->numero_control) ?></td>
        <td><?= esc($c->tipo_dte) ?></td>
        <td><?= esc($c->proveedor_nombre) ?></td>
        <td><?= $base ?></td>
        <td><?= $iva ?></td>
        <td><?= $ret ?></td>
        <td><?= $total ?></td>
        <td><?= $saldo ?></td>
        <td><?= $esAnulada ? 'Anulada' : ($saldo <= 0 ? 'Pagada' : 'Pendiente') ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:#e2efda;font-weight:bold;">
        <td colspan="4">TOTALES</td>
        <td><?= $tBase ?></td>
        <td><?= $tIva ?></td>
        <td><?= $tRet ?></td>
        <td><?= $tTotal ?></td>
        <td></td>
        <td></td>
    </tr>
</table>
