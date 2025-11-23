<h2>Tracking #<?= $tracking->id ?></h2>
<p><strong>Motorista:</strong> <?= $tracking->motorista ?? 'N/A' ?></p>
<p><strong>Fecha:</strong> <?= $tracking->date ?></p>

<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Cliente</th>
            <th>Destino / Recolección</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detalles as $d): ?>
            <tr>
                <td><?= $d->tipo_servicio ?></td>
                <td><?= $d->cliente ?></td>
                <td>
                    <?= $d->destino_personalizado ?: $d->puntofijo_nombre ?: 'Pendiente' ?>
                </td>
                <td><?= number_format($d->monto, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
