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
$subtotalProductos = 0;

foreach ($detalles as $d) {
    $subtotalProductos += $d->cantidad * $d->precio_unitario;
}

$numeroCorto = !empty($compra->numero_control)
    ? substr($compra->numero_control, -6)
    : 'N/D';

$numeroCompleto = $compra->numero_control ?? 'N/D';

$condicion = $compra->condicion_operacion ?? 1;
$esContado = $condicion == 1;
$diasCredito = (!$esContado) ? ($compra->plazo_credito ?? 0) : 0;
$tipoDte   = $compra->tipo_dte ?? null;
$sigla     = dte_siglas()[$tipoDte] ?? null;
$tipoNombre = dte_tipos()[$tipoDte] ?? 'Documento de compra';
$esCCF  = $tipoDte === '03';
$totalIva = (float)($compra->total_iva ?? 0);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between">

                <div>
                    <h4 class="mb-0">
                        Compra
                        <span class="badge bg-info text-white ms-2">
                            <?= $numeroCorto ?>
                        </span>
                    </h4>

                    <div class="mt-1 d-flex align-items-center gap-2">
                        <h5>Documento de compra: <?= esc($tipoNombre) ?></h5>
                    </div>
                </div>

                <div class="row">

                    <!-- DOCUMENTO -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <small class="text-muted d-block">Nº Control</small>
                            <small class="text-muted d-block mt-1">
                                <?= esc($numeroCompleto) ?>
                            </small>

                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">Condición</small>

                                <?php if ($esContado): ?>
                                    <span class="ml-auto text-muted fw-semibold">
                                        Contado
                                    </span>
                                <?php else: ?>
                                    <span class="ml-auto fw-semibold">
                                        Crédito <?= $diasCredito ?> días
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                    <!-- FINANCIERO -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <?php $saldo = (float)($compra->saldo ?? 0); ?>

                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Estado</small>

                                <?php if (($compra->anulada ?? 0) == 1): ?>
                                    <span class="badge text-dark px-3 py-1" style="background:#e65220;">Anulada</span>
                                <?php elseif ($saldo <= 0): ?>
                                    <span class="badge text-white px-3 py-1" style="background:#15913a;">Pagada</span>
                                <?php elseif ($saldo < $compra->total_pagar): ?>
                                    <span class="badge text-dark px-3 py-1" style="background:#fdda11;">Parcial</span>
                                <?php else: ?>
                                    <span class="badge text-dark px-3 py-1" style="background:#fdda11;">Activa</span>
                                <?php endif; ?>
                            </div>

                            <div class="mt-2 d-flex justify-content-between">
                                <small class="text-muted">Total pagado</small>
                                <span class="fw-semibold text-success">
                                    $<?= number_format($compra->total_pagar - $saldo, 2) ?>
                                </span>
                            </div>

                            <div class="mt-1 d-flex justify-content-between">
                                <small class="text-muted">Saldo pendiente</small>
                                <span class="fw-bold fs-5 <?= $saldo > 0 ? 'text-danger' : 'text-success' ?>">
                                    $<?= number_format($saldo, 2) ?>
                                </span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="card-body">

                <!-- INFO -->
                <div class="row mb-4">

                    <div class="col-md-8">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Proveedor</small>
                            <div class="fw-semibold">
                                <strong><?= esc($compra->proveedor_nombre ?? 'N/D') ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Fecha emisión</small>
                            <div class="fw-semibold">
                                <?= date('d/m/Y', strtotime($compra->fecha_emision)) ?>
                            </div>

                            <small class="text-muted mt-2 d-block">Total</small>
                            <div class="fw-bold fs-5 text-success">
                                $<?= number_format($compra->total_pagar, 2) ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- DETALLE -->
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
                                        <?= esc($d->descripcion) ?>
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

                <!-- TOTALES -->
                <div class="row mt-4">

                    <div class="col-md-6">

                        <?php if (($compra->anulada ?? 0) == 0): ?>
                            <button class="btn btn-outline-danger" id="btnAnularCompra">
                                Eliminar compra
                            </button>
                        <?php else: ?>
                            <div class="alert alert-danger text-center fw-bold">
                                COMPRA ANULADA
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="col-md-4 offset-md-2">

                        <table class="table table-borderless">

                            <tr>
                                <th class="text-end">Subtotal:</th>
                                <td class="text-end">
                                    $<?= number_format($subtotalProductos, 2) ?>
                                </td>
                            </tr>

                            <?php if ($esCCF && $totalIva > 0): ?>
                                <tr>
                                    <th class="text-end text-muted">IVA (13%):</th>
                                    <td class="text-end text-muted">
                                        $<?= number_format($totalIva, 2) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <tr class="border-top">
                                <th class="text-end fs-5">Total:</th>
                                <td class="text-end fs-5 fw-bold text-success">
                                    $<?= number_format($compra->total_pagar, 2) ?>
                                </td>
                            </tr>

                            <?php if ($saldo > 0): ?>
                                <tr>
                                    <th class="text-end text-muted">Total pagado:</th>
                                    <td class="text-end text-muted">
                                        $<?= number_format($compra->total_pagar - $saldo, 2) ?>
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <th class="text-end text-danger">Saldo pendiente:</th>
                                    <td class="text-end fw-bold text-danger">
                                        $<?= number_format($saldo, 2) ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <th class="text-end text-success">Saldo:</th>
                                    <td class="text-end fw-bold text-success">
                                        $<?= number_format(0, 2) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnAnularCompra')?.addEventListener('click', function() {

        Swal.fire({
            title: 'Eliminar compra?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {

            if (!result.isConfirmed) return;

            fetch("<?= base_url('purchases/delete/' . $compra->id) ?>", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {

                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Compra eliminada' : 'Error',
                        text: data.message
                    });

                    if (data.success) {
                        setTimeout(() => {
                            window.location.href = "<?= base_url('purchases') ?>";
                        }, 1200);
                    }

                });

        });

    });
</script>

<?= $this->endSection() ?>