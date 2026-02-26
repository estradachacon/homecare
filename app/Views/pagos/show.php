<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between">

                <div>
                    <h4 class="mb-0">
                        Pago
                        <span class="badge bg-success text-white ms-2">
                            #<?= esc($pago->id) ?>
                        </span>
                    </h4>

                    <div class="fw-bold text-uppercase mt-1" style="letter-spacing: 1px;">
                        <?= esc(ucfirst($pago->forma_pago)) ?>
                    </div>

                    <hr>

                    <div class="fw-bold text-uppercase mt-1" style="letter-spacing: 1px;">
                        <small class="text-muted d-block">Fecha de pago</small>
                        <div class="fw-semibold">
                            <?= date('d/m/Y', strtotime($pago->fecha_pago)) ?>
                        </div>
                    </div>
                </div>

                <!-- PANEL DERECHO -->
                <div class="text-end border rounded px-4 py-3 bg-light d-flex flex-column justify-content-between"
                    style="min-width: 260px; min-height: 120px;">

                    <div class="mt-2 d-flex align-items-center">
                        <small class="text-muted">Estado</small>

                        <?php if ($pago->anulado): ?>
                            <span class="badge text-dark px-3 py-1 ml-auto"
                                style="background: #e65220;">
                                <i class="fa-solid fa-ban me-1"></i> Anulado
                            </span>
                        <?php else: ?>
                            <span class="badge text-white px-3 py-1 ml-auto"
                                style="background: #15913a;">
                                <i class="fa-solid fa-check-circle me-1"></i> Aplicado
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-2 d-flex align-items-center">
                        <small class="text-muted">Total</small>

                        <span class="fw-bold fs-4 ml-auto text-success">
                            $<?= number_format($pago->total, 2) ?>
                        </span>
                    </div>

                </div>

            </div>

            <div class="card-body">


                <div class="row mb-4">

                    <div class="col-md-8">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Cliente</small>
                            <div class="fw-semibold">
                                <strong><?= esc($pago->cliente_nombre ?? 'Sin cliente') ?></strong>
                            </div>

                            <?php if (!empty($pago->numero_recupero)): ?>
                                <small class="text-muted mt-2 d-block">Número recupero</small>
                                <div class="fw-semibold">
                                    <strong><?= esc($pago->numero_recupero) ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($pago->numero_cuenta_bancaria)): ?>
                                <small class="text-muted mt-2 d-block">Cuenta bancaria</small>
                                <div class="fw-semibold">
                                    <strong><?= esc($pago->cuenta_nombre ?? '—') ?></strong>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="bg-light p-3 border rounded h-100">

                            <div class="fw-semibold mb-2">
                                Observaciones
                            </div>

                            <?php if (!empty($pago->observaciones)): ?>
                                <div class="text-body">
                                    <?= nl2br(esc($pago->observaciones)) ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">
                                    Sin observaciones
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>
                <hr>

                <h5>Facturas aplicadas</h5>

                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th class="col-md-4">Factura</th>
                            <th class="col-md-6">Observaciones</th>
                            <th class="col-md-2 text-end">Monto aplicado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalAplicado = 0; ?>
                        <?php if (!empty($facturas)): ?>
                            <?php foreach ($facturas as $f): ?>
                                <?php $totalAplicado += $f->monto; ?>
                                <tr>
                                    <td>
                                        <?= esc($f->numero_control) ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($f->observaciones)): ?>
                                            <?= nl2br(esc($f->observaciones)) ?>
                                        <?php else: ?>
                                            <span>—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">
                                        $<?= number_format($f->monto, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">
                                    No hay facturas aplicadas
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end fs-6">
                                Total aplicado:
                            </th>
                            <th class="text-end fs-5 text-success">
                                $<?= number_format($totalAplicado, 2) ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>