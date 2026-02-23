<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
// Calcular subtotal desde los productos
$subtotalProductos = 0;

foreach ($detalles as $d) {
    $subtotalProductos += $d->cantidad * $d->precio_unitario;
}

// Determinar si es Crédito Fiscal (03)
$esCreditoFiscal = ($factura->tipo_dte == '03');
$ivaCalculado = 0;

if ($esCreditoFiscal) {
    $ivaCalculado = $factura->total_pagar - $subtotalProductos;
}
$tipoDoc = dte_descripciones()[dte_siglas()[$factura->tipo_dte] ?? ''] ?? 'Documento';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between">

                <div>
                    <h4 class="mb-0">
                        Factura
                        <span class="badge bg-info text-white ms-2">
                            <?= substr($factura->numero_control, -6) ?>
                        </span>
                    </h4>

                    <div class="fw-bold text-uppercase mt-1" style="letter-spacing: 1px;">
                        <?= esc($tipoDoc) ?>
                    </div>
                </div>

                <small class="text-muted">
                    Nº Control completo: <?= esc($factura->numero_control) ?>
                </small>

            </div>

            <div class="card-body">

                <!-- INFO PRINCIPAL -->
                <div class="row mb-4">

                    <div class="col-md-8">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Cliente</small>
                            <div class="fw-semibold">
                                <strong><?= esc($factura->cliente) ?></strong>
                            </div>

                            <small class="text-muted mt-2 d-block">Vendedor</small>
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

                            <small class="text-muted mt-2 d-block">Total factura</small>
                            <div class="fw-bold fs-5 text-success">
                                $<?= number_format($factura->total_pagar, 2) ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- TABLA DETALLES -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Descripción</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($detalles as $d): ?>

                                <tr>
                                    <td><?= $d->num_item ?></td>

                                    <td>
                                        <?= nl2br(esc($d->descripcion)) ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format($d->cantidad, 2) ?>
                                    </td>

                                    <td class="text-end">
                                        $<?= number_format($d->precio_unitario, 2) ?>
                                    </td>

                                    <td class="text-end">
                                        $<?= number_format($d->cantidad * $d->precio_unitario, 2) ?>
                                    </td>
                                </tr>

                            <?php endforeach ?>

                        </tbody>

                    </table>
                </div>

                <!-- BLOQUE TOTALES -->
                <div class="row mt-4">

                    <!-- BOTÓN ANULAR -->
                    <div class="col-md-6">

                        <?php if (tienePermiso('anular_factura') && ($factura->anulada ?? 0) == 0): ?>

                            <button
                                id="btnAnularFactura"
                                class="btn btn-outline-danger">
                                <i class="fa-solid fa-ban me-1"></i>
                                Anular factura
                            </button>

                        <?php elseif (($factura->anulada ?? 0) == 1): ?>

                            <div class="alert alert-danger text-center fs-3 fw-bold py-3">
                                <i class="fa-solid fa-ban me-2"></i>
                                FACTURA ANULADA
                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- TOTALES -->
                    <div class="col-md-4 offset-md-2">

                        <table class="table table-borderless">

                            <tr>
                                <th class="text-end">Subtotal:</th>
                                <td class="text-end">
                                    $<?= number_format($subtotalProductos, 2) ?>
                                </td>
                            </tr>

                            <?php if ($esCreditoFiscal): ?>

                                <tr>
                                    <th class="text-end">IVA (13%):</th>
                                    <td class="text-end">
                                        $<?= number_format($ivaCalculado, 2) ?>
                                    </td>
                                </tr>

                            <?php endif; ?>

                            <tr class="border-top">
                                <th class="text-end fs-5">Total:</th>
                                <td class="text-end fs-5 fw-bold text-success">
                                    $<?= number_format($factura->total_pagar, 2) ?>
                                </td>
                            </tr>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('btnAnularFactura')?.addEventListener('click', function() {

        Swal.fire({
            title: '¿Anular factura?',
            html: `
            <small>Esta acción no se puede revertir.</small><br>
            <strong>Nº <?= substr($factura->numero_control, -6) ?></strong>
        `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then(result => {

            if (result.isConfirmed) {

                fetch("<?= base_url('facturas/anular/' . $factura->id) ?>", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(r => r.json())
                    .then(data => {

                        Swal.fire({
                            icon: data.success ? 'success' : 'error',
                            title: data.success ? 'Factura anulada' : 'Error',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        if (data.success) {
                            setTimeout(() => location.reload(), 1500);
                        }

                    });

            }

        });

    });
</script>
<?= $this->endSection() ?>