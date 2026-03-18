<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm">

    <!-- HEADER -->
    <div class="card-header d-flex justify-content-between">

        <div>
            <h4 class="mb-0">
                Comisión
                <span class="badge bg-success text-white ms-2">
                    #<?= $comision->id ?>
                </span>
            </h4>

            <div class="fw-bold text-uppercase mt-1">
                Resumen de comisión
            </div>

            <small class="text-muted">
                Periodo:
                <?= date('d/m/Y', strtotime($comision->fecha_inicio)) ?>
                -
                <?= date('d/m/Y', strtotime($comision->fecha_fin)) ?>
            </small>
        </div>

        <div class="text-end border rounded px-3 py-2 bg-light">

            <small class="text-muted d-block">Estado</small>

            <span class="badge 
                <?= $comision->estado == 'cerrado' ? 'bg-success' : 'bg-warning text-dark' ?>">
                <?= ucfirst($comision->estado ?? 'pendiente') ?>
            </span>

            <div class="mt-2">
                <small class="text-muted">Fecha creación</small><br>
                <strong><?= date('d/m/Y H:i', strtotime($comision->created_at)) ?></strong>
            </div>

        </div>

    </div>

    <div class="card-body">

        <!-- RESUMEN -->
        <div class="row mb-4">

            <div class="col-md-4">
                <div class="p-3 border rounded text-center">
                    <small class="text-muted">Total ventas</small>
                    <div class="fw-bold fs-4 text-primary">
                        $<?= number_format($comision->total_ventas, 2) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 border rounded text-center">
                    <small class="text-muted">Total comisión</small>
                    <div class="fw-bold fs-4 text-success">
                        $<?= number_format($comision->total_comision, 2) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 border rounded text-center">
                    <small class="text-muted">% Promedio</small>
                    <div class="fw-bold fs-4 text-dark">
                        <?= number_format($comision->porcentaje_promedio, 2) ?>%
                    </div>
                </div>
            </div>

        </div>

        <!-- TABLA DETALLES -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">

                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Documento</th>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Venta</th>
                        <th class="text-end">%</th>
                        <th class="text-end">Comisión</th>
                        <th class="text-center">Origen</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($detalles as $i => $d): ?>

                        <tr>

                            <td><?= $i + 1 ?></td>

                            <td>
                                <?= substr($d->numero_control ?? '', -6) ?><br>
                                <small class="text-muted">
                                    <?= date('d/m/Y', strtotime($d->fecha_emision)) ?>
                                </small>
                            </td>

                            <td>
                                <?= esc($d->producto ?? 'N/D') ?>
                            </td>

                            <td class="text-center">
                                <?= number_format($d->cantidad, 0) ?>
                            </td>

                            <td class="text-end">
                                $<?= number_format($d->precio_sin_iva, 2) ?>
                            </td>

                            <td class="text-end">
                                $<?= number_format($d->total_linea, 2) ?>
                            </td>

                            <td class="text-end">
                                <?= number_format($d->comision_aplicada, 2) ?>%
                            </td>

                            <td class="text-end text-success">
                                $<?= number_format($d->monto_comision, 2) ?>
                            </td>

                            <td class="text-center">

                                <?php
                                $color = match ($d->origen_comision) {
                                    'producto' => 'success  text-white',
                                    'vendedor' => 'primary  text-white',
                                    'general'  => 'secondary text-white',
                                    default    => 'dark  text-white'
                                };
                                ?>

                                <span class="badge bg-<?= $color ?>">
                                    <?= strtoupper($d->origen_comision) ?>
                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>
        </div>

        <!-- FOOTER -->
        <div class="row mt-4">

            <div class="col-md-6">
                <a href="<?= base_url('comisiones') ?>" class="btn btn-secondary">
                    ← Volver
                </a>
            </div>

            <div class="col-md-6 text-end">

                <div class="fw-bold">
                    Total comisión:
                    <span class="text-success fs-5">
                        $<?= number_format($comision->total_comision, 2) ?>
                    </span>
                </div>

            </div>

        </div>

    </div>
</div>

<?= $this->endSection() ?>