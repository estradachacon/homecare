<?php helper('dte'); ?>

<meta charset="UTF-8">
<br>
<h3>
    REPORTE DE FACTURACIÓN - DETALLE
    <?= !empty($cliente) ? ' — ' . esc($cliente->nombre) : '' ?>
</h3>
<br>
<br>
<table border="0">

    <tr>
        <td><strong>Desde:</strong></td>
        <td><?= date('d/m/Y', strtotime($desde)) ?></td>

        <td><strong>Hasta:</strong></td>
        <td><?= date('d/m/Y', strtotime($hasta)) ?></td>

        <td><strong>Cliente:</strong></td>
        <td><?= !empty($cliente) ? esc($cliente->nombre) : 'Todos los clientes' ?></td>

        <td><strong>Generado:</strong></td>
        <td><?= esc($generado_en) ?></td>
    </tr>

</table>

<br>

<style>
    td {
        white-space: nowrap;
    }
</style>
<table border="1">

    <tr style="background:#1f4e79;color:white;font-weight:bold;">
        <th>Fecha</th>
        <th>Tipo</th>
        <th>Número</th>
        <th>Cliente</th>
        <th>Total S/IVA</th>
        <th>IVA 13%</th>
        <th>Valor Venta</th>
        <th>1% Ret</th>
        <th>Total</th>
    </tr>

    <?php

    $tiposDocumento = dte_tipos();
    $siglas = dte_siglas();

    $gt_base = 0;
    $gt_iva = 0;
    $gt_valor = 0;
    $gt_ret = 0;
    $gt_total = 0;

    ?>

    <?php foreach ($reporte as $tipo => $fechas): ?>

        <tr style="background:#ddebf7;font-weight:bold;">
            <td colspan="9">
                TIPO DOCUMENTO: <?= esc($tiposDocumento[$tipo] ?? $tipo) ?>
            </td>
        </tr>

        <?php
        $sub_base = 0;
        $sub_iva = 0;
        $sub_valor = 0;
        $sub_ret = 0;
        $sub_total = 0;
        ?>

        <?php foreach ($fechas as $fecha => $data): ?>
            <?php foreach ($data['facturas'] as $factura): ?>

                <?php

                $base = $factura->total_gravada ?? 0;
                $iva = $factura->total_iva ?? 0;
                $valor = $factura->monto_total_operacion ?? 0;
                $ret = $factura->iva_rete1 ?? 0;
                $total = $factura->total_pagar ?? 0;

                if ($factura->tipo_dte == '05') {
                    $base *= -1;
                    $iva *= -1;
                    $valor *= -1;
                    $ret *= -1;
                    $total *= -1;
                }

                if ($factura->anulada) {
                    $base = $iva = $valor = $ret = $total = 0;
                }

                $sub_base += $base;
                $sub_iva += $iva;
                $sub_valor += $valor;
                $sub_ret += $ret;
                $sub_total += $total;

                $gt_base += $base;
                $gt_iva += $iva;
                $gt_valor += $valor;
                $gt_ret += $ret;
                $gt_total += $total;

                ?>

                <tr>

                    <td><?= date('d/m/Y', strtotime($factura->fecha_emision)) ?></td>

                    <td><?= esc($siglas[$factura->tipo_dte] ?? $factura->tipo_dte) ?></td>

                    <td><?= esc(str_pad(substr($factura->numero_control, -6), 6, '0', STR_PAD_LEFT)) ?></td>

                    <td><?= esc($factura->cliente_nombre) ?></td>

                    <td><?= number_format($base, 2) ?></td>

                    <td><?= number_format($iva, 2) ?></td>

                    <td><?= number_format($valor, 2) ?></td>

                    <td><?= number_format($ret, 2) ?></td>

                    <td><?= number_format($total, 2) ?></td>

                </tr>

            <?php endforeach; ?>
        <?php endforeach; ?>

        <tr style="background:#e2efda;font-weight:bold;">
            <td colspan="4">SUBTOTAL <?= esc($siglas[$tipo] ?? $tipo) ?></td>

            <td><?= number_format($sub_base, 2) ?></td>
            <td><?= number_format($sub_iva, 2) ?></td>
            <td><?= number_format($sub_valor, 2) ?></td>
            <td><?= number_format($sub_ret, 2) ?></td>
            <td><?= number_format($sub_total, 2) ?></td>

        </tr>

    <?php endforeach; ?>

    <tr style="background:#c6e0b4;font-weight:bold;">

        <td colspan="4">GRAN TOTAL</td>

        <td><?= number_format($gt_base, 2) ?></td>
        <td><?= number_format($gt_iva, 2) ?></td>
        <td><?= number_format($gt_valor, 2) ?></td>
        <td><?= number_format($gt_ret, 2) ?></td>
        <td><?= number_format($gt_total, 2) ?></td>

    </tr>

</table>