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
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            border-top: 1.5px solid #548235;
        }

        .fecha-header {
            background: #ddebf7;
            font-weight: bold;
            padding: 3px;
        }
    </style>

</head>

<body>

    <h3>REPORTE DE VENTAS</h3>

    <div class="header-info">

        <strong>Desde:</strong> <?= date('d/m/Y', strtotime($desde)) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Hasta:</strong> <?= date('d/m/Y', strtotime($hasta)) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Generado:</strong> <?= esc($generado_en) ?>

    </div>

    <?php if (!empty($sin_datos)): ?>

        <div style="margin-top:40px;font-size:12px;text-align:center;">
            No se encontraron ventas en el rango de fechas seleccionado.
        </div>

        <?php return; ?>

    <?php endif; ?>

    <?php
    $gt_base = 0;
    $gt_iva = 0;
    $gt_valor = 0;
    $gt_ret = 0;
    $gt_total = 0;
    ?>

    <!-- ========================================= -->
    <!-- AGRUPADO POR VENDEDOR -->
    <!-- ========================================= -->

    <?php if ($nivel === 'dia' && $agrupar === 'vendedor'): ?>

        <?php foreach ($reporte as $vendedor => $fechas): ?>

            <div class="tipo-header">
                VENDEDOR: <?= esc($vendedor) ?>
            </div>

            <table>

                <thead>
                    <tr>
                        <th width="18%">Fecha</th>
                        <th class="text-right">Total S/IVA</th>
                        <th class="text-right">IVA 13%</th>
                        <th class="text-right">Valor Venta</th>
                        <th class="text-right">1% Ret</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $totalBase = 0;
                    $totalIva = 0;
                    $totalValor = 0;
                    $totalRet = 0;
                    $totalTotal = 0;
                    ?>

                    <?php foreach ($fechas as $fecha => $data): ?>

                        <tr>

                            <td><?= date('d/m/Y', strtotime($fecha)) ?></td>

                            <td class="text-right">$ <?= number_format($data['base'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($data['iva'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($data['valor'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($data['ret'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($data['total'], 2) ?></td>

                        </tr>

                        <?php

                        $totalBase += $data['base'];
                        $totalIva += $data['iva'];
                        $totalValor += $data['valor'];
                        $totalRet += $data['ret'];
                        $totalTotal += $data['total'];

                        $gt_base += $data['base'];
                        $gt_iva += $data['iva'];
                        $gt_valor += $data['valor'];
                        $gt_ret += $data['ret'];
                        $gt_total += $data['total'];

                        ?>

                    <?php endforeach; ?>

                </tbody>

                <tfoot>

                    <tr class="totales">

                        <td>TOTAL <?= esc($vendedor) ?></td>

                        <td class="text-right">$ <?= number_format($totalBase, 2) ?></td>
                        <td class="text-right">$ <?= number_format($totalIva, 2) ?></td>
                        <td class="text-right">$ <?= number_format($totalValor, 2) ?></td>
                        <td class="text-right">$ <?= number_format($totalRet, 2) ?></td>
                        <td class="text-right">$ <?= number_format($totalTotal, 2) ?></td>

                    </tr>

                </tfoot>

            </table>

        <?php endforeach; ?>

    <?php endif; ?>
    <?php if ($nivel === 'factura'): ?>

        <?php foreach ($reporte as $fecha => $rows): ?>

            <div class="fecha-header">
                Fecha: <?= date('d/m/Y', strtotime($fecha)) ?>
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Factura</th>
                        <th class="text-right">Total S/IVA</th>
                        <th class="text-right">IVA 13%</th>
                        <th class="text-right">Valor Venta</th>
                        <th class="text-right">1% Ret</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $row): ?>

                        <tr>

                            <td><?= date('d/m/Y', strtotime($fecha)) ?></td>

                            <td><?= esc($row['vendedor']) ?></td>

                            <td><?= esc(substr($row['factura'], -6)) ?></td>

                            <td class="text-right">$ <?= number_format($row['base'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['iva'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['valor'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['ret'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['total'], 2) ?></td>

                        </tr>

                        <?php

                        $gt_base += $row['base'];
                        $gt_iva += $row['iva'];
                        $gt_valor += $row['valor'];
                        $gt_ret += $row['ret'];
                        $gt_total += $row['total'];

                        ?>

                    <?php endforeach; ?>

                </tbody>
            </table>

        <?php endforeach; ?>

    <?php endif; ?>

    <!-- ========================================= -->
    <!-- SIN AGRUPAR (LISTA POR FECHA) -->
    <!-- ========================================= -->

    <?php if ($agrupar !== 'vendedor'): ?>

        <?php foreach ($reporte as $fecha => $rows): ?>

            <div class="fecha-header">
                Fecha: <?= date('d/m/Y', strtotime($fecha)) ?>
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th class="text-right">Total S/IVA</th>
                        <th class="text-right">IVA 13%</th>
                        <th class="text-right">Valor Venta</th>
                        <th class="text-right">1% Ret</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $row): ?>

                        <tr>

                            <td><?= esc($row['vendedor']) ?></td>

                            <td class="text-right">$ <?= number_format($row['base'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['iva'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['valor'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['ret'], 2) ?></td>
                            <td class="text-right">$ <?= number_format($row['total'], 2) ?></td>

                        </tr>

                        <?php

                        $gt_base += $row['base'];
                        $gt_iva += $row['iva'];
                        $gt_valor += $row['valor'];
                        $gt_ret += $row['ret'];
                        $gt_total += $row['total'];

                        ?>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endforeach; ?>

    <?php endif; ?>


    <!-- ========================================= -->
    <!-- GRAN TOTAL -->
    <!-- ========================================= -->

    <table>

        <tfoot>

            <tr class="totales">

                <td>GRAN TOTAL</td>

                <td class="text-right">$ <?= number_format($gt_base, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_iva, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_valor, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_ret, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_total, 2) ?></td>

            </tr>

        </tfoot>

    </table>

</body>

</html>