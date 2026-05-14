<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm cliente-detail-card">
            <div class="card-header d-flex">
                <h4 class="mb-0 cliente-detail-title">
                    <?= esc($cliente->nombre) ?>
                </h4>
                <?php if (tienePermiso('editar_clientes')): ?>
                    <a href="<?= base_url('clientes/edit/' . $cliente->id) ?>"
                        class="btn btn-sm btn-warning cliente-mobile-edit-btn">
                        <i class="fa-solid fa-pen"></i>
                        <span>Editar</span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row cliente-detail-grid">

                    <div class="col-md-4 cliente-info-item">
                        <small class="text-muted">Documento</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->numero_documento) ?>
                        </div>
                    </div>

                    <div class="col-md-4 cliente-info-item">
                        <small class="text-muted">NRC</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->nrc ?? 'N/D') ?>
                        </div>
                    </div>

                    <div class="col-md-4 cliente-info-item">
                        <small class="text-muted">Teléfono</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->telefono ?? 'N/D') ?>
                        </div>
                    </div>
                    <div class="col-md-4 cliente-info-item cliente-account-item">
                        <small class="text-muted">Cuenta Contable</small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="fw-semibold">
                                <?php if (!empty($cliente->cuenta_codigo)): ?>
                                    <?= esc($cliente->cuenta_codigo . ' - ' . $cliente->cuenta_nombre) ?>
                                <?php else: ?>
                                    <span class="text-danger">Sin cuenta</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- FACTURAS -->

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Facturas del cliente</h5>
            </div>

            <div class="card-body">
                <form method="get" class="mb-3 cliente-filter-form">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Desde</label>
                            <input type="date" name="desde" class="form-control"
                                value="<?= esc($desde ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Hasta</label>
                            <input type="date" name="hasta" class="form-control"
                                value="<?= esc($hasta ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary">
                                <i class="fa-solid fa-filter"></i> Filtrar
                            </button>

                            <a href="<?= base_url('clientes/show/' . $cliente->id) ?>" class="btn btn-light">
                                Limpiar
                            </a>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="<?= base_url('clientes/exportar-excel/' . $cliente->id . '?desde=' . ($desde ?? '') . '&hasta=' . ($hasta ?? '')) ?>"
                                class="btn btn-success">
                                <i class="fa-solid fa-file-excel"></i> Exportar Excel
                            </a>
                        </div>
                    </div>
                </form>
                <div class="table-responsive cliente-facturas-wrap">

                    <table class="table table-bordered table-hover align-middle cliente-facturas-table">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th class="col-2">Correlativo</th>
                                <th>Fecha y hora de emisión</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th class="text-center">Estado</th>
                                <th style="width:80px">Acción</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($facturas): ?>

                                <?php foreach ($facturas as $f): ?>

                                    <tr class="cliente-factura-row <?= ($f->anulada ?? 0) == 1 ? 'table-danger' : '' ?>" data-href="<?= base_url('facturas/' . $f->id) ?>">
                                        <td class="factura-id-cell" data-label="#">
                                            <span class="badge bg-light text-dark">#<?= $f->id ?></span>
                                        </td>

                                        <td class="factura-correlativo-cell" data-label="Correlativo">
                                            <span class="badge bg-info text-white badge-lg">
                                                <?= substr($f->numero_control, -6) ?>
                                            </span>
                                            <small class="text-muted factura-mobile-date">
                                                <?= date('d/m/Y', strtotime($f->fecha_emision)) ?>
                                            </small>
                                        </td>

                                        <td class="factura-fecha-cell" data-label="Fecha">
                                            <?= date('d/m/Y', strtotime($f->fecha_emision)) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i:s', strtotime($f->hora_emision)) ?>
                                            </small>
                                        </td>

                                        <td class="text-end fw-bold factura-total-cell" data-label="Total">
                                            $<?= number_format($f->total_pagar, 2) ?>
                                            <span class="factura-mobile-status">
                                                <?php if (($f->anulada ?? 0) == 1): ?>
                                                    <span class="badge bg-danger text-white">Anulada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success text-white">Activa</span>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="factura-saldo-cell" data-label="Saldo">
                                            $<?= number_format($f->saldo, 2) ?>
                                        </td>
                                        <td class="text-center factura-estado-cell" data-label="Estado">
                                            <?php if (($f->anulada ?? 0) == 1): ?>
                                                <span class="badge bg-danger text-white">
                                                    Anulada
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white">
                                                    Activa
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center factura-action-cell" data-label="Accion">
                                            <a href="<?= base_url('facturas/' . $f->id) ?>"
                                                class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>

                                <?php endforeach ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Este cliente no tiene facturas.
                                    </td>
                                </tr>

                            <?php endif ?>

                        </tbody>

                    </table>

                </div>
                <div id="pagerContainer" class="d-flex mt-3">
                    <?= $pager->only(['desde', 'hasta'])->links('default', 'bootstrap_full') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .factura-mobile-date,
    .factura-mobile-status {
        display: none;
    }

    .cliente-mobile-edit-btn {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-left: auto;
    }

    @media (max-width: 767.98px) {
        .cliente-detail-card .card-header {
            align-items: flex-start;
            gap: .75rem;
            justify-content: space-between;
        }

        .cliente-mobile-edit-btn {
            flex: 0 0 auto;
        }

        .cliente-detail-title {
            font-size: 1.1rem;
            line-height: 1.25;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .cliente-detail-grid {
            gap: .65rem;
        }

        .cliente-info-item {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            padding: .45rem 0;
            border-bottom: 1px solid #eef1f4;
        }

        .cliente-info-item small {
            flex: 0 0 auto;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .cliente-info-item .fw-semibold {
            min-width: 0;
            text-align: right;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .cliente-account-item {
            display: block;
            border-bottom: 0;
        }

        .cliente-account-item .d-flex {
            align-items: flex-start !important;
        }

        .cliente-account-item .fw-semibold {
            flex: 1;
            text-align: left;
        }

        .cliente-filter-form .row {
            gap: .65rem;
        }

        .cliente-filter-form .btn {
            width: 100%;
            margin-top: .35rem;
        }

        .cliente-filter-form .text-end {
            text-align: left !important;
        }

        .cliente-facturas-wrap {
            overflow: visible;
        }

        .cliente-facturas-table {
            border-collapse: separate;
            border-spacing: 0 .65rem;
        }

        .cliente-facturas-table thead {
            display: none;
        }

        .cliente-facturas-table tbody,
        .cliente-facturas-table tr,
        .cliente-facturas-table td {
            display: block;
            width: 100%;
        }

        .cliente-facturas-table tr.cliente-factura-row {
            cursor: pointer;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem;
            background: #fff;
            box-shadow: 0 .125rem .45rem rgba(15, 23, 42, .06);
        }

        .cliente-facturas-table tr.cliente-factura-row.table-danger {
            background: #fff5f5;
        }

        .cliente-facturas-table td {
            border: 0;
            padding: .18rem 0;
        }

        .cliente-facturas-table .factura-id-cell,
        .cliente-facturas-table .factura-fecha-cell,
        .cliente-facturas-table .factura-saldo-cell,
        .cliente-facturas-table .factura-estado-cell {
            display: none;
        }

        .cliente-facturas-table .factura-correlativo-cell,
        .cliente-facturas-table .factura-total-cell {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            align-items: center;
        }

        .cliente-facturas-table .factura-correlativo-cell {
            flex-wrap: wrap;
        }

        .cliente-facturas-table .factura-correlativo-cell::before,
        .cliente-facturas-table .factura-total-cell::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .cliente-facturas-table .factura-mobile-date {
            display: block;
            flex-basis: 100%;
            padding-left: calc(.75rem + 78px);
        }

        .cliente-facturas-table .factura-mobile-status {
            display: inline-flex;
            margin-left: .4rem;
        }

        .cliente-facturas-table .factura-action-cell {
            display: none;
        }

        #pagerContainer {
            overflow-x: auto;
            justify-content: flex-start;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cliente-factura-row[data-href]').forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('a, button')) return;
                window.location.href = this.dataset.href;
            });
        });
    });
</script>
<?= $this->endSection() ?>
