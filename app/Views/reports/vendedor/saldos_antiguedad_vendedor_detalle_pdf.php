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
    margin-bottom: 12px;
}

th, td {
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

.vendedor-bloque {
    page-break-inside: avoid;
    margin-bottom: 15px;
}

.vendedor-header {
    background: #ddebf7;
    font-weight: bold;
    padding: 6px;
    border: 1px solid #9dc3e6;
    margin-top: 10px;
}

.pago-row td {
    background: #f7f7f7;
    font-size: 8.5px;
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

<h3>SALDOS POR ANTIGÜEDAD CON DETALLE - POR VENDEDOR</h3>

<div class="header-info">
    <strong>Fecha corte:</strong> <?= date('d/m/Y', strtotime($fecha)) ?>
    &nbsp;&nbsp;&nbsp;
    <strong>Generado:</strong> <?= esc($generado_en) ?>
</div>

<?php foreach ($reporte as $vendedor): ?>

    <div class="vendedor-bloque">

        <div class="vendedor-header">
            VENDEDOR: <?= esc($vendedor['vendedor']) ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="20%">Factura</th>
                    <th width="20%">Cliente</th>
                    <th width="15%">Fecha</th>
                    <th width="15%" class="text-right">Total</th>
                    <th width="15%" class="text-right">Saldo</th>
                    <th width="15%">Días</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($vendedor['facturas'] as $item): ?>

                    <tr>
                        <td><?= esc($item['factura']->numero_control ?? $item['factura']->id) ?></td>
                        <td><?= esc($item['factura']->cliente_nombre) ?></td>
                        <td><?= date('d/m/Y', strtotime($item['factura']->fecha_emision)) ?></td>
                        <td class="text-right">$ <?= number_format($item['factura']->monto_total_operacion, 2) ?></td>
                        <td class="text-right">$ <?= number_format($item['factura']->saldo, 2) ?></td>
                        <td><?= $item['dias'] ?></td>
                    </tr>

                    <!-- PAGOS -->
                    <?php foreach ($item['pagos'] as $pago): ?>
                        <tr class="pago-row">
                            <td colspan="2">
                                ↳ Pago <?= date('d/m/Y', strtotime($pago->fecha_pago)) ?>
                            </td>
                            <td></td>
                            <td colspan="2" class="text-right">
                                $ <?= number_format($pago->monto, 2) ?>
                            </td>
                            <td>Aplicado</td>
                        </tr>
                    <?php endforeach; ?>

                <?php endforeach; ?>

            </tbody>

            <tfoot>
                <tr class="totales">
                    <td colspan="3">Totales Vendedor</td>
                    <td class="text-right">
                        $ <?= number_format($vendedor['totales']['total_facturas'], 2) ?>
                    </td>
                    <td class="text-right">
                        $ <?= number_format($vendedor['totales']['total_saldo'], 2) ?>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

    </div>

<?php endforeach; ?>

</body>
</html>