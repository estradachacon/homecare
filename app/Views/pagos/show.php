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
                    <?php if (!$pago->anulado): ?>
                        <div class="mt-3 text-end">
                            <button id="btnAnularPago"
                                class="btn btn-danger btn-sm"
                                data-id="<?= $pago->id ?>">

                                <i class="fa-solid fa-ban me-1"></i>

                                <?php if (!empty($anulacionParcial) && $anulacionParcial): ?>
                                    Completar anulación del pago
                                <?php else: ?>
                                    Anular transacción
                                <?php endif; ?>

                            </button>
                        </div>
                    <?php endif; ?>
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
                    <div class="mt-3 pt-2 border-top text-muted small text-end">
                        Creado el
                        <span class="fw-semibold">
                            <?= date('d/m/Y', strtotime($pago->created_at)) ?>
                        </span>
                        a las
                        <span class="fw-semibold">
                            <?= date('H:i:s', strtotime($pago->created_at)) ?>
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
                            <th class="col-md-3">Factura</th>
                            <th class="col-md-3">Observaciones</th>
                            <th class="col-md-2">Retención 10%</th>
                            <th class="col-md-2 text-end">Monto aplicado</th>
                            <th class="col-md-2 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalAplicado = 0; ?>
                        <?php $hayAnulaciones = false; ?>

                        <?php if (!empty($facturas)): ?>

                            <?php foreach ($facturas as $f): ?>

                                <?php
                                if (!$f->anulado) {
                                    $totalAplicado += $f->monto;
                                } else {
                                    $hayAnulaciones = true;
                                }
                                $retMonto = (float)($f->retencion_monto ?? 0);
                                ?>

                                <tr class="<?= $f->anulado ? 'table-danger' : '' ?>">
                                    <td>
                                        <span class="font-weight-bold"><?= substr(esc($f->numero_control), -6) ?></span>
                                        <small class="text-muted d-block" style="font-size:.75rem;"><?= esc($f->numero_control) ?></small>
                                    </td>

                                    <td>
                                        <?php if (!empty($f->observaciones)): ?>
                                            <?= nl2br(esc($f->observaciones)) ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($retMonto > 0): ?>
                                            <span class="text-danger font-weight-bold">
                                                $<?= number_format($retMonto, 2) ?>
                                            </span>
                                            <?php if (!empty($f->ret_cuenta_codigo)): ?>
                                                <small class="text-muted d-block" style="font-size:.72rem;">
                                                    <?= esc($f->ret_cuenta_codigo) ?> <?= esc($f->ret_cuenta_nombre) ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end">
                                        $<?= number_format($f->monto, 2) ?>
                                        <?php if ($retMonto > 0): ?>
                                            <small class="text-success d-block">
                                                Recibido: $<?= number_format($f->monto - $retMonto, 2) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($f->anulado): ?>
                                            <span class="badge bg-danger">
                                                <i class="fa-solid fa-ban me-1"></i> Anulado
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-check me-1"></i> Aplicado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    No hay facturas aplicadas
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                    <tfoot class="table-light">
                        <?php if (!empty($totalRetencion) && $totalRetencion > 0): ?>
                        <tr>
                            <td colspan="3" class="text-end text-muted small">Monto bruto aplicado:</td>
                            <td class="text-end text-muted">$<?= number_format($totalAplicado, 2) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end text-danger small">Retenciones aplicadas:</td>
                            <td class="text-end text-danger">— $<?= number_format($totalRetencion, 2) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th colspan="3" class="text-end">Recibido en efectivo:</th>
                            <th class="text-end text-success" style="font-size:1.1rem;">$<?= number_format($totalAplicado - $totalRetencion, 2) ?></th>
                            <th></th>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <th colspan="4" class="text-end">Total aplicado:</th>
                            <th class="text-end text-success" style="font-size:1.1rem;">$<?= number_format($totalAplicado, 2) ?></th>
                        </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
                <?php if (!empty($hayAnulaciones) && $hayAnulaciones): ?>

                    <hr>

                    <h6 class="mt-4 text-danger">
                        Historial de anulaciones
                    </h6>

                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Fecha anulación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($facturas as $f): ?>
                                <?php if ($f->anulado): ?>
                                    <tr>
                                        <td><?= esc($f->numero_control) ?></td>
                                        <td class="text-end">$<?= number_format($f->monto, 2) ?></td>
                                        <td>
                                            <?= $f->anulado_at
                                                ? date('d/m/Y H:i:s', strtotime($f->anulado_at))
                                                : '—' ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const btn = document.getElementById('btnAnularPago');

        if (btn) {

            btn.addEventListener('click', function() {

                const pagoId = this.dataset.id;

                let mensaje = "Esta acción revertirá los saldos aplicados.";

                <?php if (!empty($anulacionParcial) && $anulacionParcial): ?>
                    mensaje = "Algunas facturas ya están anuladas. Se anularán únicamente las facturas activas restantes.";
                <?php endif; ?>

                Swal.fire({
                    title: '¿Anular este pago?',
                    text: mensaje,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {

                    if (result.isConfirmed) {
                        window.location.href = "<?= base_url('payments/anular/') ?>" + pagoId;
                    }

                });

            });

        }

    });
</script>
<?= $this->endSection() ?>