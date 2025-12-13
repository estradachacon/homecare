<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?php
$tiposServicio = [
    1 => 'Punto fijo',
    2 => 'Personalizado',
    3 => 'Recolecta de paquete',
    4 => 'Casillero'
];
?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center bg-primary text-white">
                <h5 class="mb-0">Detalle de Tracking #<?= $tracking->id ?></h5>
                <a href="<?= base_url('tracking-pdf/' . $tracking->id) ?>" target="_blank"
                   class="btn btn-light btn-sm ml-auto">
                   <i class="fa-solid fa-file-pdf"></i> Exportar PDF
                </a>
            </div>
            <div class="card-body">
                <p><strong>Motorista:</strong> <?= $tracking->motorista_name ?? 'N/A' ?></p>
                <p><strong>Creación:</strong> <?= date('d/m/Y H:i', strtotime($tracking->created_at)) ?></p>
                <p><strong>Fecha de entrega:</strong> <?= date('d/m/Y', strtotime($tracking->date)) ?></p>

                <hr>

                <h6>Paquetes</h6>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Vendedor</th>
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

                                // Servicio 1 — Punto fijo
                                if ($d->tipo_servicio == 1) {
                                    $destino = $d->puntofijo_nombre ?? 'Punto no encontrado';
                                }

                                // Servicio 2 — Personalizado
                                elseif ($d->tipo_servicio == 2) {
                                    $destino = $d->destino_personalizado ?: 'N/A';
                                }

                                // Servicio 4 — Casillero (temporal similar a punto fijo)
                                elseif ($d->tipo_servicio == 4) {
                                    $destino = $d->puntofijo_nombre ?: 'Casillero';
                                }

                                // Servicio 3 — Recolecta
                                elseif ($d->tipo_servicio == 3) {
                                    $recolecta = $d->lugar_recolecta_paquete ?: 'Pendiente';
                                    $entrega = $d->destino_personalizado ?: 'Pendiente';
                                }
                                ?>

                                <tr>
                                    <td><?= esc($d->id) ?></td>
                                    <td><?= esc($tipo) ?></td>
                                    <td><?= esc($d->vendedor) ?></td>
                                    <td><?= esc($d->cliente) ?></td>

                                    <td>
                                        <?php if ($d->tipo_servicio == 3): ?>
                                            <strong>Recolecta:</strong> <?= esc($recolecta) ?><br>
                                            <strong>Entrega:</strong> <?= esc($entrega) ?>
                                        <?php else: ?>
                                            <?= esc($destino) ?>
                                        <?php endif; ?>
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