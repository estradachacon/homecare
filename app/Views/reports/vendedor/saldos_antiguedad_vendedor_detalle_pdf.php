<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 9px;
            color: #333;
        }

        @page {
            margin: 40px 30px 60px 30px;
        }

        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
        }

        h3 {
            margin-bottom: 5px;
        }

        .header-info {
            margin-bottom: 10px;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 0.5px solid #999;
            padding: 4px;
        }

        th {
            background: #1f4e79;
            color: #fff;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .vendedor-header {
            background: #ddebf7;
            font-weight: bold;
            padding: 6px;
            border: 1px solid #9dc3e6;
            margin-top: 10px;
        }

        .cliente-header {
            font-weight: bold;
            margin-top: 8px;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            border-top: 2px solid #548235;
        }

        @page {
            margin: 60px 30px 60px 30px;
        }

        .cliente-bloque {
            page-break-inside: auto;
        }

        .vendedor-header:not(:first-of-type) {
            page-break-before: always;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>

</head>
<?php
$tipos = [
    '01' => 'FCF',
    '03' => 'CCF',
    '05' => 'NCF'
];
?>

<body>
    <h3>SALDOS POR ANTIGÜEDAD CON DETALLE - POR VENDEDOR</h3>

    <div class="header-info">
        <strong>Fecha corte:</strong> <?= date('d/m/Y', strtotime($fecha)) ?>
        &nbsp;&nbsp;&nbsp;
        <strong>Generado:</strong> <?= esc($generado_en) ?>
    </div>

    <?php $i = 0; ?>
    <?php foreach ($reporte as $vendedor): ?>

        <?php if ($i > 0): ?>
            <div style="page-break-before: always;"></div>
        <?php endif; ?>

        <?php $i++; ?>
        <div class="vendedor-header">
            VENDEDOR: <?= esc($vendedor['vendedor']) ?>
        </div>

        <?php foreach ($vendedor['clientes'] as $cliente): ?>

            <div class="cliente-bloque">

                <div class="cliente-header">
                    Cliente: <?= esc($cliente['cliente']) ?>
                </div>

                <table>

                    <thead>
                        <tr>
                            <th width="12%">Fecha</th>
                            <th width="15%">N. Doc.</th>
                            <th width="10%">Plazo</th>
                            <th width="12%" class="text-right">30 días</th>
                            <th width="12%" class="text-right">60 días</th>
                            <th width="12%" class="text-right">90 días</th>
                            <th width="12%" class="text-right">120 + días</th>
                            <th width="15%" class="text-right">Total</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($cliente['documentos'] as $doc): ?>

                            <tr>

                                <td><?= date('d/m/Y', strtotime($doc['fecha'])) ?></td>

                                <td>
                                    <?php
                                    $tipo = $tipos[$doc['tipo']] ?? $doc['tipo'];
                                    ?>
                                    <?= esc($tipo) ?> <?= substr($doc['doc'], -4) ?>
                                </td>

                                <td><?= esc($doc['plazo']) ?></td>

                                <td class="text-right">
                                    <?= $doc['rango_0_30'] ? '$ ' . number_format($doc['rango_0_30'], 2) : '' ?>
                                </td>

                                <td class="text-right">
                                    <?= $doc['rango_31_60'] ? '$ ' . number_format($doc['rango_31_60'], 2) : '' ?>
                                </td>

                                <td class="text-right">
                                    <?= $doc['rango_61_90'] ? '$ ' . number_format($doc['rango_61_90'], 2) : '' ?>
                                </td>

                                <td class="text-right">
                                    <?= $doc['rango_90_mas'] ? '$ ' . number_format($doc['rango_90_mas'], 2) : '' ?>
                                </td>

                                <td class="text-right">
                                    $ <?= number_format($doc['total'], 2) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                    <tfoot>

                        <tr class="totales">

                            <td colspan="3">SALDO</td>

                            <td class="text-right">
                                $ <?= number_format($cliente['totales']['0_30'], 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($cliente['totales']['31_60'], 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($cliente['totales']['61_90'], 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($cliente['totales']['90_mas'], 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($cliente['totales']['total'], 2) ?>
                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        <?php endforeach; ?>
        <div class="cliente-header">
            TOTAL VENDEDOR
        </div>

        <table>

            <thead>
                <tr>
                    <th width="12%">30 días</th>
                    <th width="12%">60 días</th>
                    <th width="12%">90 días</th>
                    <th width="12%">120 + días</th>
                    <th width="15%">Total</th>
                </tr>
            </thead>

            <tbody>

                <tr class="totales">

                    <td class="text-right">
                        $ <?= number_format($vendedor['totales_vendedor']['0_30'], 2) ?>
                    </td>

                    <td class="text-right">
                        $ <?= number_format($vendedor['totales_vendedor']['31_60'], 2) ?>
                    </td>

                    <td class="text-right">
                        $ <?= number_format($vendedor['totales_vendedor']['61_90'], 2) ?>
                    </td>

                    <td class="text-right">
                        $ <?= number_format($vendedor['totales_vendedor']['90_mas'], 2) ?>
                    </td>

                    <td class="text-right">
                        $ <?= number_format($vendedor['totales_vendedor']['total'], 2) ?>
                    </td>

                </tr>

            </tbody>

        </table>
    <?php endforeach; ?>

</body>

</html>