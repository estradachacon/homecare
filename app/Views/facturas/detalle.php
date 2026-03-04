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
$numeroCorto = !empty($factura->numero_control)
    ? substr($factura->numero_control, -6)
    : 'N/D';

$numeroCompleto = $factura->numero_control ?? 'N/D';

// Tipo de venta (catalogo)
$tipoVenta = $factura->tipo_venta_nombre ?? null;
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
                    <?php if (!empty($factura->codigo_generacion_relacionado)): ?>
                        <div class="mt-2 border rounded px-3 py-2 bg-light" style="max-width:340px;">
                            <div class="d-flex justify-content-between">

                                <small class="text-muted">
                                    Documento asociado
                                </small>

                                <?php if (!empty($facturaRelacionada)): ?>

                                    <a href="<?= base_url('facturas/' . $facturaRelacionada->id) ?>"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="fw-bold">

                                <?php if (!empty($facturaRelacionada)): ?>

                                    <?php $siglaRelacionado = dte_siglas()[$facturaRelacionada->tipo_dte] ?? 'DOC'; ?>

                                    <?= esc($siglaRelacionado) ?>
                                    <?= substr($facturaRelacionada->numero_control, -6) ?>

                                <?php else: ?>

                                    N/D

                                <?php endif; ?>

                            </div>

                        </div>

                    <?php endif; ?>
                    <?php if (!empty($notasCredito)): ?>

                        <div class="mt-2 border rounded px-3 py-2 bg-light" style="max-width:340px;">

                            <div class="d-flex justify-content-between">

                                <small class="text-muted">
                                    Nota<?= count($notasCredito) > 1 ? 's' : '' ?> de crédito aplicada<?= count($notasCredito) > 1 ? 's' : '' ?>
                                </small>

                                <span class="badge bg-warning text-dark">
                                    <?= count($notasCredito) ?>
                                </span>

                            </div>

                            <div class="mt-1">

                                <?php foreach ($notasCredito as $nc): ?>

                                    <div class="d-flex justify-content-between mb-1">

                                        <div class="fw-bold">

                                            <?php $siglaNC = dte_siglas()[$nc->tipo_dte] ?? 'NC'; ?>

                                            <?= esc($siglaNC) ?>
                                            <?= substr($nc->numero_control, -6) ?>

                                        </div>

                                        <a href="<?= base_url('facturas/' . $nc->id) ?>"
                                            class="btn btn-sm btn-outline-secondary">

                                            <i class="fa-solid fa-eye"></i>

                                        </a>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endif; ?>
                </div>

                <div class="row">
                    <!-- PANEL DOCUMENTO -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <small class="text-muted d-block">Nº Control</small>
                            <small class="text-muted d-block mt-1">
                                <?= esc($numeroCompleto) ?>
                            </small>

                            <?php if (!empty($tipoVenta)): ?>
                                <div class="mt-2 d-flex align-items-center">
                                    <small class="text-muted">
                                        Tipo de venta:
                                    </small>

                                    <span class="badge text-dark px-3 py-1 ml-auto"
                                        style="background: #9efdc9;">
                                        <?= esc($tipoVenta) ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php
                            $condicionOperacion = $factura->condicion_operacion ?? 1; // 1 por defecto contado
                            $esContado = $condicionOperacion == 1;

                            $diasCondicion = (!$esContado)
                                ? (int)($factura->plazo_credito ?? 0)
                                : 0;
                            ?>

                            <!-- CONDICIÓN DE PAGO -->
                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">
                                    Condición:
                                </small>

                                <?php if ($esContado): ?>

                                    <span class="ml-auto text-muted fw-semibold">
                                        Contado
                                    </span>

                                <?php else: ?>

                                    <span class="ml-auto fw-semibold">
                                        Crédito <?= $diasCondicion ?> días
                                    </span>

                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                    <!-- PANEL FINANCIERO -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <?php
                            $saldoPendiente = $factura->saldo ?? 0;
                            $fechaEmision = strtotime($factura->fecha_emision);
                            $hoy = time();
                            $diasTranscurridos = floor(($hoy - $fechaEmision) / 86400);
                            ?>

                            <!-- ESTADO -->
                            <div class="d-flex align-items-center">
                                <small class="text-muted">Estado</small>

                                <?php if (($factura->anulada ?? 0) == 1): ?>

                                    <span class="badge text-dark px-3 py-1 ml-auto"
                                        style="background: #e65220;">
                                        <i class="fa-solid fa-ban me-1"></i> Anulada
                                    </span>

                                <?php elseif ($saldoPendiente == 0): ?>

                                    <span class="badge text-white px-3 py-1 ml-auto"
                                        style="background: #15913a;">
                                        <i class="fa-solid fa-check-circle me-1"></i> Pagada
                                    </span>

                                <?php else: ?>

                                    <span class="badge text-dark px-3 py-1 ml-auto"
                                        style="background: #fdda11;">
                                        Activa
                                    </span>

                                <?php endif; ?>
                            </div>

                            <?php if (($factura->anulada ?? 0) == 0 && $saldoPendiente == 0 && !empty($factura->fecha_ultimo_pago)): ?>

                                <div class="mt-1 text-muted small text-end">
                                    Pagada el
                                    <strong>
                                        <?= date('d/m/Y', strtotime($factura->fecha_ultimo_pago)) ?>
                                    </strong>
                                </div>

                            <?php endif; ?>

                            <!-- SALDO -->
                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">Saldo pendiente</small>

                                <?php if (($factura->anulada ?? 0) == 1): ?>

                                    <span class="fw-bold text-muted ml-auto">—</span>

                                <?php else: ?>

                                    <span class="fw-bold fs-5 ml-auto
            <?= $saldoPendiente > 0 ? 'text-danger' : 'text-success' ?>">
                                        $<?= number_format($saldoPendiente, 2) ?>
                                    </span>

                                <?php endif; ?>
                            </div>

                            <!-- DÍAS TRANSCURRIDOS SOLO SI ES CRÉDITO -->
                            <?php if (!$esContado): ?>

                                <div class="mt-2 d-flex align-items-center">
                                    <small class="text-muted">Transcurrido</small>

                                    <span class="ml-auto 
            <?= $diasTranscurridos > $diasCondicion ? 'text-danger fw-bold' : '' ?>">
                                        <?= $diasTranscurridos ?> días
                                    </span>
                                </div>

                            <?php endif; ?>
                        </div>
                    </div>

                </div>

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
                <!-- HISTORIAL DE PAGOS -->
                <?php if (!empty($pagos)): ?>

                    <div class="mt-4">

                        <button class="btn btn-outline-secondary btn-sm"
                            type="button"
                            data-toggle="collapse"
                            data-target="#tablaPagosFactura"
                            aria-expanded="false"
                            aria-controls="tablaPagosFactura">

                            <i class="fa fa-money-bill-wave mr-1"></i>
                            Pagos aplicados
                            <span class="badge badge-success ml-1">
                                <?= count($pagos) ?>
                            </span>

                        </button>

                        <div class="collapse mt-3" id="tablaPagosFactura">

                            <div class="table-responsive">

                                <table class="table table-sm table-bordered table-hover align-middle">

                                    <thead class="table-light">
                                        <tr>
                                            <th>Pago</th>
                                            <th>Fecha</th>
                                            <th>Forma</th>
                                            <th class="text-end">Monto</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>

                                    <?php
                                    $totalAplicado = 0;
                                    $totalAnulado = 0;
                                    ?>

                                    <tbody>

                                        <?php foreach ($pagos as $p):

                                            $esAnulado = ($p->anulado == 1 || $p->pago_anulado == 1);

                                            if ($esAnulado) {
                                                $totalAnulado += $p->monto;
                                            } else {
                                                $totalAplicado += $p->monto;
                                            }
                                        ?>

                                            <tr class="<?= $esAnulado ? 'table-danger text-muted' : '' ?>">

                                                <td>
                                                    <span class="badge badge-secondary">
                                                        #<?= $p->pago_id ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?= date('d/m/Y', strtotime($p->fecha_pago)) ?>
                                                </td>

                                                <td>
                                                    <?= ucfirst($p->forma_pago) ?>
                                                </td>

                                                <td class="text-end">
                                                    $<?= number_format($p->monto, 2) ?>
                                                </td>

                                                <td class="text-center">

                                                    <?php if ($esAnulado): ?>

                                                        <span class="badge badge-danger">
                                                            Anulado
                                                        </span>

                                                    <?php else: ?>

                                                        <span class="badge badge-success">
                                                            Aplicado
                                                        </span>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>

                                        <?php endforeach ?>

                                    </tbody>

                                    <tfoot>

                                        <tr class="table-light">
                                            <th colspan="3" class="text-end">Total aplicado</th>
                                            <th class="text-end text-success">
                                                $<?= number_format($totalAplicado, 2) ?>
                                            </th>
                                            <th></th>
                                        </tr>

                                        <tr class="table-light">
                                            <th colspan="3" class="text-end">Total anulado</th>
                                            <th class="text-end text-danger">
                                                $<?= number_format($totalAnulado, 2) ?>
                                            </th>
                                            <th></th>
                                        </tr>

                                        <tr class="table-secondary font-weight-bold">
                                            <th colspan="3" class="text-end">Total histórico</th>
                                            <th class="text-end">
                                                $<?= number_format($totalAplicado + $totalAnulado, 2) ?>
                                            </th>
                                            <th></th>
                                        </tr>

                                    </tfoot>

                                </table>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('btnAnularFactura')?.addEventListener('click', function() {

        fetch("<?= base_url('facturas/checkPagos/' . $factura->id) ?>", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {

                // 🔴 SI TIENE PAGOS
                if (data.tiene_pagos) {

                    let pagosHtml = '<ul class="list-group text-start mb-3">';

                    data.pagos.forEach(p => {
                        pagosHtml += `
                    <li class="list-group-item d-flex justify-content-between">
                        <div>
                            <strong>Pago #${p.pago_id}</strong><br>
                            <small>${p.fecha_pago} - ${p.forma_pago}</small>
                        </div>
                        <span class="badge bg-success">
                            $${parseFloat(p.monto).toFixed(2)}
                        </span>
                    </li>
                `;
                    });

                    pagosHtml += '</ul>';

                    Swal.fire({
                        title: 'Factura con pagos aplicados',
                        html: `
                    <div class="text-start">
                        ${pagosHtml}
                        <p class="mt-3">
                            Total pagado: <strong>$${data.total_pagado}</strong>
                        </p>
                        <p class="text-danger fw-bold">
                            ⚠ Si continúa, se revertirán estos pagos y los movimientos bancarios.
                        </p>
                    </div>
                `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Revertir y anular',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#dc3545',
                        width: 600
                    }).then(result => {
                        if (result.isConfirmed) {
                            ejecutarAnulacion(true);
                        }
                    });

                }
                // 🟢 SI NO TIENE PAGOS
                else {

                    Swal.fire({
                        title: '¿Anular factura?',
                        text: 'Esta acción marcará la factura como anulada.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, anular',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#dc3545'
                    }).then(result => {
                        if (result.isConfirmed) {
                            ejecutarAnulacion(false);
                        }
                    });

                }

            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'No se pudo verificar los pagos.', 'error');
            });

        function ejecutarAnulacion(revertirPagos = false) {

            fetch("<?= base_url('facturas/anular/' . $factura->id) ?>", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        revertir_pagos: revertirPagos
                    })
                })
                .then(r => r.json())
                .then(data => {

                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Factura anulada' : 'Error',
                        text: data.message
                    });

                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }

                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo procesar la anulación.', 'error');
                });

        }

    });
</script>
<?= $this->endSection() ?>