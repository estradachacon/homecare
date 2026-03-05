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

</style>
</head>

<body>

<h3>SALDOS POR ANTIGÜEDAD</h3>

<div class="header-info">
    <strong>Fecha corte:</strong> <?= date('d/m/Y', strtotime($fecha)) ?>
    &nbsp;&nbsp;&nbsp;
    <strong>Generado:</strong> <?= esc($generado_en) ?>
</div>

<table>
    <thead>
        <tr>
            <th width="30%">Cliente</th>
            <th width="14%" class="text-right">0–30</th>
            <th width="14%" class="text-right">31–60</th>
            <th width="14%" class="text-right">61–90</th>
            <th width="14%" class="text-right">91+</th>
            <th width="14%" class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>

        <?php 
        $t0 = 0; $t1 = 0; $t2 = 0; $t3 = 0; $tt = 0;
        ?>

        <?php foreach ($reporte as $row): ?>

            <?php
                $t0 += $row['0_30'];
                $t1 += $row['31_60'];
                $t2 += $row['61_90'];
                $t3 += $row['91_mas'];
                $tt += $row['total'];
            ?>

            <tr class="cliente-row">
                <td><?= esc($row['cliente']) ?></td>
                <td class="text-right">$ <?= number_format($row['0_30'], 2) ?></td>
                <td class="text-right">$ <?= number_format($row['31_60'], 2) ?></td>
                <td class="text-right">$ <?= number_format($row['61_90'], 2) ?></td>
                <td class="text-right">$ <?= number_format($row['91_mas'], 2) ?></td>
                <td class="text-right"><strong>$ <?= number_format($row['total'], 2) ?></strong></td>
            </tr>

        <?php endforeach; ?>

    </tbody>

    <tfoot>
        <tr class="totales">
            <td class="text-right">TOTALES GENERALES</td>
            <td class="text-right">$ <?= number_format($t0, 2) ?></td>
            <td class="text-right">$ <?= number_format($t1, 2) ?></td>
            <td class="text-right">$ <?= number_format($t2, 2) ?></td>
            <td class="text-right">$ <?= number_format($t3, 2) ?></td>
            <td class="text-right">$ <?= number_format($tt, 2) ?></td>
        </tr>
    </tfoot>
</table>

</body>
</html>