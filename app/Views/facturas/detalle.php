<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .swal2-container .select2-container .select2-selection--single {
        height: 42px !important;
        padding: 6px 10px;
        font-size: 15px;
    }

    .swal2-container .select2-selection__rendered {
        line-height: 28px !important;
    }

    .swal2-container .select2-selection__arrow {
        height: 40px !important;
    }

    .swal2-container .select2-container {
        width: 100% !important;
    }

    .invoice-detail-table th,
    .invoice-detail-table td,
    .invoice-payments-table th,
    .invoice-payments-table td,
    .invoice-remesas-table th,
    .invoice-remesas-table td {
        vertical-align: middle;
    }

    @media (max-width: 767.98px) {
        .invoice-card {
            border: 0;
            box-shadow: none;
        }
        .invoice-header {
            display: block !important;
            padding: .85rem !important;
        }
        .invoice-title-wrap h4 {
            font-size: 1.1rem;
        }
        .invoice-summary-row {
            margin-top: .75rem;
        }
        .invoice-summary-row > [class*="col-"],
        .invoice-info-row > [class*="col-"] {
            margin-bottom: .75rem;
        }
        .invoice-card .card-body {
            padding: .85rem;
        }
        .invoice-actions-col,
        .invoice-totals-col {
            margin-bottom: 1rem;
        }
        #btnAnularFactura,
        .invoice-collapse-btn {
            width: 100%;
        }
        .invoice-table-wrap {
            overflow: visible;
        }
        .invoice-mobile-table {
            border-collapse: separate;
            border-spacing: 0 .75rem;
        }
        .invoice-mobile-table thead {
            display: none;
        }
        .invoice-mobile-table,
        .invoice-mobile-table tbody,
        .invoice-mobile-table tr,
        .invoice-mobile-table td {
            display: block;
            width: 100%;
        }
        .invoice-mobile-table tbody tr {
            border: 1px solid #e5e9f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(31, 41, 55, .06);
            overflow: hidden;
        }
        .invoice-mobile-table tbody tr.table-danger {
            background: #fff7f7;
            border-color: #f1c3c3;
        }
        .invoice-mobile-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            border-top: 1px solid #eef1f5 !important;
            padding: .55rem .75rem;
            text-align: right !important;
        }
        .invoice-mobile-table td:first-child {
            border-top: 0 !important;
            background: #f8fafc;
            font-weight: 700;
        }
        .invoice-mobile-table td::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            text-align: left;
            flex: 0 0 40%;
        }
        .invoice-mobile-table td > * {
            max-width: 60%;
        }
        .invoice-mobile-table .invoice-description {
            text-align: left !important;
            word-break: break-word;
        }
        .invoice-mobile-table tfoot,
        .invoice-mobile-table tfoot tr,
        .invoice-mobile-table tfoot th {
            display: block;
            width: 100%;
        }
        .invoice-mobile-table tfoot tr {
            border: 1px solid #d8e2f0;
            border-radius: 8px;
            background: #f8fafc;
            margin-bottom: .5rem;
            padding: .45rem .75rem;
            text-align: right;
        }
        .invoice-mobile-table tfoot th {
            border: 0 !important;
            padding: .15rem 0;
        }
    }
</style>
<?php
// Calcular subtotal desde los productos
$subtotalProductos = 0;

foreach ($detalles as $d) {
    $subtotalProductos += $d->cantidad * $d->precio_unitario;
}

// Determinar si es Crédito Fiscal (03)
$esCreditoFiscal = ($factura->tipo_dte == '03');
$esSujetoExcluido = ($factura->tipo_dte == '14');
$ivaCalculado = 0;
$retencionRenta = 0;

if ($esCreditoFiscal) {
    $ivaCalculado = $factura->total_pagar - $subtotalProductos;
}

