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
}

th, td {
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

.cliente-row {
    page-break-inside: avoid;
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

@page {
    margin-top: 55px;
    margin-bottom: 60px;
    margin-left: 40px;
    margin-right: 40px;
}
</style>
</head>

<body>

<h3>SALDOS POR TIPO DE VENTA — PRODUCTOS VS SERVICIOS</h3>

<div class="header-info">
    <strong>Fecha corte:</strong> <?= date('d/m/Y', strtotime($fecha)) ?>
    &nbsp;&nbsp;&nbsp;
    <strong>Generado:</strong> <?= esc($generado_en) ?>
</div>

<table>
    <thead>
        <tr>
            <th width="40%">Cliente</th>
            <th width="20%" class="text-right">Saldo Productos</th>
            <th width="20%" class="text-right">Saldo Servicios</th>
            <th width="20%" class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>

        <?php
        $tProd = 0;
        $tServ = 0;
        $tTotal = 0;
        ?>

        <?php foreach ($reporte as $row): ?>

            <?php
                $tProd  += $row['productos'];
                $tServ  += $row['servicios'];
                $tTotal += $row['total'];
            ?>

            <tr class="cliente-row">
                <td><?= esc($row['cliente']) ?></td>
                <td class="text-right">$ <?= number_format($row['productos'], 2) ?></td>
                <td class="text-right">$ <?= number_format($row['servicios'], 2) ?></td>
                <td class="text-right"><strong>$ <?= number_format($row['total'], 2) ?></strong></td>
            </tr>

        <?php endforeach; ?>

    </tbody>

    <tfoot>
        <tr class="totales">
            <td class="text-right">TOTALES GENERALES</td>
            <td class="text-right">$ <?= number_format($tProd, 2) ?></td>
            <td class="text-right">$ <?= number_format($tServ, 2) ?></td>
            <td class="text-right">$ <?= number_format($tTotal, 2) ?></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
