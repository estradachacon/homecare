<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="mb-0">
                    <?= esc($cliente->nombre) ?>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-4">
                        <small class="text-muted">Documento</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->numero_documento) ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">NRC</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->nrc ?? 'N/D') ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">Teléfono</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->telefono ?? 'N/D') ?>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- FACTURAS -->

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Facturas del cliente</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th class="col-2">Correlativo</th>
                                <th>Fecha y hora de emisión</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th class="text-center">Estado</th>
                                <th style="width:80px">Acción</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($facturas): ?>

                                <?php foreach ($facturas as $f): ?>

                                    <tr class="<?= ($f->anulada ?? 0) == 1 ? 'table-danger' : '' ?>">
                                        <td><?= $f->id ?></td>

                                        <td>
                                            <span class="badge bg-info text-white badge-lg">
                                                <?= substr($f->numero_control, -6) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= date('d/m/Y', strtotime($f->fecha_emision)) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i:s', strtotime($f->hora_emision)) ?>
                                        </td>

                                        <td class="text-end fw-bold">
                                            $<?= number_format($f->total_pagar, 2) ?>
                                        </td>
                                        <td>
                                            $<?= number_format($f->saldo, 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (($f->anulada ?? 0) == 1): ?>
                                                <span class="badge bg-danger text-white">
                                                    Anulada
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white">
                                                    Activa
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('facturas/' . $f->id) ?>"
                                                class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>

                                <?php endforeach ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Este cliente no tiene facturas.
                                    </td>
                                </tr>

                            <?php endif ?>

                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>