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

<h3>Reporte de Transacciones</h3>

<p>
    Desde: <?= esc($filters['fecha_desde'] ?? '—') ?>
    &nbsp; | &nbsp;
    Hasta: <?= esc($filters['fecha_hasta'] ?? '—') ?>
</p>

<table width="100%" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Cuenta</th>
            <th>Monto</th>
            <th>Origen</th>
            <th>Referencia</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($trans as $t): ?>
            <tr>
                <td><?= $t->id ?></td>
                <td><?= ucfirst($t->tipo) ?></td>
                <td><?= esc($t->cuenta) ?></td>
                <td>$<?= number_format($t->monto, 2) ?></td>
                <td><?= esc($t->origen) ?></td>
                <td><?= esc($t->referencia) ?></td>
                <td><?= $t->created_at ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">TOTAL</th>
            <th class="text-right">$<?= number_format(array_sum(array_map(fn($t) => $t->monto, $trans)), 2) ?></th>
            <th colspan="3"></th>
    </tfoot>
</table>


</body>
</html>
