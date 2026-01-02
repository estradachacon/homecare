<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

<h3>Reporte de Paquetes</h3>
<p>Generado: <?= date('d/m/Y H:i') ?></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Vendedor</th>
            <th>Servicio</th>
            <th>Fecha</th>
            <th>Estatus</th>
            <th>Flete</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $totalFlete = 0;
        $totalMonto = 0;
        ?>
        <?php foreach ($packages as $pkg): ?>
            <tr>
                <td><?= $pkg->id ?></td>
                <td><?= esc($pkg->cliente) ?></td>
                <td><?= esc($pkg->vendedor) ?></td>
                <td><?= esc($pkg->tipo_servicio) ?></td>
                <td><?= esc($pkg->fecha_ingreso) ?></td>
                <td><?= esc($pkg->estatus) ?></td>
                <td class="text-right"><?= number_format($pkg->flete_total, 2) ?></td>
                <td class="text-right"><?= number_format($pkg->monto, 2) ?></td>
            </tr>
            <?php
            $totalFlete += $pkg->flete_total;
            $totalMonto += $pkg->monto;
            ?>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">TOTALES</th>
            <th class="text-right"><?= number_format($totalFlete, 2) ?></th>
            <th class="text-right"><?= number_format($totalMonto, 2) ?></th>
        </tr>
    </tfoot>
</table>

</body>
</html>
