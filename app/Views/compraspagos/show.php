<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">

                <div>
                    <h4 class="mb-1">
                        Pago a Proveedor
                        <span class="badge bg-primary text-white ms-2">
                            <?= esc($pago->numero_pago ?? '#' . $pago->id) ?>
                        </span>
                    </h4>

                    <div class="text-uppercase fw-semibold" style="letter-spacing:1px; font-size:13px;">
                        <?php
                        $formas = [
                            'efectivo'      => ['label' => 'Efectivo',      'class' => 'text-white bg-success'],
                            'transferencia' => ['label' => 'Transferencia', 'class' => 'text-white bg-primary'],
                            'cheque'        => ['label' => 'Cheque',        'class' => 'text-white bg-info'],
                            'tarjeta'       => ['label' => 'Tarjeta',       'class' => 'bg-warning text-dark'],
                        ];
                        $forma = $formas[$pago->forma_pago] ?? ['label' => ucfirst($pago->forma_pago ?? '—'), 'class' => 'bg-secondary'];
                        ?>
                        <span class="badge <?= $forma['class'] ?>"><?= $forma['label'] ?></span>
                    </div>

                    <div class="mt-2">
                        <small class="text-muted">Fecha de pago</small>
                        <div class="fw-semibold">
                            <?= $pago->fecha_pago ? date('d/m/Y', strtotime($pago->fecha_pago)) : '—' ?>
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2 flex-wrap justify-content-between">
                        <a href="<?= base_url('compraspagos') ?>" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> Volver
                        </a>
                        <?php if (!$pago->anulado && tienePermiso('registrar_pagos_a_compras')): ?>
                            <button id="btnAnularPago" class="btn btn-danger btn-sm" data-id="<?= $pago->id ?>">
                                <i class="fa-solid fa-ban me-1"></i>
                                <?= $anulacionParcial ? 'Completar anulación' : 'Anular pago' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Panel derecho -->
                <div class="text-end border rounded px-4 py-3 bg-light d-flex flex-column justify-content-between"
                    style="min-width:240px; min-height:110px;">

                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Estado</small>
                        <?php if ($pago->anulado): ?>
                            <span class="badge bg-danger px-3 py-1">
                                <i class="fa-solid fa-ban me-1"></i> Anulado
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success text-white px-3 py-1">
                                <i class="fa-solid fa-check-circle me-1"></i> Aplicado
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">Total</small>
                        <span class="fw-bold fs-4 text-success">$<?= number_format($pago->total, 2) ?></span>
                    </div>

                    <div class="mt-2 pt-2 border-top text-muted small text-end">
                        Registrado el
                        <span class="fw-semibold"><?= date('d/m/Y H:i', strtotime($pago->created_at)) ?></span>
                    </div>
                </div>

            </div>

            <div class="card-body">

                <!-- Datos del proveedor y cuenta -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Proveedor</small>
                            <div class="fw-semibold fs-6"><?= esc($pago->proveedor_nombre ?? '—') ?></div>

                            <?php if (!empty($pago->numero_cuenta_bancaria)): ?>
                                <small class="text-muted mt-2 d-block">Cuenta bancaria</small>
                                <div class="fw-semibold"><?= esc($pago->cuenta_nombre ?? '—') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 border rounded h-100">
                            <div class="fw-semibold mb-1">Observaciones</div>
                            <?php if (!empty($pago->observaciones)): ?>
                                <div><?= nl2br(esc($pago->observaciones)) ?></div>
                            <?php else: ?>
                                <div class="text-muted">Sin observaciones</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Tabla de compras aplicadas -->
                <h6>Compras aplicadas</h6>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th>Observaciones</th>
                                <th class="text-end" style="width:140px">Monto aplicado</th>
                                <th class="text-center" style="width:110px">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($detalles)): ?>
                                <?php foreach ($detalles as $d): ?>
                                    <tr class="<?= $d->anulado ? 'table-danger' : '' ?>">
                                        <td>
                                            <?= esc($d->numero_control ?? '—') ?>
                                            <a href="<?= base_url('compras/preview/' . $d->compra_id) ?>"
                                                class="btn btn-link p-0 ms-1 verCompra"
                                                data-id="<?= $d->compra_id ?>"
                                                title="Ver compra">
                                                <i class="fa-solid fa-eye text-muted"></i>
                                            </a>
                                        </td>
                                        <td><?= !empty($d->observaciones) ? esc($d->observaciones) : '<span class="text-muted">—</span>' ?></td>
                                        <td class="text-end fw-semibold">$<?= number_format($d->monto, 2) ?></td>
                                        <td class="text-center">
                                            <?php if ($d->anulado): ?>
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
                                    <td colspan="4" class="text-center text-muted">Sin detalle</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end fs-6">Total activo aplicado:</th>
                                <th class="text-end fs-5 text-success">$<?= number_format($totalActivo, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Historial anulaciones -->
                <?php if ($hayAnulaciones): ?>
                    <hr>
                    <h6 class="text-danger mt-3">Historial de anulaciones</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Documento</th>
                                <th class="text-end">Monto</th>
                                <th>Fecha anulación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $d): ?>
                                <?php if ($d->anulado): ?>
                                    <tr>
                                        <td><?= esc($d->numero_control ?? '—') ?></td>
                                        <td class="text-end">$<?= number_format($d->monto, 2) ?></td>
                                        <td><?= $d->anulado_at ? date('d/m/Y H:i', strtotime($d->anulado_at)) : '—' ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php if ($pago->anulado): ?>
                    <div class="alert alert-danger mt-3">
                        <i class="fa-solid fa-ban me-1"></i>
                        Pago anulado el <?= $pago->anulado_at ? date('d/m/Y H:i', strtotime($pago->anulado_at)) : '—' ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal vista previa compra -->
<div class="modal fade" id="modalCompra">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Vista previa de compra</h6>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="compraPreview">Cargando...</div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    // Vista previa compra
    $(document).on('click', '.verCompra', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#compraPreview').html('Cargando...');
        $('#modalCompra').modal('show');
        $.get('<?= base_url('compras/preview') ?>/' + id, function (html) {
            $('#compraPreview').html(html);
        }).fail(function () {
            $('#compraPreview').html('Error al cargar.');
        });
    });

    $('#modalCompra').on('hidden.bs.modal', function () {
        $('#compraPreview').html('');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });

    // Anular
    <?php if (!$pago->anulado && tienePermiso('registrar_pagos_a_compras')): ?>
    document.getElementById('btnAnularPago')?.addEventListener('click', function () {
        const pagoId = this.dataset.id;
        let msg = 'Esta acción revertirá los saldos de las compras aplicadas.';
        <?php if ($anulacionParcial): ?>
        msg = 'Algunas compras ya están anuladas. Solo se revertirán las activas.';
        <?php endif; ?>

        Swal.fire({
            title: '¿Anular este pago?',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('compraspagos/anular/') ?>' + pagoId;
            }
        });
    });
    <?php endif; ?>

});
</script>

<?= $this->endSection() ?>
