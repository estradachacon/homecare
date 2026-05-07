<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .box-totales { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 16px; }
    .box-totales .row-total { font-size: 1.05rem; font-weight: 700; }
    .log-entry { border-left: 3px solid #dee2e6; padding-left: 10px; margin-bottom: 8px; }
    .badge-estado-pendiente  { background:#ffc107; color:#000; }
    .badge-estado-facturada  { background:#28a745; color:#fff; }
    .badge-estado-anulada    { background:#dc3545; color:#fff; }
</style>

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
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="header-title mb-0 d-inline">
                        Nota de Pedido — <strong><?= esc($pedido->numero) ?></strong>
                    </h4>
                    &nbsp;
                    <?php
                    $badgeClass = [
                        'pendiente' => 'badge-estado-pendiente',
                        'facturada' => 'badge-estado-facturada',
                        'anulada'   => 'badge-estado-anulada',
                    ][$pedido->estado] ?? 'badge-secondary';
                    $badgeLabel = [
                        'pendiente' => 'Pendiente',
                        'facturada' => 'Facturada',
                        'anulada'   => 'Anulada',
                    ][$pedido->estado] ?? $pedido->estado;
                    ?>
                    <span class="badge <?= $badgeClass ?> px-2 py-1"><?= $badgeLabel ?></span>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($pedido->estado === 'pendiente' && tienePermiso('editar_pedidos')): ?>
                        <a href="<?= base_url('pedidos/' . $pedido->id . '/editar') ?>" class="btn btn-primary btn-sm mr-1">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                    <?php endif; ?>
                    <?php if ($pedido->estado !== 'anulada' && tienePermiso('anular_pedidos')): ?>
                        <button id="btnAnular" class="btn btn-danger btn-sm mr-1" data-id="<?= $pedido->id ?>" data-numero="<?= esc($pedido->numero) ?>">
                            <i class="fa-solid fa-ban"></i> Anular
                        </button>
                    <?php endif; ?>
                    <?php if ($pedido->estado !== 'anulada' && tienePermiso('editar_pedidos')): ?>
                        <button id="btnAsociarFactura" class="btn btn-success btn-sm mr-1">
                            <i class="fa-solid fa-link"></i>
                            <?= $pedido->estado === 'facturada' ? 'Cambiar Factura' : 'Asociar Factura' ?>
                        </button>
                    <?php endif; ?>
                    <a href="<?= base_url('pedidos') ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <!-- Datos del cliente -->
                    <div class="col-md-5">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-2 px-3">
                                <h6 class="mb-2 text-muted"><i class="fa-solid fa-user mr-1"></i> Cliente</h6>
                                <p class="mb-1"><strong><?= esc($pedido->cliente_nombre) ?></strong></p>
                                <?php if ($pedido->cliente_tipo_doc && $pedido->cliente_num_doc): ?>
                                    <p class="mb-1 small text-muted"><?= esc($pedido->cliente_tipo_doc) ?>: <?= esc($pedido->cliente_num_doc) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_nrc): ?>
                                    <p class="mb-1 small text-muted">NRC: <?= esc($pedido->cliente_nrc) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_telefono): ?>
                                    <p class="mb-1 small text-muted"><i class="fa-solid fa-phone mr-1"></i><?= esc($pedido->cliente_telefono) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_correo): ?>
                                    <p class="mb-1 small text-muted"><i class="fa-solid fa-envelope mr-1"></i><?= esc($pedido->cliente_correo) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_direccion): ?>
                                    <p class="mb-0 small text-muted"><i class="fa-solid fa-location-dot mr-1"></i><?= esc($pedido->cliente_direccion) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del pedido -->
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-2 px-3">
                                <h6 class="mb-2 text-muted"><i class="fa-solid fa-file-invoice mr-1"></i> Pedido</h6>
                                <p class="mb-1"><strong>Vendedor:</strong> <?= esc($pedido->vendedor_nombre) ?></p>
                                <p class="mb-1">
                                    <strong>Documento:</strong>
                                    <?php
                                    $docLabel = ['factura' => 'Factura', 'credito_fiscal' => 'Crédito Fiscal', 'nota_remision' => 'Nota de Remisión'];
                                    echo $docLabel[$pedido->tipo_documento] ?? esc($pedido->tipo_documento);
                                    ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Pago:</strong>
                                    <?php if ($pedido->tipo_pago === 'credito'): ?>
                                        Crédito &mdash; <?= $pedido->dias_credito ?> días
                                    <?php else: ?>
                                        Contado
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido->created_at)) ?></p>
                                <?php if ($pedido->factura_numero): ?>
                                    <p class="mb-0">
                                        <strong>Factura:</strong>
                                        <a href="<?= base_url('facturas/' . $pedido->factura_id) ?>">
                                            <?= esc($pedido->factura_numero) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="col-md-3">
                        <div class="box-totales">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal:</span>
                                <span>$<?= number_format($pedido->subtotal, 2) ?></span>
                            </div>
                            <?php if ($pedido->iva > 0): ?>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>IVA (13%):</span>
                                    <span>$<?= number_format($pedido->iva, 2) ?></span>
                                </div>
                            <?php endif; ?>
                            <hr class="my-1">
                            <div class="d-flex justify-content-between row-total">
                                <span>Total:</span>
                                <span class="text-primary">$<?= number_format($pedido->total, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($pedido->notas): ?>
                    <div class="alert alert-light border mb-3">
                        <strong><i class="fa-solid fa-note-sticky mr-1"></i>Notas:</strong> <?= nl2br(esc($pedido->notas)) ?>
                    </div>
                <?php endif; ?>

                <!-- Tabla de productos -->
                <h6 class="text-muted mb-2">Productos</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $i => $d): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($d->producto_codigo) ?></td>
                                    <td><?= esc($d->producto_nombre) ?></td>
                                    <td class="text-end"><?= number_format($d->cantidad, 2) ?></td>
                                    <td class="text-end">$<?= number_format($d->precio_unitario, 2) ?></td>
                                    <td class="text-end">$<?= number_format($d->subtotal, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end fw-bold">$<?= number_format($pedido->subtotal, 2) ?></td>
                            </tr>
                            <?php if ($pedido->iva > 0): ?>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">IVA (13%):</td>
                                    <td class="text-end fw-bold">$<?= number_format($pedido->iva, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold">$<?= number_format($pedido->total, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Log de actividad -->
                <h6 class="text-muted mt-4 mb-2"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Actividad</h6>
                <?php if (empty($log)): ?>
                    <p class="text-muted small">Sin actividad registrada.</p>
                <?php else: ?>
                    <?php foreach ($log as $entry): ?>
                        <div class="log-entry">
                            <span class="fw-bold small"><?= esc($entry->accion) ?></span>
                            <?php if ($entry->detalle): ?>
                                <span class="text-muted small"> — <?= esc($entry->detalle) ?></span>
                            <?php endif; ?>
                            <br>
                            <span class="text-muted" style="font-size:0.78rem;">
                                <?= esc($entry->user_nombre) ?> — <?= date('d/m/Y H:i', strtotime($entry->created_at)) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asociar Factura -->
<div class="modal fade" id="modalFactura" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asociar Factura</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Seleccione la factura</label>
                    <select id="selectFactura" class="form-control" style="width:100%"></select>
                </div>
                <div id="facturaError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarFactura">
                    <i class="fa-solid fa-link"></i> Asociar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    // ── Anular ─────────────────────────────────────────────────────────────
    $('#btnAnular').on('click', function () {
        const id     = $(this).data('id');
        const numero = $(this).data('numero');

        Swal.fire({
            title: '¿Anular nota?',
            html: `¿Está seguro de anular <strong>${numero}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch(`<?= base_url('pedidos') ?>/${id}/anular`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Anulada', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    });

    // ── Asociar factura ────────────────────────────────────────────────────
    $('#btnAsociarFactura').on('click', function () {
        $('#facturaError').addClass('d-none').text('');
        $('#modalFactura').modal('show');
    });

    $('#selectFactura').select2({
        dropdownParent: $('#modalFactura'),
        language: 'es',
        placeholder: 'Buscar factura...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '<?= base_url('pedidos/facturas-cliente/' . $pedido->cliente_id) ?>',
            dataType: 'json',
            delay: 300,
            data: params => ({ q: params.term || '' }),
            processResults: data => ({ results: data.results }),
            cache: true,
        },
    });

    $('#btnConfirmarFactura').on('click', function () {
        const facturaId = $('#selectFactura').val();
        if (!facturaId) {
            $('#facturaError').removeClass('d-none').text('Seleccione una factura.');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Asociando...');

        fetch(`<?= base_url('pedidos/' . $pedido->id . '/asociar-factura') ?>`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ factura_id: parseInt(facturaId) }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Listo', data.message, 'success').then(() => location.reload());
            } else {
                $('#facturaError').removeClass('d-none').text(data.message);
            }
        })
        .finally(() => {
            $('#btnConfirmarFactura').prop('disabled', false).html('<i class="fa-solid fa-link"></i> Asociar');
        });
    });
});
</script>

<?= $this->endSection() ?>
