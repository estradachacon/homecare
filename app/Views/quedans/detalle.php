<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php

$total = 0;
$totalSaldo = 0;
$totalFacturas = count($detalles);
$hoy = time();

foreach ($detalles as $d) {

    $total += $d->monto_aplicado;

    if (!($d->anulada ?? 0)) {
        $totalSaldo += $d->saldo;
    }
}
?>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between">

                <div>
                    <h4 class="mb-0">
                        Quedan
                        <span class="badge bg-info text-white ms-2">
                            <?= esc($quedan->numero_quedan) ?>
                        </span>
                    </h4>

                    <div class="fw-bold text-uppercase mt-1">
                        Control de cuentas por cobrar
                    </div>
                    <?php if (!$quedan->anulado): ?>

                        <?php if (tienePermiso('anular_quedans') && !$quedan->anulado): ?>

                            <button id="btnAnularQuedan" class="btn btn-outline-danger">
                                <i class="fa-solid fa-ban"></i>
                                Anular Quedan
                            </button>

                        <?php endif; ?>

                    <?php else: ?>

                        <div class="alert alert-danger text-center fw-bold">
                            QUEDAN ANULADO
                        </div>

                    <?php endif; ?>
                </div>


                <div class="row">

                    <!-- PANEL DOCUMENTO -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <small class="text-muted d-block">Cliente</small>

                            <strong>
                                <?= esc($quedan->cliente_nombre ?? 'N/D') ?>
                            </strong>

                            <div class="mt-2">

                                <small class="text-muted">
                                    Fecha emisión
                                </small>

                                <div class="fw-semibold">
                                    <?= date('d/m/Y', strtotime($quedan->fecha_emision)) ?>
                                </div>

                            </div>

                        </div>
                    </div>


                    <!-- PANEL FINANCIERO -->
                    <div class="col-md-6">

                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <div class="d-flex align-items-center">

                                <small class="text-muted">
                                    Estado
                                </small>

                                <?php

                                $fechaPago = strtotime($quedan->fecha_pago);
                                $estaVencido = ($hoy > $fechaPago);

                                ?>
                                <?php if ($quedan->anulado): ?>

                                    <span class="badge text-white px-3 py-1 ml-auto"
                                        style="background:#e65220;">
                                        Anulado
                                    </span>

                                <?php elseif ($totalSaldo == 0): ?>

                                    <span class="badge text-white px-3 py-1 ml-auto"
                                        style="background:#15913a;">
                                        Pagado
                                    </span>

                                <?php elseif ($estaVencido): ?>

                                    <span class="badge text-white px-3 py-1 ml-auto"
                                        style="background:#e65220;">
                                        Vencido
                                    </span>

                                <?php else: ?>

                                    <span class="badge text-dark px-3 py-1 ml-auto"
                                        style="background:#fdda11;">
                                        Pendiente
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div class="mt-2 d-flex align-items-center">

                                <small class="text-muted">
                                    Fecha promesa de pago:
                                </small>

                                <span class="ml-auto fw-semibold">
                                    <?= date('d/m/Y', strtotime($quedan->fecha_pago)) ?>
                                </span>

                            </div>


                            <div class="mt-2 d-flex align-items-center">

                                <small class="text-muted">
                                    Total aplicado en Quedan
                                </small>

                                <span class="fw-bold fs-5 text-success ml-auto">
                                    $<?= number_format($quedan->total_aplicado, 2) ?>
                                </span>

                            </div>
                            <?php if ($quedan->anulado): ?>

                                <div class="mt-3 border-top pt-2">

                                    <div class="d-flex align-items-center">
                                        <small class="text-muted">
                                            Anulado por:
                                        </small>

                                        <span class="ml-auto fw-semibold">
                                            <?= esc($quedan->usuario_anulo ?? 'N/D') ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($quedan->fecha_anulacion)): ?>
                                        <div class="d-flex align-items-center mt-1">
                                            <small class="text-muted">
                                                Fecha anulación:
                                            </small>

                                            <span class="ml-auto">
                                                <?= date('d/m/Y H:i', strtotime($quedan->fecha_anulacion)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                </div>

                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>


            <div class="card-body">

                <!-- TABLA DETALLE FACTURAS -->

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">
                            <tr>

                                <th>Factura</th>
                                <th>Fecha</th>
                                <th class="text-center">Condición</th>
                                <th class="text-center">Transcurrido</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-end">Aplicado</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Menú</th>

                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($detalles)): ?>

                                <?php foreach ($detalles as $d): ?>
                                    <?php

                                    $fechaFactura = strtotime($d->fecha_emision);

                                    $esAnulada = ($d->anulada ?? 0) == 1;
                                    $esPagada  = ($d->saldo == 0 && !$esAnulada);

                                    // 🔥 lógica SIN romper nada
                                    if ($esAnulada && !empty($quedan->fecha_anulacion)) {
                                        $fechaFin = strtotime($quedan->fecha_anulacion);
                                    } elseif ($esPagada) {
                                        $fechaFin = strtotime($quedan->fecha_pago);
                                    } else {
                                        $fechaFin = $hoy;
                                    }

                                    $dias = max(0, floor(($fechaFin - $fechaFactura) / 86400));
                                    ?>
                                    <tr class="<?= $esAnulada ? 'table-danger text-muted' : '' ?>">

                                        <td>
                                            <strong><?= substr($d->numero_control, -6) ?></strong>
                                        </td>

                                        <td class="text-center">
                                            <?= date('d/m/Y', strtotime($d->fecha_emision)) ?>
                                        </td>

                                        <td class="text-center">

                                            <?php if (($d->condicion_operacion ?? 1) == 1): ?>

                                                <span class="badge bg-success text-white">
                                                    Contado
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-warning text-dark">
                                                    Crédito
                                                </span>

                                            <?php endif ?>

                                        </td>

                                        <td class="text-center 
                                            <?= $dias > 30 ? 'text-danger fw-bold' : '' ?>">
                                            <?= $dias ?> días
                                        </td>

                                        <td class="text-end">
                                            $<?= number_format($d->total_pagar, 2) ?>
                                        </td>

                                        <td class="text-end 
                                            <?= $d->saldo > 0 ? 'text-danger' : 'text-success' ?>">

                                            $<?= number_format($d->saldo, 2) ?>

                                        </td>

                                        <td class="text-end fw-bold">
                                            $<?= number_format($d->monto_aplicado, 2) ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($esAnulada): ?>

                                                <span class="badge bg-danger">
                                                    Anulada
                                                </span>

                                            <?php elseif ($esPagada): ?>

                                                <span class="badge bg-success text-white">
                                                    Pagada
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-warning text-dark">
                                                    Pendiente
                                                </span>

                                            <?php endif ?>
                                        </td>

                                        <td class="text-center">
                                            <a href="<?= base_url('facturas/' . $d->factura_id) ?>"
                                                class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        No hay facturas en este quedan
                                    </td>
                                </tr>
                            <?php endif ?>
                        </tbody>
                    </table>
                </div>


                <!-- BLOQUE TOTALES -->

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 bg-light">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Facturas en quedan</span>
                                <strong><?= $totalFacturas ?></strong>
                            </div>

                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted">Saldo pendiente</span>
                                <strong class="text-danger">
                                    $<?= number_format($totalSaldo, 2) ?>
                                </strong>
                            </div>

                        </div>
                    </div>


                    <div class="col-md-4 offset-md-2">

                        <table class="table table-borderless">

                            <tr class="border-top">

                                <th class="text-end fs-5">
                                    Total quedan
                                </th>

                                <td class="text-end fs-5 fw-bold text-success">
                                    $<?= number_format($total, 2) ?>
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
    document.getElementById('btnAnularQuedan')?.addEventListener('click', function() {

        Swal.fire({

            title: '¿Anular quedan?',
            text: 'Esta acción marcará el quedan como anulado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'

        }).then(result => {

            if (!result.isConfirmed) return;

            fetch("<?= base_url('quedans/anular/' . $quedan->id) ?>", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {

                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Quedan anulado' : 'Error',
                        text: data.message
                    });

                    if (data.success) {
                        setTimeout(() => location.reload(), 1200);
                    }

                });

        });

    });
</script>

<?= $this->endSection() ?>