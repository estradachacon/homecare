<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .info-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .info-value {
        font-size: 18px;
        font-weight: 600;
    }
</style>

<?php
$stock = $stock ?? 0;
?>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between">

                <div>
                    <h4 class="mb-0">
                        Producto
                        <span class="badge bg-info text-white ms-2">
                            <?= esc($producto->codigo ?? 'SIN CODIGO') ?>
                        </span>
                    </h4>

                    <div class="fw-bold text-uppercase mt-1" style="letter-spacing: 1px;">
                        <?= esc($producto->descripcion) ?>
                    </div>
                </div>

                <div class="row">

                    <!-- PANEL PRODUCTO -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <small class="text-muted d-block">Estado</small>

                            <?php if (($producto->activo ?? 1) == 1): ?>
                                <span class="badge text-white px-3 py-1" style="background:#15913a;">
                                    Activo
                                </span>
                            <?php else: ?>
                                <span class="badge text-dark px-3 py-1" style="background:#e65220;">
                                    Inactivo
                                </span>
                            <?php endif; ?>

                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">Tipo</small>

                                <span class="ml-auto fw-semibold">
                                    <?= esc($producto->tipo ?? 'N/D') ?>
                                </span>
                            </div>

                        </div>
                    </div>

                    <!-- PANEL STOCK -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <div class="d-flex align-items-center">
                                <small class="text-muted">Stock actual</small>

                                <span class="fw-bold fs-5 ml-auto 
                                    <?= $stock > 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($stock, 2) ?>
                                </span>
                            </div>

                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">Costo promedio</small>

                                <span class="fw-bold ml-auto">
                                    $<?= number_format($producto->costo_promedio ?? 0, 4) ?>
                                </span>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="card-body">

                <!-- INFO -->
                <div class="row mb-4">

                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Código</small>
                            <div class="fw-semibold">
                                <?= esc($producto->codigo ?? 'N/D') ?>
                            </div>

                            <small class="text-muted mt-2 d-block">Descripción</small>
                            <div class="fw-semibold">
                                <?= esc($producto->descripcion ?? 'N/D') ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100">

                            <small class="text-muted">Costo promedio actual</small>
                            <div class="fw-bold fs-5 text-primary">
                                $<?= number_format($producto->costo_promedio ?? 0, 4) ?>
                            </div>

                            <small class="text-muted mt-2 d-block">Última actualización</small>
                            <div class="fw-semibold">
                                <?= !empty($producto->updated_at) ? date('d/m/Y H:i', strtotime($producto->updated_at)) : 'N/D' ?>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- KARDEX -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tipo</th>
                                <th>Referencia</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Costo</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($movimientos)): ?>
                                <?php foreach ($movimientos as $m): ?>

                                    <tr>
                                        <td><?= $m->id ?></td>

                                        <td>
                                            <span class="badge 
                                                <?= $m->tipo_movimiento == 'ENTRADA' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $m->tipo_movimiento ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= esc($m->referencia_tipo) ?> #<?= $m->referencia_id ?>
                                        </td>

                                        <td class="text-end">
                                            <?= number_format($m->cantidad, 2) ?>
                                        </td>

                                        <td class="text-end">
                                            $<?= number_format($m->costo_unitario, 4) ?>
                                        </td>

                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($m->created_at)) ?>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Sin movimientos
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>