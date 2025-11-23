<?php
$tiposServicio = [
    1 => 'Punto fijo',
    2 => 'Personalizado',
    3 => 'Recolecta de paquete',
    4 => 'Casillero'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tracking #<?= $tracking->id ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h2 { text-align: center; margin-bottom: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .logo { width: 60px; height: 60px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px 10px; border: 1px solid #ddd; }
        th { background-color: #007bff; color: #fff; text-align: left; }
        td { background-color: #f8f9fa; }
        tr:nth-child(even) td { background-color: #e9ecef; }
        .rounded { border-radius: 8px; overflow: hidden; }
        .section-title { background-color: #007bff; color: #fff; padding: 6px 10px; border-radius: 6px; margin-top: 15px; }
    </style>
</head>
<body>

<div class="header">
    <img src="<?= base_url('favicon.ico') ?>" alt="Logo" class="logo">
    <h2>Detalle de Tracking #<?= $tracking->id ?></h2>
    <div></div>
</div>

<p><strong>Motorista:</strong> <?= $tracking->motorista_name ?? 'N/A' ?></p>
<p><strong>Fecha:</strong> <?= $tracking->date ?></p>

<div class="section-title">Paquetes</div>

<table class="rounded">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Cliente</th>
            <th>Destino / Recolección</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($detalles)): ?>
            <?php foreach ($detalles as $d): ?>
                <?php
                $tipo = $tiposServicio[$d->tipo_servicio] ?? 'Desconocido';

                if ($d->tipo_servicio == 1) $destino = $d->puntofijo_nombre ?? 'Punto no encontrado';
                elseif ($d->tipo_servicio == 2) $destino = $d->destino_personalizado ?: 'N/A';
                elseif ($d->tipo_servicio == 4) $destino = $d->puntofijo_nombre ?: 'Casillero';
                elseif ($d->tipo_servicio == 3) {
                    $recolecta = $d->lugar_recolecta_paquete ?: 'Pendiente';
                    $entrega = $d->destino_personalizado ?: 'Pendiente';
                }
                ?>
                <tr>
                    <td><?= esc($tipo) ?></td>
                    <td><?= esc($d->cliente) ?></td>
                    <td>
                        <?php if ($d->tipo_servicio == 3): ?>
                            <strong>Recolecta:</strong> <?= esc($recolecta) ?><br>
                            <strong>Entrega:</strong> <?= esc($entrega) ?>
                        <?php else: ?>
                            <?= esc($destino) ?>
                        <?php endif; ?>
                    </td>
                    <td>$<?= number_format($d->monto, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">No hay paquetes asignados</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
