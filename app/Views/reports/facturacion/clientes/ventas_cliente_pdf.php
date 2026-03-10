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

        .cliente-header {
            background: #ddebf7;
            font-weight: bold;
            padding: 3px;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            border-top: 1.5px solid #548235;
        }

        .anulada {
            color: #888;
            background: #f5f5f5;
        }
    </style>

</head>

<body>

    <h3>
        REPORTE DE VENTAS POR CLIENTE
    </h3>

    <div class="header-info">

        <strong>Desde:</strong>
        <?= date('d/m/Y', strtotime($desde)) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Hasta:</strong>
        <?= date('d/m/Y', strtotime($hasta)) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Generado:</strong>
        <?= esc($generado_en) ?>

    </div>

    <?php

    $gt_base = 0;
    $gt_iva = 0;
    $gt_valor = 0;
    $gt_ret = 0;
    $gt_total = 0;

    ?>

    <?php foreach ($reporte as $cliente => $tipos): ?>

        <div class="cliente-header">
            CLIENTE: <?= esc($cliente) ?>
        </div>

        <?php foreach ($tipos as $tipo => $data): ?>

            <div class="tipo-header">

                TIPO DOCUMENTO:
                <?= esc($tiposDocumento[$tipo] ?? 'Documento ' . $tipo) ?>

            </div>

            <?php

            $sub_base = 0;
            $sub_iva = 0;
            $sub_valor = 0;
            $sub_ret = 0;
            $sub_total = 0;

            ?>

            <table>

                <thead>

                    <tr>

                        <th width="8%">Fecha</th>
                        <th width="6%">Tipo</th>
                        <th width="7%">Num. Doc</th>
                        <th width="35%">Cliente</th>

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

                        $baseReal  = $factura->total_gravada ?? 0;
                        $ivaReal   = $factura->total_iva ?? 0;
                        $valorReal = $factura->monto_total_operacion ?? 0;
                        $retReal   = $factura->iva_rete1 ?? 0;
                        $totalReal = $factura->total_pagar ?? 0;

                        $esAnulada = $factura->anulada == 1;

                        /* VALORES PARA SUMAS */

                        $base  = $esAnulada ? 0 : $baseReal;
                        $iva   = $esAnulada ? 0 : $ivaReal;
                        $valor = $esAnulada ? 0 : $valorReal;
                        $ret   = $esAnulada ? 0 : $retReal;
                        $total = $esAnulada ? 0 : $totalReal;

                        /* NOTA DE CREDITO RESTA */

                        if ($factura->tipo_dte == '05') {

                            $baseReal  *= -1;
                            $ivaReal   *= -1;
                            $valorReal *= -1;
                            $retReal   *= -1;
                            $totalReal *= -1;

                            $base  *= -1;
                            $iva   *= -1;
                            $valor *= -1;
                            $ret   *= -1;
                            $total *= -1;
                        }

                        if ($esAnulada) {
                            $base = $iva = $valor = $ret = $total = 0;
                        }

                        /* SUBTOTAL */

                        $sub_base  += $base;
                        $sub_iva   += $iva;
                        $sub_valor += $valor;
                        $sub_ret   += $ret;
                        $sub_total += $total;

                        /* GRAN TOTAL */

                        $gt_base  += $base;
                        $gt_iva   += $iva;
                        $gt_valor += $valor;
                        $gt_ret   += $ret;
                        $gt_total += $total;

                        ?>

                        <tr class="<?= $esAnulada ? 'anulada' : '' ?>">

                            <td>
                                <?= date('d/m/Y', strtotime($factura->fecha_emision)) ?>
                            </td>

                            <td>
                                <?= esc($siglas[$factura->tipo_dte] ?? $factura->tipo_dte) ?>
                            </td>

                            <td>
                                <?= esc(str_pad(substr($factura->numero_control, -6), 6, '0', STR_PAD_LEFT)) ?>
                            </td>

                            <td style="padding:0;">

                                <table style="width:100%; border-collapse:collapse;">

                                    <tr>

                                        <td style="border:none;">
                                            <?= esc($factura->cliente_nombre) ?>
                                        </td>

                                        <?php if ($esAnulada): ?>

                                            <td style="border:none;text-align:right;color:#b00020;font-weight:bold;">
                                                ANULADO
                                            </td>

                                        <?php else: ?>

                                            <td style="border:none;"></td>

                                        <?php endif; ?>

                                    </tr>

                                </table>

                            </td>

                            <td class="text-right">
                                $ <?= number_format($baseReal, 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($ivaReal, 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($valorReal, 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($retReal, 2) ?>
                            </td>

                            <td class="text-right">
                                $ <?= number_format($totalReal, 2) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

                <tfoot>

                    <tr class="totales">

                        <td colspan="4">
                            SUBTOTAL
                        </td>

                        <td class="text-right">
                            $ <?= number_format($sub_base, 2) ?>
                        </td>

                        <td class="text-right">
                            $ <?= number_format($sub_iva, 2) ?>
                        </td>

                        <td class="text-right">
                            $ <?= number_format($sub_valor, 2) ?>
                        </td>

                        <td class="text-right">
                            $ <?= number_format($sub_ret, 2) ?>
                        </td>

                        <td class="text-right">
                            $ <?= number_format($sub_total, 2) ?>
                        </td>

                    </tr>

                </tfoot>

            </table>

        <?php endforeach; ?>

    <?php endforeach; ?>

    <table>

        <tfoot>

            <tr class="totales">

                <td colspan="4">
                    GRAN TOTAL
                </td>

                <td class="text-right">
                    $ <?= number_format($gt_base, 2) ?>
                </td>

                <td class="text-right">
                    $ <?= number_format($gt_iva, 2) ?>
                </td>

                <td class="text-right">
                    $ <?= number_format($gt_valor, 2) ?>
                </td>

                <td class="text-right">
                    $ <?= number_format($gt_ret, 2) ?>
                </td>

                <td class="text-right">
                    $ <?= number_format($gt_total, 2) ?>
                </td>

            </tr>

        </tfoot>

    </table>

</body>

</html>