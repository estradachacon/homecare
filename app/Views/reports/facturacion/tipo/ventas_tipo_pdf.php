<?php helper('dte'); ?>

<?php

$tiposDocumento = dte_tipos();
$siglas = dte_siglas();

$gt_base = 0;
$gt_iva = 0;
$gt_valor = 0;
$gt_ret = 0;
$gt_total = 0;

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 8px;
            color: #333;
            margin-top: 15px;
        }

        h3 {
            margin-bottom: 3px;
        }

        .header-info {
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            table-layout: fixed;
        }

        th,
        td {
            border: 0.4px solid #999;
            padding: 3px;
        }

        th {
            background: #1f4e79;
            color: #fff;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .tipo-header {
            background: #1f4e79;
            color: white;
            font-weight: bold;
            padding: 4px;
            margin-top: 10px;
        }

        .grupo-header {
            background: #ddebf7;
            font-weight: bold;
            padding: 3px;
        }

        .item-row td {
            border: none;
            font-size: 7px;
            padding-left: 15px;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
            border-top: 1.5px solid #548235;
        }
    </style>

</head>

<body>

    <h3>
        REPORTE DE VENTAS POR TIPO DE VENTA
    </h3>

    <div class="header-info">

        <strong>Desde:</strong>
        <?= date('d/m/Y', strtotime($desde)) ?>

        &nbsp;&nbsp;

        <strong>Hasta:</strong>
        <?= date('d/m/Y', strtotime($hasta)) ?>

        &nbsp;&nbsp;

        <strong>Clasificado por:</strong>
        <?= ucfirst($clasificado) ?>

        &nbsp;&nbsp;

        <strong>Generado:</strong>
        <?= esc($generado_en) ?>

    </div>


    <?php foreach ($reporte as $tipoVenta => $grupos): ?>

        <div class="tipo-header">
            TIPO DE VENTA: <?= esc($tipoVenta) ?>
        </div>

        <?php
        $tipo_base  = 0;
        $tipo_iva   = 0;
        $tipo_valor = 0;
        $tipo_ret   = 0;
        $tipo_total = 0;

        // Obtenemos el último grupo para saber cuándo cerrar la tabla
        $n_grupos = count($grupos);
        $i_grupo = 0;
        ?>

        <?php foreach ($grupos as $grupo => $data): ?>
            <?php $i_grupo++; ?>

            <div class="grupo-header">
                <?= $clasificado === 'cliente' ? 'CLIENTE: ' : 'VENDEDOR: ' ?>
                <?= esc($grupo) ?>
            </div>

            <table>
                <colgroup>
                    <col style="width:8%">
                    <col style="width:6%">
                    <col style="width:8%">
                    <col style="width:30%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:12%">
                    <col style="width:10%">
                    <col style="width:12%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th class="text-right">Valor S/IVA</th>
                        <th class="text-right">IVA</th>
                        <th class="text-right">Valor Venta</th>
                        <th class="text-right">1% Ret</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grp_base  = 0;
                    $grp_iva   = 0;
                    $grp_valor = 0;
                    $grp_ret   = 0;
                    $grp_total = 0;
                    ?>
                    <?php foreach ($data['documentos'] as $doc): ?>
                        <?php
                        $factura = $doc['factura'];
                        $items   = $doc['items'];
                        $base  = $factura->total_gravada ?? 0;
                        $iva   = $factura->total_iva ?? 0;
                        $valor = $factura->monto_total_operacion ?? 0;
                        $ret   = $factura->iva_rete1 ?? 0;
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

                        $grp_base += $base;
                        $grp_iva += $iva;
                        $grp_valor += $valor;
                        $grp_ret += $ret;
                        $grp_total += $total;

                        $tipo_base += $base;
                        $tipo_iva += $iva;
                        $tipo_valor += $valor;
                        $tipo_ret += $ret;
                        $tipo_total += $total;

                        $gt_base += $base;
                        $gt_iva += $iva;
                        $gt_valor += $valor;
                        $gt_ret += $ret;
                        $gt_total += $total;
                        ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($factura->fecha_emision)) ?></td>
                            <td><?= esc($siglas[$factura->tipo_dte] ?? $factura->tipo_dte) ?></td>
                            <td><?= esc(substr($factura->numero_control, -6)) ?></td>
                            <td><?= esc($factura->cliente_nombre) ?></td>
                            <td class="text-right">$ <?= number_format($base, 2) ?></td>
                            <td class="text-right">$ <?= number_format($iva, 2) ?></td>
                            <td class="text-right">$ <?= number_format($valor, 2) ?></td>
                            <td class="text-right">$ <?= number_format($ret, 2) ?></td>
                            <td class="text-right">$ <?= number_format($total, 2) ?></td>
                        </tr>
                        <?php if ($mostrarItems && !empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="item-row">
                                    <td colspan="2"></td>
                                    <td colspan="2"><?= esc($item->descripcion ?? '') ?></td>
                                    <td class="text-right"><?= number_format($item->precio_unitario ?? 0, 2) ?></td>
                                    <td colspan="4"></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="totales">
                        <td colspan="4">TOTAL <?= strtoupper($clasificado) ?></td>
                        <td class="text-right">$ <?= number_format($grp_base, 2) ?></td>
                        <td class="text-right">$ <?= number_format($grp_iva, 2) ?></td>
                        <td class="text-right">$ <?= number_format($grp_valor, 2) ?></td>
                        <td class="text-right">$ <?= number_format($grp_ret, 2) ?></td>
                        <td class="text-right">$ <?= number_format($grp_total, 2) ?></td>
                    </tr>

                    <?php if ($i_grupo === $n_grupos): ?>
                        <tr class="totales" style="background:#d9d9d9; border-top: 2px solid #333;">
                            <td colspan="4">TOTAL TIPO DE VENTA</td>
                            <td class="text-right">$ <?= number_format($tipo_base, 2) ?></td>
                            <td class="text-right">$ <?= number_format($tipo_iva, 2) ?></td>
                            <td class="text-right">$ <?= number_format($tipo_valor, 2) ?></td>
                            <td class="text-right">$ <?= number_format($tipo_ret, 2) ?></td>
                            <td class="text-right">$ <?= number_format($tipo_total, 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        <?php endforeach; ?>

        <?php if ($saltoTipo): ?>
            <div style="page-break-before:always;"></div>
        <?php endif; ?>

    <?php endforeach; ?>

    <table style="margin-top:20px;">
        <colgroup>
            <col style="width:9%"> <!-- Fecha -->
            <col style="width:7%"> <!-- Tipo -->
            <col style="width:9%"> <!-- Número -->
            <col style="width:29%"> <!-- Cliente -->
            <col style="width:11%"> <!-- S/IVA -->
            <col style="width:11%"> <!-- IVA -->
            <col style="width:11%"> <!-- Venta -->
            <col style="width:6%"> <!-- Ret -->
            <col style="width:7%"> <!-- Total -->
        </colgroup>
        <tr class="totales" style="background:#fce4d6;">
            <td colspan="4">GRAN TOTAL</td>
            <td class="text-right">$ <?= number_format($gt_base, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_iva, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_valor, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_ret, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_total, 2) ?></td>
        </tr>
    </table>


    <table>
        <colgroup>
            <col style="width:8%">
            <col style="width:6%">
            <col style="width:8%">
            <col style="width:30%">
            <col style="width:12%">
            <col style="width:12%">
            <col style="width:12%">
            <col style="width:10%">
            <col style="width:12%">
        </colgroup>
        <tr class="totales">

            <td colspan="4">
                GRAN TOTAL
            </td>

            <td class="text-right">$ <?= number_format($gt_base, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_iva, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_valor, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_ret, 2) ?></td>
            <td class="text-right">$ <?= number_format($gt_total, 2) ?></td>

        </tr>

    </table>

</body>

</html>