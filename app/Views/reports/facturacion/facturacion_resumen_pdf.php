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
            margin-bottom: 10px;
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

        .fsee-header {
            background: #7b7b7b;
            color: #fff;
            font-weight: bold;
            padding: 6px;
            margin-top: 15px;
            font-size: 9px;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            border-top: 2px solid #548235;
        }
    </style>
</head>

<body>

    <?php
    $labelLinea = match($tipo_linea ?? '') {
        'producto' => 'Solo Productos',
        'servicio' => 'Solo Servicios',
        default    => 'Productos y Servicios',
    };
    ?>

    <h3>
        REPORTE DE FACTURACIÓN - RESUMEN
        <?= !empty($cliente) ? ' — ' . esc($cliente->nombre) : '' ?>
    </h3>

    <div class="header-info">

        <strong>Desde:</strong> <?= date('d/m/Y', strtotime($desde)) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Hasta:</strong> <?= date('d/m/Y', strtotime($hasta)) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Línea:</strong> <?= esc($labelLinea) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Generado:</strong> <?= esc($generado_en) ?>

    </div>

    <?php
    $tiposDocumento = [
        '01' => 'Factura de Consumidor Final Electrónica',
        '03' => 'Comprobante de Crédito Fiscal Electrónico',
        '04' => 'Nota de Remisión',
        '05' => 'Nota de Crédito',
        '06' => 'Nota de Débito',
        '14' => 'Factura de Sujeto Excluido',
    ];

    $aliasTipo = [
        '01' => 'FACT-DTE',
        '03' => 'CCF-DTE',
        '04' => 'NR-DTE',
        '05' => 'NC-DTE',
        '06' => 'ND-DTE',
        '14' => 'FSEE',
    ];

    // Reorganizar por tipo
    $agrupado = [];
    foreach ($reporte as $fecha => $tipos) {
        foreach ($tipos as $tipo => $data) {
            $agrupado[$tipo][$fecha] = $data;
        }
    }

    $gt_base = 0;
    $gt_iva  = 0;
    $gt_valor = 0;
    $gt_ret  = 0;
    $gt_total = 0;
    ?>

    <?php foreach ($agrupado as $tipo => $fechas): ?>

        <?php if ((string) $tipo === '14') continue; ?>

        <div class="tipo-header">
            TIPO DOCUMENTO:
            <?= esc($tiposDocumento[$tipo] ?? 'Documento ' . $tipo) ?>
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
                $totalBase  = 0;
                $totalIva   = 0;
                $totalValor = 0;
                $totalRet   = 0;
                $totalTipo  = 0;
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
                    $totalBase  += $data['base'];
                    $totalIva   += $data['iva'];
                    $totalValor += $data['valor'];
                    $totalRet   += $data['ret'];
                    $totalTipo  += $data['total'];

                    $gt_base  += $data['base'];
                    $gt_iva   += $data['iva'];
                    $gt_valor += $data['valor'];
                    $gt_ret   += $data['ret'];
                    $gt_total += $data['total'];
                    ?>

                <?php endforeach; ?>

            </tbody>

            <tfoot>
                <tr class="totales">
                    <td>TOTAL <?= esc($aliasTipo[$tipo] ?? $tipo) ?></td>
                    <td class="text-right">$ <?= number_format($totalBase, 2) ?></td>
                    <td class="text-right">$ <?= number_format($totalIva, 2) ?></td>
                    <td class="text-right">$ <?= number_format($totalValor, 2) ?></td>
                    <td class="text-right">$ <?= number_format($totalRet, 2) ?></td>
                    <td class="text-right">$ <?= number_format($totalTipo, 2) ?></td>
                </tr>
            </tfoot>
        </table>

    <?php endforeach; ?>

    <!-- GRAN TOTAL (sin sujeto excluido) -->
    <table>
        <tfoot>
            <tr class="totales">
                <td>TOTAL GENERAL</td>
                <td class="text-right">$ <?= number_format($gt_base, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_iva, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_valor, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_ret, 2) ?></td>
                <td class="text-right">$ <?= number_format($gt_total, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- BLOQUE SUJETO EXCLUIDO (fuera de totales) -->
    <?php if (isset($agrupado['14'])): ?>

        <div class="fsee-header">
            FACTURAS DE SUJETO EXCLUIDO — no incluidas en el total general
        </div>

        <table>
            <thead>
                <tr>
                    <th width="18%">Fecha</th>
                    <th class="text-right">Total S/IVA</th>
                    <th class="text-right">10% Retencion Renta</th>
                    <th class="text-right">Valor Venta</th>
                    <th class="text-right">1% Ret</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $fsee_base  = 0; $fsee_iva   = 0;
                $fsee_valor = 0; $fsee_ret   = 0; $fsee_total = 0;
                ?>
                <?php foreach ($agrupado['14'] as $fecha => $data): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($fecha)) ?></td>
                        <td class="text-right">$ <?= number_format($data['base'], 2) ?></td>
                        <?php $renta = max(0, ($data['base'] ?? 0) - ($data['total'] ?? 0)); ?>
                        <td class="text-right">$ <?= number_format($renta, 2) ?></td>
                        <td class="text-right">$ <?= number_format($data['valor'], 2) ?></td>
                        <td class="text-right">$ <?= number_format($data['ret'], 2) ?></td>
                        <td class="text-right">$ <?= number_format($data['total'], 2) ?></td>
                    </tr>
                    <?php
                    $fsee_base  += $data['base'];
                    $fsee_iva   += $renta;
                    $fsee_valor += $data['valor'];
                    $fsee_ret   += $data['ret'];
                    $fsee_total += $data['total'];
                    ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="totales">
                    <td>GRAN TOTAL FSEE</td>
                    <td class="text-right">$ <?= number_format($fsee_base, 2) ?></td>
                    <td class="text-right">$ <?= number_format($fsee_iva, 2) ?></td>
                    <td class="text-right">$ <?= number_format($fsee_valor, 2) ?></td>
                    <td class="text-right">$ <?= number_format($fsee_ret, 2) ?></td>
                    <td class="text-right">$ <?= number_format($fsee_total, 2) ?></td>
                </tr>
            </tfoot>
        </table>

    <?php endif; ?>

</body>

</html>
