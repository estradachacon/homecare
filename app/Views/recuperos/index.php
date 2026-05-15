<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    /* Igualar Select2 al tamaño form-control-sm del resto del filtro */
    #filtroClienteSelect + .select2-container .select2-selection--single {
        height: 31px !important;
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
    #filtroClienteSelect + .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 29px !important;
        font-size: .875rem;
        padding-left: .5rem;
    }
    #filtroClienteSelect + .select2-container .select2-selection--single .select2-selection__arrow {
        height: 29px !important;
    }
    #filtroClienteSelect + .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #80bdff;
        box-shadow: 0 0 0 .2rem rgba(0,123,255,.25);
    }
    .recuperos-table th,
    .recuperos-table td {
        vertical-align: middle;
    }
    .recuperos-table .recupero-main-link {
        display: inline-block;
        min-width: 96px;
    }
    .recuperos-table .recupero-reference {
        max-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (max-width: 767.98px) {
        .recuperos-card-header {
            align-items: flex-start !important;
            gap: .75rem;
        }
        .recuperos-card-header .btn {
            width: 100%;
        }
        .recuperos-filter-actions {
            width: 100%;
        }
        .recuperos-filter-actions .btn,
        .recuperos-filter-actions a {
            flex: 1 1 0;
        }
        .recuperos-table-wrap {
            overflow: visible;
        }
        .recuperos-table {
            border-collapse: separate;
            border-spacing: 0 .75rem;
        }
        .recuperos-table thead {
            display: none;
        }
        .recuperos-table,
        .recuperos-table tbody,
        .recuperos-table tr,
        .recuperos-table td {
            display: block;
            width: 100%;
        }
        .recuperos-table tbody tr {
            border: 1px solid #e5e9f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(31, 41, 55, .06);
            overflow: hidden;
            cursor: pointer;
        }
        .recuperos-table tbody tr.table-danger {
            background: #fff7f7;
            border-color: #f1c3c3;
        }
        .recuperos-table tbody tr.table-primary {
            background: #f5f8ff;
            border-color: #cbd9ff;
        }
        .recuperos-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            border-top: 1px solid #eef1f5;
            padding: .55rem .75rem;
            text-align: right !important;
        }
        .recuperos-table td:first-child {
            border-top: 0;
            background: #f8fafc;
            font-size: .95rem;
        }
        .recuperos-table td::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: .73rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            text-align: left;
            flex: 0 0 42%;
        }
        .recuperos-table td > * {
            max-width: 58%;
        }
        .recuperos-table .recupero-reference {
            display: none;
        }
        .recuperos-table .recupero-date-cell,
        .recuperos-table .recupero-state-cell,
        .recuperos-table .recupero-actions {
            display: none;
        }
        .recuperos-table .recupero-card-head {
            align-items: center;
            background: #f8fafc;
        }
        .recuperos-table .recupero-card-head::before {
            display: none;
        }
        .recuperos-table .recupero-main-link {
            min-width: 0;
            max-width: 62%;
            text-align: left;
        }
        .recupero-mobile-date {
            display: inline-block !important;
            max-width: 38%;
            color: #6c757d;
            font-size: .78rem;
            white-space: nowrap;
        }
        .recuperos-table .recupero-client-cell {
            align-items: flex-start;
        }
        .recuperos-table .recupero-client-name {
            max-width: 58%;
            white-space: normal;
            word-break: normal;
            overflow-wrap: anywhere;
            line-height: 1.25;
            text-align: right;
        }
        .recuperos-table .recupero-total-cell {
            align-items: center;
        }
        .recuperos-table .recupero-total-state {
            display: inline-flex !important;
            align-items: center;
            gap: .45rem;
            max-width: 58%;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .recuperos-table .recupero-total-state > * {
            max-width: none;
        }
        .recuperos-table .recupero-mobile-state {
            display: inline-block !important;
        }
    }
</style>

<?php
$formaCobro = [
    'efectivo'      => ['label' => 'Efectivo',      'color' => 'success'],
    'cheque'        => ['label' => 'Cheque',         'color' => 'info'],
    'transferencia' => ['label' => 'Transferencia',  'color' => 'primary'],
    'deposito'      => ['label' => 'Depósito',       'color' => 'warning'],
];
?>

