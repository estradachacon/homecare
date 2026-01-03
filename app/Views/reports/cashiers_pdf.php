<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h3 {
            text-align: center;
            margin-bottom: 5px;
        }
        .filters {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

<h3>Reporte de Movimientos de Caja</h3>

<div class="filters">
    <?php if (!empty($filters['fecha_desde'])): ?>
        <strong>Desde:</strong> <?= esc($filters['fecha_desde']) ?>
    <?php endif; ?>

    <?php if (!empty($filters['fecha_hasta'])): ?>
        &nbsp;&nbsp;<strong>Hasta:</strong> <?= esc($filters['fecha_hasta']) ?>
    <?php endif; ?>

    <?php if (!empty($filters['tipo'])): ?>
        &nbsp;&nbsp;<strong>Tipo:</strong>
        <?= $filters['tipo'] === 'entrada' ? 'Entrada' : 'Salida' ?>
    <?php endif; ?>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Caja</th>
            <th>Tipo</th>
            <th>Concepto</th>
            <th>Referencia</th>
            <th>Fecha</th>
            <th class="text-right">Monto</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['cashier_id'] ?></td>
                <td><?= $r['type'] === 'in' ? 'Entrada' : 'Salida' ?></td>
                <td><?= esc($r['concept']) ?></td>
                <td>
                    <?= esc($r['reference_type'] ?? '-') ?>
                    <?= $r['reference_id'] ? '#'.$r['reference_id'] : '' ?>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                <td class="text-right">$<?= number_format($r['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="text-right"><strong>TOTAL</strong></td>
            <td class="text-right"><strong>$<?= number_format($total, 2) ?></strong></td>
        </tr>
    </tfoot>
</table>

</body>
</html>
