<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Detalle de Tracking #<?= $tracking->id ?></h5>
            </div>
            <div class="card-body">
                <p><strong>Motorista:</strong> <?= $tracking->motorista_name ?? 'N/A' ?></p>
                <p><strong>Fecha:</strong> <?= $tracking->date ?></p>

                <hr>

                <h6>Paquetes</h6>
                <table class="table table-bordered table-sm">
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
                                <tr>
                                    <td><?= $d->tipo_servicio ?></td>
                                    <td><?= $d->cliente ?></td>
                                    <td>
                                        <?php
                                        // Mostrar destino según si es personalizado o punto fijo
                                        if (!empty($d->destino_personalizado)) {
                                            echo $d->destino_personalizado;
                                        } elseif (!empty($d->lugar_recolecta_paquete)) {
                                            echo $d->lugar_recolecta_paquete;
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td><?= number_format($d->monto, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay paquetes asignados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <a href="<?= base_url('tracking') ?>" class="btn btn-secondary">Volver</a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>