<!-- Mensajes flash -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa-solid fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between py-2 recuperos-card-header">
        <h5 class="header-title mb-0">
            <i class="fa-solid fa-money-bill-wave text-success mr-2"></i>Recuperos de Cobro
        </h5>
        <?php if (tienePermiso('crear_recupero')): ?>
            <a href="<?= base_url('recuperos/nuevo') ?>" class="btn btn-success btn-sm">
                <i class="fa-solid fa-plus mr-1"></i>Nuevo Recupero
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card-body border-bottom py-2 bg-light">
        <form method="GET" action="<?= base_url('recuperos') ?>" class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold mb-1">Cliente</label>
                <?php
                    // Buscar el cliente pre-seleccionado para poblar el Select2
                    $clientePre = null;
                    if (!empty($filtros['cliente_id'])) {
                        foreach ($clientes as $c) {
                            if ($c->id == $filtros['cliente_id']) { $clientePre = $c; break; }
                        }
                    }
                ?>
                <select id="filtroClienteSelect" name="cliente_id" style="width:100%">
                    <option value="">— Todos los clientes —</option>
                    <?php if ($clientePre): ?>
                        <option value="<?= $clientePre->id ?>" selected><?= esc($clientePre->nombre) ?></option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold mb-1">Estado</label>
                <select name="estado" class="form-control form-control-sm">
                    <option value="">— Todos —</option>
                    <option value="ACTIVO"   <?= ($filtros['estado'] === 'ACTIVO')   ? 'selected' : '' ?>>Activo</option>
                    <option value="APLICADO" <?= ($filtros['estado'] === 'APLICADO') ? 'selected' : '' ?>>Aplicado</option>
                    <option value="ANULADO"  <?= ($filtros['estado'] === 'ANULADO')  ? 'selected' : '' ?>>Anulado</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold mb-1">Desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm"
                       value="<?= esc($filtros['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                       value="<?= esc($filtros['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="col-md-3 mb-2 d-flex gap-1 recuperos-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm mr-1">
                    <i class="fa-solid fa-filter mr-1"></i>Filtrar
                </button>
                <a href="<?= base_url('recuperos') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <?php if (empty($recuperos)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                No se encontraron recuperos con los filtros aplicados.
            </div>
        <?php else: ?>
            <div class="table-responsive recuperos-table-wrap">
                <table class="table table-sm table-hover mb-0 recuperos-table">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-dark">N° Recupero</th>
                            <th class="text-dark">Fecha</th>
                            <th class="text-dark">Cliente</th>
                            <th class="text-dark">Vendedor</th>
                            <th class="text-dark">Usuario</th>
                            <th class="text-dark">Forma de cobro</th>
                            <th class="text-dark">Referencia</th>
                            <th class="text-right text-dark">Total</th>
                            <th class="text-center text-dark">Estado</th>
                            <th class="text-center text-dark">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recuperos as $r): ?>
                            <?php
                            $fc    = $formaCobro[$r->forma_cobro] ?? ['label' => ucfirst($r->forma_cobro), 'color' => 'secondary'];
                            $anulado  = ($r->estado === 'ANULADO');
                            $aplicado = ($r->estado === 'APLICADO');
                            ?>
                            <?php $estadoColor = $anulado ? 'danger' : ($aplicado ? 'primary' : 'success'); ?>
                            <tr class="<?= $anulado ? 'table-danger' : ($aplicado ? 'table-primary' : '') ?>"
                                data-href="<?= base_url('recuperos/' . $r->id) ?>">
                                <td data-label="Recupero" class="recupero-card-head">
                                    <a href="<?= base_url('recuperos/' . $r->id) ?>"
                                       class="font-weight-bold text-dark recupero-main-link">
                                        <?= esc($r->numero_recupero) ?>
                                    </a>
                                    <span class="recupero-mobile-date d-none">
                                        <?= date('d/m/Y', strtotime($r->fecha)) ?>
                                    </span>
                                </td>
                                <td data-label="Fecha" class="recupero-date-cell"><?= date('d/m/Y', strtotime($r->fecha)) ?></td>
                                <td data-label="Cliente" class="recupero-client-cell">
                                    <span class="recupero-client-name"><?= esc($r->cliente_nombre) ?></span>
                                </td>
                                <td data-label="Vendedor">
                                    <span><?= esc($r->vendedor_nombre ?? 'Sin vendedor') ?></span>
                                </td>
                                <td data-label="Usuario">
                                    <span><?= esc($r->usuario_nombre ?? 'N/D') ?></span>
                                </td>
                                <td data-label="Forma">
                                    <span class="badge badge-<?= $fc['color'] ?>">
                                        <?= $fc['label'] ?>
                                    </span>
                                </td>
                                <td data-label="Referencia" class="text-muted small recupero-reference"><?= esc($r->referencia ?? '—') ?></td>
                                <td data-label="Total" class="text-right font-weight-bold recupero-total-cell">
                                    <span class="recupero-total-state">
                                        <span>
                                            <?= $anulado
                                                ? '<s class="text-muted">$ ' . number_format($r->total, 2) . '</s>'
                                                : '$ ' . number_format($r->total, 2) ?>
                                        </span>
                                        <span class="badge badge-<?= $estadoColor ?> d-none recupero-mobile-state">
                                            <?= $r->estado ?>
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Estado" class="text-center recupero-state-cell">
                                    <span class="badge badge-<?= $estadoColor ?>">
                                        <?= $r->estado ?>
                                    </span>
                                </td>
                                <td data-label="Acciones" class="text-center recupero-actions">
                                    <a href="<?= base_url('recuperos/' . $r->id) ?>"
                                       class="btn btn-outline-secondary btn-sm" title="Ver detalle">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($pager): ?>
                <div class="card-footer d-flex justify-content-end py-2">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
$(function() {
    $('#filtroClienteSelect').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Buscar cliente...',
        allowClear: true,
        language: 'es',
        ajax: {
            url: '<?= base_url("clientes/buscar") ?>',
            dataType: 'json',
            delay: 250,
            data: p => ({ q: p.term }),
            processResults: data => ({
                results: Array.isArray(data) ? data : []
            })
        }
    });

    $('.recuperos-table tbody').on('click', 'tr[data-href]', function(e) {
        if ($(e.target).closest('a, button, input, select, textarea').length) return;
        window.location = $(this).data('href');
    });
});
</script>
<?= $this->endSection() ?>
