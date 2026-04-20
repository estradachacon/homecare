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

        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">

                <div>
                    <h4 class="mb-1">
                        <?= $dte->tipo_dte === '01' ? 'Factura' : 'Crédito Fiscal' ?>
                        <span class="badge bg-primary ms-1"><?= esc($dte->numero_control) ?></span>
                    </h4>

                    <div class="mt-1">
                        <small class="text-muted">Código de generación</small><br>
                        <code style="font-size:11px;"><?= esc($dte->codigo_generacion) ?></code>
                    </div>

                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('emision-dte') ?>" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> Volver
                        </a>
                        <a href="<?= base_url('emision-dte/' . $dte->id . '/imprimir') ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-print me-1"></i> Imprimir
                        </a>
                        <?php if (!$dte->anulada && tienePermiso('emitir_dte')): ?>
                            <button class="btn btn-sm btn-outline-info" id="btnConsultarEstado" data-id="<?= $dte->id ?>">
                                <i class="fa-solid fa-sync me-1"></i> Consultar MH
                            </button>
                        <?php endif; ?>
                        <?php if (!$dte->anulada && in_array($dte->tipo_dte, ['01','03']) && tienePermiso('emitir_dte')): ?>
                            <a href="<?= base_url('emision-dte/' . $dte->id . '/nc') ?>"
                               class="btn btn-sm btn-warning">
                                <i class="fa-solid fa-file-circle-minus me-1"></i> Generar NC
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Panel resumen -->
                <div class="border rounded px-4 py-3 bg-light text-end" style="min-width:220px;">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted me-3">Ambiente</small>
                        <span class="badge <?= $dte->ambiente === '01' ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <?= $dte->ambiente === '01' ? 'Producción' : 'Pruebas' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted me-3">Estado MH</small>
                        <?php
                            $estadoMh = strtolower($dte->estado_mh ?? '');
                            $badgeMh  = match($estadoMh) {
                                'procesado'   => ['class' => 'bg-success', 'label' => 'Procesado'],
                                'recibido'    => ['class' => 'bg-primary', 'label' => 'Recibido'],
                                'rechazado'   => ['class' => 'bg-danger',  'label' => 'Rechazado'],
                                default       => ['class' => 'bg-secondary', 'label' => ucfirst($estadoMh ?: 'Pendiente')],
                            };
                        ?>
                        <span class="badge <?= $badgeMh['class'] ?>"><?= $badgeMh['label'] ?></span>
                    </div>
                    <?php if ($dte->sello_recibido): ?>
                        <div class="mt-1 border-top pt-1">
                            <small class="text-muted">Sello MH</small><br>
                            <small class="text-success" style="word-break:break-all;"><?= esc(substr($dte->sello_recibido, 0, 40)) ?>...</small>
                        </div>
                    <?php endif; ?>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Total</small>
                        <span class="fw-bold fs-4 text-success">$<?= number_format($dte->total_pagar, 2) ?></span>
                    </div>
                    <div class="text-muted" style="font-size:11px;">
                        <?= date('d/m/Y', strtotime($dte->fecha_emision)) ?>
                        <?= substr($dte->hora_emision, 0, 5) ?>
                    </div>
                </div>

            </div>

            <div class="card-body">

                <!-- Emisor / Receptor -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <small class="text-muted d-block mb-1">Cliente / Receptor</small>
                            <div class="fw-semibold"><?= esc($dte->cliente_nombre ?? 'Consumidor Final') ?></div>
                            <?php if (!empty($dte->numero_documento)): ?>
                                <small class="text-muted">Doc: <?= esc($dte->numero_documento) ?></small>
                            <?php endif; ?>
                            <?php if (!empty($dte->nrc)): ?>
                                <small class="text-muted ms-2">NRC: <?= esc($dte->nrc) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light border rounded p-3 h-100">
                            <small class="text-muted d-block">Condición</small>
                            <span class="fw-semibold">
                                <?= $dte->condicion_operacion == 2 ? 'Crédito (' . $dte->plazo_credito . ' días)' : 'Contado' ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light border rounded p-3 h-100">
                            <small class="text-muted d-block">Tipo de documento</small>
                            <span class="badge <?= $dte->tipo_dte === '01' ? 'bg-info text-dark' : 'bg-primary' ?> fs-6">
                                <?= $dte->tipo_dte === '01' ? 'Factura (01)' : 'Créd. Fiscal (03)' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabla de líneas -->
                <h6>Detalle de productos / servicios</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Descripción</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">P/Unit</th>
                                <th class="text-end">Descuento</th>
                                <th class="text-end">Venta Gravada</th>
                                <th class="text-end">IVA</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($detalles)): ?>
                                <?php foreach ($detalles as $d): ?>
                                    <tr>
                                        <td><?= $d->num_item ?? '—' ?></td>
                                        <td><?= esc($d->descripcion) ?></td>
                                        <td class="text-center">
                                            <small><?= match((int)($d->tipo_item ?? 1)) { 1=>'Bien', 2=>'Servicio', 3=>'Ambos', default=>'—' } ?></small>
                                        </td>
                                        <td class="text-end"><?= number_format($d->cantidad, 2) ?></td>
                                        <td class="text-end">$<?= number_format($d->precio_uni, 2) ?></td>
                                        <td class="text-end"><?= number_format($d->monto_descu ?? 0, 2) ?></td>
                                        <td class="text-end">$<?= number_format($d->venta_gravada, 2) ?></td>
                                        <td class="text-end">$<?= number_format($d->iva_item, 2) ?></td>
                                        <td class="text-end fw-semibold">$<?= number_format($d->venta_gravada + $d->iva_item, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center text-muted">Sin detalle.</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end">Subtotal gravado:</th>
                                <th class="text-end">$<?= number_format($dte->total_gravada, 2) ?></th>
                                <th class="text-end">$<?= number_format($dte->total_iva, 2) ?></th>
                                <th class="text-end text-success fs-6">$<?= number_format($dte->total_pagar, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Sello recibido -->
                <?php if ($dte->sello_recibido): ?>
                    <div class="alert alert-success mt-3 mb-0" style="font-size:12px; word-break:break-all;">
                        <i class="fa-solid fa-stamp me-1"></i>
                        <b>Sello MH:</b> <?= esc($dte->sello_recibido) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fa-solid fa-clock me-1"></i>
                        DTE pendiente de procesamiento por el Ministerio de Hacienda.
                    </div>
                <?php endif; ?>

                <?php if ($dte->anulada): ?>
                    <div class="alert alert-danger mt-2 mb-0">
                        <i class="fa-solid fa-ban me-1"></i>
                        DTE anulado el <?= $dte->fecha_anulacion ? date('d/m/Y H:i', strtotime($dte->fecha_anulacion)) : '—' ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnConsultarEstado')?.addEventListener('click', function () {
    const id = this.dataset.id;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Consultando...';

    fetch('<?= base_url("emision-dte") ?>/' + id + '/estado')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon : 'info',
                    title: 'Estado en MH',
                    html : `<p><b>Estado:</b> ${data.estado ?? '—'}</p>` +
                           (data.sello ? `<p class="small text-muted">Sello: ${data.sello.substring(0,40)}...</p>` : ''),
                    timer: 3000,
                    showConfirmButton: true,
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-sync me-1"></i> Consultar MH';
            }
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo consultar el estado.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-sync me-1"></i> Consultar MH';
        });
});
</script>

<?= $this->endSection() ?>
