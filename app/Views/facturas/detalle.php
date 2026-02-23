<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">

                <h4 class="mb-0">
                    Factura
                    <span class="badge bg-info text-white ms-2">
                        <?= substr($factura->numero_control, -6) ?>
                    </span>
                </h4>

                <small class="text-muted">
                    Nº Control completo: <?= esc($factura->numero_control) ?>
                </small>

            </div>
            <div class="card-body">
                <div class="row mb-4">

                    <div class="col-md-8">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Cliente</small>
                            <div class="fw-semibold">
                                <strong><?= esc($factura->cliente) ?></strong>
                            </div>
                            <small class="text-muted">Vendedor</small>
                            <div class="fw-semibold">
                                <strong><?= esc($factura->vendedor ?? 'N/D') ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Fecha emisión</small>
                            <div class="fw-semibold">
                                <strong>
                                    <?= date('d/m/Y', strtotime($factura->fecha_emision)) ?>
                                </strong>
                            </div>
                            <small class="text-muted">Total factura</small>
                            <div class="fw-bold fs-5 text-success">
                                $<?= number_format($factura->total_pagar, 2) ?>
                            </div>
                        </div>
                    </div>

                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($detalles as $d): ?>
                            <tr>
                                <td><?= $d->num_item ?></td>
                                <td><?= nl2br(esc($d->descripcion)) ?></td>
                                <td><?= $d->cantidad ?></td>
                                <td>$<?= number_format($d->precio_unitario, 2) ?></td>
                                <td>$<?= number_format($d->cantidad * $d->precio_unitario, 2) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>