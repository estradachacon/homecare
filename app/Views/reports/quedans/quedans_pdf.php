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
            margin-bottom: 6px;
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

        .sub-header {
            background: #ddebf7;
            font-weight: bold;
            padding: 3px;
        }

        /* ESTADOS */
        .estado {
            text-align: center;
            font-weight: bold;
        }

        .estado-activo {
            background: #fdecea;
            color: #c0392b;
        }

        .estado-parcial {
            background: #fff4e5;
            color: #e67e22;
        }

        .estado-pagado {
            background: #e8f8f5;
            color: #1e8449;
        }

        .estado-anulado {
            background: #f2f2f2;
            color: #7f8c8d;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }
                @page {
            margin: 40px 30px 60px 30px;
        }
    </style>
</head>

<body>

    <h3>REPORTE DE QUEDANS</h3>

    <div class="header-info">
        <strong>Generado:</strong> <?= date('d/m/Y H:i') ?>
    </div>

    <?php $granTotal = 0; ?>

    <?php foreach ($quedans as $q): ?>

        <div class="sub-header">
            Quedan #<?= $q->numero_quedan ?> |
            <?= esc($q->cliente_nombre) ?> |
            Emisión: <?= date('d/m/Y', strtotime($q->fecha_emision)) ?> |
            Pago: <?= date('d/m/Y', strtotime($q->fecha_pago)) ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="30%">Documento</th>
                    <th width="20%">Fecha</th>
                    <th width="15%">Total</th>
                    <th width="15%">Aplicado</th>
                    <th width="20%">Estado</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $detalles = (new \App\Models\QuedanFacturaModel())
                    ->getFacturasPorQuedan($q->id);

                $totalQuedan = 0;
                ?>

                <?php foreach ($detalles as $d): ?>

                    <?php
                    // 🔥 CALCULAR SALDO DEL QUEDAN
                    if (($d->anulada ?? 0) == 0) {
                        $totalQuedan += $d->saldo ?? 0;
                    }

                    // 🔥 FORMATO DOCUMENTO
                    $correlativo = str_pad(substr($d->numero_control ?? '', -6), 6, '0', STR_PAD_LEFT);

                    $partes = explode('-', $d->numero_control);
                    $tipoCodigo = $partes[1] ?? null;

                    $sigla = $siglas[$tipoCodigo] ?? 'DOC';

                    // 🔥 ESTADO
                    if (($d->anulada ?? 0) == 1) {
                        $estado = 'Anulada';
                        $class = 'estado-anulado';
                    } elseif (($d->saldo ?? 0) == 0) {
                        $estado = 'Pagado';
                        $class = 'estado-pagado';
                    } elseif (($d->saldo ?? 0) < ($d->total_pagar ?? 0)) {
                        $estado = 'Parcial';
                        $class = 'estado-parcial';
                    } else {
                        $estado = 'Activo';
                        $class = 'estado-activo';
                    }
                    ?>

                    <tr>

                        <td>
                            <?= esc($sigla . ' ' . $correlativo) ?>
                        </td>

                        <td>
                            <?= !empty($d->fecha_emision) ? date('d/m/Y', strtotime($d->fecha_emision)) : '' ?>
                        </td>

                        <td class="text-right">
                            $<?= number_format($d->total_pagar ?? 0, 2) ?>
                        </td>

                        <td class="text-right">
                            $<?= number_format($d->monto_aplicado ?? 0, 2) ?>
                        </td>

                        <td class="estado <?= $class ?>">
                            <?= $estado ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

            <tfoot>
                <tr class="totales">
                    <td colspan="4">SALDO del QUEDAN</td>
                    <td class="text-right">
                        $<?= number_format($totalQuedan, 2) ?>
                    </td>
                </tr>
            </tfoot>

        </table>

        <?php $granTotal += $totalQuedan; ?>

    <?php endforeach; ?>

    <table>
        <tfoot>
            <tr class="totales">
                <td colspan="4">Saldo Total general</td>
                <td class="text-right">
                    $<?= number_format($granTotal, 2) ?>
                </td>
            </tr>
        </tfoot>
    </table>

</body>

</html>