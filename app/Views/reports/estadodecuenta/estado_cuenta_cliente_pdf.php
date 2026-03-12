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

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            border-top: 2px solid #548235;
        }

        @page {
            margin-top: 55px;
            margin-bottom: 60px;
            margin-left: 40px;
            margin-right: 40px;
        }
    </style>
</head>

<body>

    <h3>ESTADO DE CUENTA DEL CLIENTE</h3>

    <div class="header-info">

        <strong>Cliente:</strong>
        <?= esc($cliente->nombre) ?>

        &nbsp;&nbsp;&nbsp;

        <strong>Generado:</strong>
        <?= esc($generado_en) ?>

    </div>

    <table>

        <thead>
            <tr>
                <th width="12%">Fecha</th>
                <th width="10%">Tipo</th>
                <th width="15%">Documento</th>
                <th width="25%">Asociado</th>
                <th width="19%" class="text-right">Cargo</th>
                <th width="19%" class="text-right">Abono</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($movimientos as $m): ?>

                <tr>

                    <td>
                        <?= date('d/m/Y', strtotime($m->fecha)) ?>
                    </td>

                    <td>
                        <?= esc($m->tipo) ?>
                    </td>

                    <td>
                        <?= esc($m->numDoc) ?>
                    </td>

                    <td>
                        <?= esc($m->asociado) ?>
                    </td>

                    <td class="text-right">
                        <?= $m->cargo > 0 ? '$ ' . number_format($m->cargo, 2) : '' ?>
                    </td>

                    <td class="text-right">
                        <?= $m->abono > 0 ? '$ ' . number_format($m->abono, 2) : '' ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

        <tfoot>

            <tr class="totales">
                <td colspan="4" class="text-right">
                    SUBTOTAL
                </td>

                <td class="text-right">
                    $ <?= number_format($totalCargo, 2) ?>
                </td>

                <td class="text-right">
                    $ <?= number_format($totalAbono, 2) ?>
                </td>

            </tr>

            <tr class="totales">

                <td colspan="5" class="text-right">
                    SALDO
                </td>

                <td class="text-right">
                    $ <?= number_format($saldo, 2) ?>
                </td>

            </tr>

        </tfoot>

    </table>

</body>

</html>