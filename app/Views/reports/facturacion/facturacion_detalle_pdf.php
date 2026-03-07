<?php helper('dte'); ?>
<?php

$tiposDocumento = dte_tipos();
$siglas = dte_siglas();

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
            margin: 5px;
        }

        h3 {
            margin-bottom: 3px;
            font-size: 10px;
        }

        .header-info {
            margin-bottom: 6px;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th,
        td {
            border: 0.4px solid #999;
            padding: 2px 3px;
            font-size: 8px;
        }

        th {
            background: #1f4e79;
            color: #fff;
            font-weight: bold;
            font-size: 8px;
        }

        .text-right {
            text-align: right;
        }

        .tipo-header {
            background: #1f4e79;
            color: #fff;
            font-weight: bold;
            padding: 3px;
            margin-top: 8px;
            font-size: 8px;
        }

        .fecha-header {
            background: #ddebf7;
            font-weight: bold;
            padding: 3px;
            font-size: 8px;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            border-top: 1.5px solid #548235;
        }

        .footer {
            position: fixed;
            bottom: -5px;
            left: 0px;
            right: 0px;
            height: 15px;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>

<body>

    <h3>REPORTE DE FACTURACIÓN - DETALLE</h3>

    <div class="header-info">
        <strong>Desde:</strong> <?= date('d/m/Y', strtotime($desde)) ?>
        &nbsp;&nbsp;&nbsp;
        <strong>Hasta:</strong> <?= date('d/m/Y', strtotime($hasta)) ?>
        &nbsp;&nbsp;&nbsp;
        <strong>Generado:</strong> <?= esc($generado_en) ?>
    </div>

    <?php $granTotal = 0; ?>
    <?php

    $gt_base = 0;
    $gt_iva = 0;
    $gt_valor = 0;
    $gt_ret = 0;
    $gt_total = 0;

    ?>

    <?php foreach ($reporte as $tipo => $fechas): ?>

        <div class="tipo-header">
            TIPO DOCUMENTO:
            <?= esc($tiposDocumento[$tipo] ?? 'Documento ' . $tipo) ?>
        </div>

        <?php $totalTipo = 0; ?>

        <?php foreach ($fechas as $fecha => $data): ?>

            <div class="fecha-header">
                Fecha: <?= date('d/m/Y', strtotime($fecha)) ?>
            </div>

            <table>
                <thead>
                    <tr>

                        <th width="6%">Tipo</th>
                        <th width="7%">Num. Doc</th>
                        <th width="38%">Cliente</th>

                        <th class="text-right">Total S/IVA</th>
                        <th class="text-right">IVA 13%</th>
                        <th class="text-right">Valor Venta</th>
                        <th class="text-right">1% Ret</th>
                        <th class="text-right">Total</th>

                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($data['facturas'] as $factura): ?>
                        <?php

                        $total = $factura->monto_total_operacion;

                        $base = $total / 1.13;
                        $iva = $total - $base;
                        $ret = $base * 0.01;
                        $valor = $base + $iva;

                        /* NOTA DE CREDITO RESTA */
                        if ($factura->tipo_dte == '05') {
                            $base *= -1;
                            $iva *= -1;
                            $valor *= -1;
                            $ret *= -1;
                            $total *= -1;
                        }

                        /* GRAN TOTALES */
                        $gt_base += $base;
                        $gt_iva += $iva;
                        $gt_valor += $valor;
                        $gt_ret += $ret;
                        $gt_total += $total;

                        ?>
                        <tr>

                            <td><?= esc($siglas[$factura->tipo_dte] ?? $factura->tipo_dte) ?></td>

                            <td><?= esc(str_pad(substr($factura->numero_control, -6), 6, '0', STR_PAD_LEFT)) ?></td>

                            <td><?= esc($factura->cliente_nombre) ?></td>

                            <td class="text-right">$ <?= number_format($base, 2) ?></td>

                            <td class="text-right">$ <?= number_format($iva, 2) ?></td>

                            <td class="text-right">$ <?= number_format($valor, 2) ?></td>

                            <td class="text-right">$ <?= number_format($ret, 2) ?></td>

                            <td class="text-right">$ <?= number_format($total, 2) ?></td>

                        </tr>
                    <?php endforeach; ?>

                </tbody>

                <tfoot>
                    <tr class="totales">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right">TOTAL DÍA</td>
                        <td class="text-right">
                            $ <?= number_format($data['total'], 2) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <?php
            $totalTipo += $data['total'];
            $granTotal += $data['total'];
            ?>

        <?php endforeach; ?>

        <table>
            <tfoot>

                <tr class="totales">

                    <td colspan="3">GRAN TOTAL</td>

                    <td class="text-right">$ <?= number_format($gt_base, 2) ?></td>

                    <td class="text-right">$ <?= number_format($gt_iva, 2) ?></td>

                    <td class="text-right">$ <?= number_format($gt_valor, 2) ?></td>

                    <td class="text-right">$ <?= number_format($gt_ret, 2) ?></td>

                    <td class="text-right">$ <?= number_format($gt_total, 2) ?></td>

                </tr>

            </tfoot>
        </table>

    <?php endforeach; ?>

    <table>
        <tfoot>
            <tr class="totales">
                <td class="text-right">TOTAL GENERAL</td>
                <td class="text-right">
                    $ <?= number_format($granTotal, 2) ?>
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>