if ($esSujetoExcluido) {
    $retencionRenta = max(0, $subtotalProductos - (float) $factura->total_pagar);
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
        <div class="card invoice-card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between invoice-header">

                <div class="invoice-title-wrap">
                    <h4 class="mb-0">
                        Factura
                        <span class="badge bg-info text-white ms-2">
                            <?= esc($numeroCorto) ?>
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

                <div class="row invoice-summary-row">
                    <!-- PANEL DOCUMENTO -->
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">

                            <small class="text-muted d-block">Correlativo</small>
                            <small class="text-muted d-block mt-1">
                                <?= esc($numeroCorto) ?>
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

                            <?php if (!empty($factura->quedan) && $factura->quedan->anulado == 0): ?>

                                <div class="mt-2 d-flex align-items-center">

                                    <small class="text-muted">
                                        En Quedan
                                    </small>

                                    <a href="<?= base_url('quedans/' . $factura->quedan->id) ?>"
                                        class="badge text-white px-3 py-1 ml-auto"
                                        style="background:#6f42c1;">

                                        <?= esc($factura->quedan->numero_quedan) ?>

                                    </a>

                                </div>

                            <?php endif; ?>

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
                <div class="row mb-4 invoice-info-row">

                    <div class="col-md-8">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Cliente</small>
                            <div class="fw-semibold">
                                <strong><?= esc($factura->cliente) ?></strong>
                            </div>

                            <small class="text-muted mt-2 d-block">Vendedor</small>
                            <div class="fw-semibold">
                                <?php if (tienePermiso('editar_vendedor_en_detalle')): ?>

                                    <strong
                                        id="editarVendedorFactura"
                                        data-factura="<?= $factura->id ?>"
                                        style="cursor:pointer; border-bottom:1px dashed #999;"
                                        title="Cambiar vendedor">

                                        <?= esc($factura->vendedor ?? 'N/D') ?>

                                    </strong>

                                <?php else: ?>

                                    <strong><?= esc($factura->vendedor ?? 'N/D') ?></strong>

                                <?php endif; ?>
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
                <div class="table-responsive invoice-table-wrap">
                    <table class="table table-bordered table-hover align-middle invoice-mobile-table invoice-detail-table">

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
                                    <td data-label="#"><?= $d->num_item ?></td>

                                    <td data-label="Descripcion" class="invoice-description">
                                        <?= nl2br(esc($d->descripcion)) ?>
                                    </td>

                                    <td data-label="Cantidad" class="text-end">
                                        <?= number_format($d->cantidad, 2) ?>
                                    </td>

                                    <td data-label="Precio" class="text-end">
                                        $<?= number_format($d->precio_unitario, 2) ?>
                                    </td>

                                    <td data-label="Total" class="text-end">
                                        $<?= number_format($d->cantidad * $d->precio_unitario, 2) ?>
                                    </td>
                                </tr>

                            <?php endforeach ?>

                        </tbody>

                    </table>
                </div>

                <!-- BLOQUE TOTALES -->
                <div class="row mt-4 invoice-actions-row">

                    <!-- BOTÓN ANULAR -->
                    <div class="col-md-6 invoice-actions-col">

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
                    <div class="col-md-4 offset-md-2 invoice-totals-col">

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

                            <?php if ($esSujetoExcluido && $retencionRenta > 0): ?>

                                <tr>
                                    <th class="text-end">Retención Renta (10%):</th>
                                    <td class="text-end text-danger">
                                        -$<?= number_format($retencionRenta, 2) ?>
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

                        <button class="btn btn-outline-secondary btn-sm invoice-collapse-btn"
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

                            <div class="table-responsive invoice-table-wrap">

                                <table class="table table-sm table-bordered table-hover align-middle invoice-mobile-table invoice-payments-table">

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

                                                <td data-label="Pago">
                                                    <span class="badge badge-secondary">
                                                        #<?= $p->pago_id ?>
                                                    </span>
                                                </td>

                                                <td data-label="Fecha">
                                                    <?= date('d/m/Y', strtotime($p->fecha_pago)) ?>
                                                </td>

                                                <td data-label="Forma">
                                                    <?= ucfirst($p->forma_pago) ?>
                                                </td>

                                                <td data-label="Monto" class="text-end">
                                                    $<?= number_format($p->monto, 2) ?>
                                                </td>

                                                <td data-label="Estado" class="text-center">

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

                <!-- ── REMESAS (RECUPEROS) ── -->
                <?php if (!empty($remesas)): ?>

                    <?php
                    $formaCobroLabel = [
                        'efectivo'      => 'Efectivo',
                        'cheque'        => 'Cheque',
                        'transferencia' => 'Transferencia',
                        'deposito'      => 'Depósito bancario',
                    ];
                    $totalRemesado = 0;
                    $totalRemesaAplicada = 0;
                    foreach ($remesas as $rm) {
                        if ($rm->estado !== 'ANULADO') $totalRemesado += (float)$rm->monto_aplicado;
                        if ($rm->estado === 'APLICADO') $totalRemesaAplicada += (float)$rm->monto_aplicado;
                    }
                    ?>

                    <div class="mt-4">

                        <button class="btn btn-outline-primary btn-sm invoice-collapse-btn"
                                type="button"
                                data-toggle="collapse"
                                data-target="#tablaRemesasFactura"
                                aria-expanded="false"
                                aria-controls="tablaRemesasFactura">
                            <i class="fa-solid fa-wallet mr-1"></i>
                            Remesas recibidas
                            <span class="badge badge-primary ml-1"><?= count($remesas) ?></span>
                        </button>

                        <div class="collapse mt-3" id="tablaRemesasFactura">

                            <div class="alert alert-info py-2 mb-3" style="font-size:.85rem;">
                                <i class="fa-solid fa-circle-info mr-1"></i>
                                Las remesas son cobros recibidos por el vendedor que aún no han sido aplicados como pago formal.
                                <strong>No afectan el saldo de la factura</strong> hasta que se registre el pago correspondiente.
                            </div>

                            <div class="table-responsive invoice-table-wrap">
                                <table class="table table-sm table-bordered table-hover invoice-mobile-table invoice-remesas-table">
                                    <thead style="background:#1e3a5f;">
                                        <tr>
                                            <th style="color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Recupero</th>
                                            <th style="color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Fecha</th>
                                            <th style="color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Forma cobro</th>
                                            <th class="text-right" style="color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Monto remesado</th>
                                            <th class="text-center" style="color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Estado</th>
                                            <th class="text-center" style="color:#fff;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($remesas as $rm):
                                            $rmAnulado  = ($rm->estado === 'ANULADO');
                                            $rmAplicado = ($rm->estado === 'APLICADO');
                                            $rmColor    = $rmAnulado ? 'danger' : ($rmAplicado ? 'primary' : 'warning');
                                            $rmIcon     = $rmAnulado ? 'fa-ban' : ($rmAplicado ? 'fa-link' : 'fa-clock');
                                        ?>
                                            <tr class="<?= $rmAnulado ? 'table-danger text-muted' : '' ?>">
                                                <td data-label="Recupero">
                                                    <a href="<?= base_url('recuperos/' . $rm->recupero_id) ?>"
                                                       class="font-weight-bold <?= $rmAnulado ? 'text-muted' : '' ?>">
                                                        <?= esc($rm->numero_recupero) ?>
                                                    </a>
                                                </td>
                                                <td data-label="Fecha" class="small"><?= date('d/m/Y', strtotime($rm->fecha)) ?></td>
                                                <td data-label="Forma cobro" class="small"><?= $formaCobroLabel[$rm->forma_cobro] ?? ucfirst($rm->forma_cobro) ?></td>
                                                <td data-label="Monto remesado" class="text-right font-weight-bold <?= $rmAnulado ? 'text-muted' : 'text-dark' ?>">
                                                    <?= $rmAnulado ? '<s>' : '' ?>
                                                    $<?= number_format($rm->monto_aplicado, 2) ?>
                                                    <?= $rmAnulado ? '</s>' : '' ?>
                                                </td>
                                                <td data-label="Estado" class="text-center">
                                                    <span class="badge badge-<?= $rmColor ?>">
                                                        <i class="fa-solid <?= $rmIcon ?> mr-1"></i><?= $rm->estado ?>
                                                    </span>
                                                </td>
                                                <td data-label="Pago" class="text-center">
                                                    <?php if ($rm->pago_id): ?>
                                                        <a href="<?= base_url('payments/' . $rm->pago_id) ?>"
                                                           class="badge badge-success" title="Ver pago aplicado">
                                                            <i class="fa-solid fa-receipt mr-1"></i>#<?= $rm->pago_id ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <th colspan="3" class="text-right small">Total remesado (activo + aplicado):</th>
                                            <th class="text-right text-primary">$<?= number_format($totalRemesado, 2) ?></th>
                                            <th colspan="2"></th>
                                        </tr>
                                        <?php if ($totalRemesaAplicada > 0): ?>
                                            <tr class="table-light">
                                                <th colspan="3" class="text-right small">Ya convertido a pago:</th>
                                                <th class="text-right text-success">$<?= number_format($totalRemesaAplicada, 2) ?></th>
                                                <th colspan="2"></th>
                                            </tr>
                                        <?php endif; ?>
                                        <tr class="table-light">
                                            <th colspan="3" class="text-right small">Pendiente de aplicar:</th>
                                            <th class="text-right <?= ($totalRemesado - $totalRemesaAplicada) > 0 ? 'text-warning' : 'text-muted' ?>">
                                                $<?= number_format($totalRemesado - $totalRemesaAplicada, 2) ?>
                                            </th>
                                            <th colspan="2"></th>
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
                .then(async response => {

                    const text = await response.text();

                    let data;

                    try {
                        data = JSON.parse(text);
                    } catch (err) {

                        console.error("Respuesta del servidor:", text);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error del servidor',
                            html: `
                    <div style="text-align:left">
                        <b>No se pudo interpretar la respuesta del servidor.</b>
                        <hr>
                        <pre style="
                            max-height:300px;
                            overflow:auto;
                            font-size:12px;
                            background:#f6f6f6;
                            padding:10px;
                            border-radius:5px
                        ">
                        ${text.substring(0,800)}
                        </pre>
                    </div>
                `,
                            width: 700
                        });

                        throw new Error("Respuesta no es JSON");
                    }

                    return data;

                })
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

                    console.error("Error en anulación:", error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error inesperado',
                        text: error.message
                    });

                });

        }

    });
    document.getElementById('editarVendedorFactura')?.addEventListener('click', function() {

        const facturaId = this.dataset.factura;

        Swal.fire({
            title: 'Cambiar vendedor',
            html: `<select id="swalSellerSelect" style="width:100%"></select>`,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            didOpen: () => {

                $('#swalSellerSelect').select2({
                    placeholder: 'Buscar vendedor...',
                    dropdownParent: $('.swal2-container'),
                    minimumInputLength: 2,
                    ajax: {
                        url: "<?= base_url('sellers/searchAjax') ?>",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                select2: 1
                            };
                        },
                        processResults: function(data) {
                            return data;
                        }
                    }
                });

            },
            preConfirm: () => {

                const seller = $('#swalSellerSelect').val();

                if (!seller) {
                    Swal.showValidationMessage('Debe seleccionar un vendedor');
                }

                return seller;

            }

        }).then(result => {

            if (!result.isConfirmed) return;

            fetch("<?= base_url('facturas/cambiar-vendedor') ?>", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        factura_id: facturaId,
                        vendedor_id: result.value
                    })
                })
                .then(r => r.json())
                .then(data => {

                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Vendedor actualizado' : 'Error',
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
