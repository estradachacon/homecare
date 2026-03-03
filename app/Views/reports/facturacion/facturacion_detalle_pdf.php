<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 10px;
            color: #333;
        }

        h3 {
            margin-bottom: 5px;
        }

        .header-info {
            margin-bottom: 12px;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th,
        td {
            border: 0.5px solid #999;
            padding: 5px;
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
            padding: 6px;
            margin-top: 15px;
        }

        .fecha-header {
            background: #ddebf7;
            font-weight: bold;
            padding: 5px;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            border-top: 2px solid #548235;
        }

        .footer {
            position: fixed;
            bottom: -5px;
            left: 0px;
            right: 0px;
            height: 20px;
            font-size: 8px;
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
        $tiposDocumento = [
            '01' => 'Factura de Consumidor Final Electrónica',
            '03' => 'Comprobante de Crédito Fiscal Electrónico',
            '04' => 'Nota de Remisión',
            '05' => 'Nota de Crédito',
            '06' => 'Nota de Débito',
        ];
        $aliasTipo = [
            '01' => 'FACT-DTE',
            '03' => 'CCF-DTE',
            '04' => 'NR-DTE',
            '05' => 'NC-DTE',
            '06' => 'ND-DTE',
        ];
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
                        <th width="20%">Correlativo</th>
                        <th width="50%">Cliente</th>
                        <th width="30%" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($data['facturas'] as $factura): ?>
                        <tr>
                            <td><?= esc(str_pad(substr($factura->numero_control, -6), 6, '0', STR_PAD_LEFT)) ?></td>
                            <td><?= esc($factura->cliente_nombre) ?></td>
                            <td class="text-right">
                                $ <?= number_format($factura->monto_total_operacion, 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>

                <tfoot>
                    <tr class="totales">
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
                    <td class="text-right">
                        TOTAL <?= esc($aliasTipo[$tipo] ?? $tipo) ?>
                    </td>
                    <td class="text-right">
                        $ <?= number_format($totalTipo, 2) ?>
                    </td>
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