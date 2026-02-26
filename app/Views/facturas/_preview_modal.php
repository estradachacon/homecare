<?php $pagos = $pagos ?? []; ?>
<?php
$subtotalProductos = 0;

foreach ($detalles as $d) {
    $subtotalProductos += $d->cantidad * $d->precio_unitario;
}

$esCreditoFiscal = ($factura->tipo_dte == '03');

$ivaCalculado = 0;
if ($esCreditoFiscal) {
    $ivaCalculado = $factura->total_pagar - $subtotalProductos;
}
?>

<h6><?= esc($factura->numero_control) ?></h6>

<small class="text-muted"><?= esc($factura->cliente) ?></small>

<hr>

<div class="row mb-3">

    <div class="col-md-6">
        <small class="text-muted">Fecha</small><br>
        <strong><?= date('d/m/Y', strtotime($factura->fecha_emision)) ?></strong>
    </div>

    <div class="col-md-6 text-end">
        <small class="text-muted">Vendedor</small><br>
        <strong><?= esc($factura->vendedor ?? 'N/D') ?></strong>
    </div>

</div>

<table class="table table-sm table-bordered">

    <thead class="table-light">
        <tr>
            <th>Descripción</th>
            <th class="text-end">Cantidad</th>
            <th class="text-end">Precio</th>
            <th class="text-end">Total</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($detalles as $d): ?>
            <tr>
                <td><?= nl2br(esc($d->descripcion)) ?></td>
                <td class="text-end"><?= number_format($d->cantidad,2) ?></td>
                <td class="text-end">$<?= number_format($d->precio_unitario,2) ?></td>
                <td class="text-end">$<?= number_format($d->cantidad * $d->precio_unitario,2) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>

</table>

<div class="row mt-3">

    <div class="col-md-6"></div>

    <div class="col-md-6">

        <table class="table table-sm table-borderless">

            <tr>
                <th class="text-end">Subtotal:</th>
                <td class="text-end">$<?= number_format($subtotalProductos,2) ?></td>
            </tr>

            <?php if ($esCreditoFiscal): ?>
                <tr>
                    <th class="text-end">IVA:</th>
                    <td class="text-end">$<?= number_format($ivaCalculado,2) ?></td>
                </tr>
            <?php endif ?>

            <tr class="border-top">
                <th class="text-end fs-6">Total:</th>
                <td class="text-end fw-bold text-success fs-6">
                    $<?= number_format($factura->total_pagar,2) ?>
                </td>
            </tr>

            <tr>
                <th class="text-end">Saldo:</th>
                <td class="text-end text-danger">
                    $<?= number_format($factura->saldo,2) ?>
                </td>
            </tr>

        </table>

    </div>

</div>
<?php if (!empty($pagos)): ?>

<hr>

<h6 class="mt-4">Pagos aplicados</h6>

<table class="table table-sm table-bordered">

    <thead class="table-light">
        <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Referencia</th>
            <th class="text-end">Monto</th>
        </tr>
    </thead>

    <tbody>

        <?php 
        $totalPagado = 0;
        foreach ($pagos as $p): 
            $monto = floatval($p->monto);
            $totalPagado += $monto;
        ?>

        <tr>
            <td><?= date('d/m/Y', strtotime($p->fecha_pago)) ?></td>
            <td><?= esc(ucfirst($p->forma_pago)) ?></td>
            <td>
                Pago #<?= esc($p->pago_id) ?>
                <?php if (!empty($p->observaciones)): ?>
                    <br>
                    <small class="text-muted"><?= esc($p->observaciones) ?></small>
                <?php endif; ?>
            </td>
            <td class="text-end">
                $<?= number_format($monto, 2) ?>
            </td>
        </tr>

        <?php endforeach; ?>

    </tbody>

    <tfoot class="table-light">
        <tr>
            <th colspan="3" class="text-end">Total pagado:</th>
            <th class="text-end text-primary">
                $<?= number_format($totalPagado, 2) ?>
            </th>
        </tr>

        <tr>
            <th colspan="3" class="text-end">Saldo pendiente:</th>
            <th class="text-end text-danger">
                $<?= number_format($factura->total_pagar - $totalPagado, 2) ?>
            </th>
        </tr>
    </tfoot>

</table>

<?php endif; ?>