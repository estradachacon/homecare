<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .remesas-index-table th,
    .remesas-index-table td {
        vertical-align: middle;
    }
    .remesas-index-table .remesa-desc {
        max-width: 260px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (max-width: 767.98px) {
        .remesas-card-header {
            align-items: flex-start !important;
            gap: .75rem;
        }
        .remesas-card-header .btn,
        .remesas-filter-actions .btn,
        .remesas-filter-actions a {
            width: 100%;
        }
        .remesas-filter-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
        }
        .remesas-table-wrap {
            overflow: visible;
        }
        .remesas-index-table {
            border-collapse: separate;
            border-spacing: 0 .75rem;
        }
        .remesas-index-table thead {
            display: none;
        }
        .remesas-index-table,
        .remesas-index-table tbody,
        .remesas-index-table tr,
        .remesas-index-table td {
            display: block;
            width: 100%;
        }
        .remesas-index-table tbody tr {
            border: 1px solid #e5e9f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(31, 41, 55, .06);
            overflow: hidden;
            cursor: pointer;
        }
        .remesas-index-table tbody tr.table-danger {
            background: #fff7f7;
            border-color: #f1c3c3;
        }
        .remesas-index-table tbody tr.table-secondary {
            background: #f8fafc;
            border-color: #d8dee6;
        }
        .remesas-index-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            border-top: 1px solid #eef1f5;
            padding: .55rem .75rem;
            text-align: right !important;
        }
        .remesas-index-table td:first-child {
            border-top: 0;
            background: #f8fafc;
            font-size: .95rem;
        }
        .remesas-index-table td::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            text-align: left;
            flex: 0 0 42%;
        }
        .remesas-index-table td > * {
            max-width: 58%;
        }
        .remesas-index-table .remesa-desc {
            display: block;
            max-width: 100%;
            width: 100%;
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
            word-break: normal;
            overflow-wrap: anywhere;
            line-height: 1.25;
            text-align: left !important;
        }
        .remesas-index-table .remesa-desc::before {
            display: block;
            margin-bottom: .25rem;
            text-align: left;
            flex: none;
        }
        .remesas-index-table .remesa-desc > * {
            max-width: 100%;
        }
        .remesas-index-table .remesa-date-cell,
        .remesas-index-table .remesa-state-cell,
        .remesas-index-table .remesa-actions {
            display: none;
        }
        .remesas-index-table .remesa-card-head {
            align-items: center;
            background: #f8fafc;
        }
        .remesas-index-table .remesa-card-head::before {
            display: none;
        }
        .remesas-index-table .remesa-main-link {
            min-width: 0;
            max-width: 62%;
            text-align: left;
        }
        .remesa-mobile-date {
            display: inline-block !important;
            max-width: 38%;
            color: #6c757d;
            font-size: .78rem;
            white-space: nowrap;
        }
        .remesas-index-table .remesa-total-cell {
            align-items: center;
        }
        .remesas-index-table .remesa-total-state {
            display: inline-flex !important;
            align-items: center;
            gap: .45rem;
            max-width: 58%;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .remesas-index-table .remesa-total-state > * {
            max-width: none;
        }
        .remesas-index-table .remesa-mobile-state {
            display: inline-block !important;
        }
    }
</style>

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
    <div class="card-header d-flex flex-wrap justify-content-between py-2 remesas-card-header">
        <h5 class="header-title mb-0">
            <i class="fa-solid fa-layer-group text-primary mr-2"></i>Remesas Contables
        </h5>
        <?php if (tienePermiso('crear_remesa_contable')): ?>
            <a href="<?= base_url('contabilidad/remesas/nuevo') ?>" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus mr-1"></i>Nueva Remesa
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card-body border-bottom py-2 bg-light">
        <form method="GET" action="<?= base_url('contabilidad/remesas') ?>" class="form-row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold mb-1">Tipo de partida</label>
                <select name="tipo_partida_id" class="form-control form-control-sm">
                    <option value="">— Todos —</option>
                    <?php foreach ($tiposPartida as $tp): ?>
                        <option value="<?= $tp->id ?>" <?= ($filtros['tipo_partida_id'] == $tp->id) ? 'selected' : '' ?>>
                            <?= esc($tp->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold mb-1">Estado</label>
                <select name="estado" class="form-control form-control-sm">
                    <option value="">— Todos —</option>
                    <option value="ACTIVO"  <?= ($filtros['estado'] === 'ACTIVO')  ? 'selected' : '' ?>>Activo</option>
                    <option value="CERRADO" <?= ($filtros['estado'] === 'CERRADO') ? 'selected' : '' ?>>Cerrado</option>
                    <option value="ANULADO" <?= ($filtros['estado'] === 'ANULADO') ? 'selected' : '' ?>>Anulado</option>
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
            <div class="col-md-3 mb-2 remesas-filter-actions">
                <button type="submit" class="btn btn-primary btn-sm mr-1">
                    <i class="fa-solid fa-filter mr-1"></i>Filtrar
                </button>
                <a href="<?= base_url('contabilidad/remesas') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-rotate-left mr-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <?php if (empty($remesas)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                No se encontraron remesas con los filtros aplicados.
            </div>
        <?php else: ?>
            <div class="table-responsive remesas-table-wrap">
                <table class="table table-sm table-hover mb-0 remesas-index-table">
                    <thead class="thead-dark">
                        <tr>
                            <th>N° Remesa</th>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Tipo de Partida</th>
                            <th>Asientos</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($remesas as $r): ?>
                            <?php
                            $anulado = ($r->estado === 'ANULADO');
                            $cerrado = ($r->estado === 'CERRADO');
                            $color = $anulado ? 'danger' : ($cerrado ? 'secondary' : 'success');
                            $icon  = $anulado ? 'fa-ban' : ($cerrado ? 'fa-lock' : 'fa-circle-check');
                            ?>
                            <tr class="<?= $anulado ? 'table-danger' : ($cerrado ? 'table-secondary' : '') ?>"
                                data-href="<?= base_url('contabilidad/remesas/' . $r->id) ?>">
                                <td data-label="Remesa" class="remesa-card-head">
                                    <a href="<?= base_url('contabilidad/remesas/' . $r->id) ?>"
                                       class="font-weight-bold text-dark remesa-main-link">
                                        <?= esc($r->numero_remesa) ?>
                                    </a>
                                    <span class="remesa-mobile-date d-none">
                                        <?= date('d/m/Y', strtotime($r->fecha)) ?>
                                    </span>
                                </td>
                                <td data-label="Fecha" class="remesa-date-cell"><?= date('d/m/Y', strtotime($r->fecha)) ?></td>
                                <td data-label="Descripcion" class="small remesa-desc"><?= esc($r->descripcion) ?></td>
                                <td data-label="Tipo partida">
                                    <?php if ($r->tipo_partida_nombre): ?>
                                        <span class="badge badge-light border"><?= esc($r->tipo_partida_nombre) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Asientos" class="text-center">
                                    <span class="badge badge-secondary"><?= (int)($r->num_asientos ?? 0) ?></span>
                                </td>
                                <td data-label="Total" class="text-right font-weight-bold remesa-total-cell">
                                    <span class="remesa-total-state">
                                        <span>
                                            <?= $anulado
                                                ? '<s class="text-muted">$' . number_format($r->total, 2) . '</s>'
                                                : '$' . number_format($r->total, 2) ?>
                                        </span>
                                        <span class="badge badge-<?= $color ?> d-none remesa-mobile-state">
                                            <?= $r->estado ?>
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Estado" class="text-center remesa-state-cell">
                                    <span class="badge badge-<?= $color ?>">
                                        <i class="fa-solid <?= $icon ?> mr-1"></i><?= $r->estado ?>
                                    </span>
                                </td>
                                <td data-label="Acciones" class="text-center remesa-actions">
                                    <a href="<?= base_url('contabilidad/remesas/' . $r->id) ?>"
                                       class="btn btn-outline-secondary btn-sm" title="Ver detalle">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pager): ?>
                <div class="card-footer d-flex justify-content-end py-2">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Cargar conteos de asientos por remesa
$(function() {
    const ids = <?= json_encode(array_column($remesas, 'id')) ?>;
    if (!ids.length) return;
    ids.forEach(function(id) {
        fetch('<?= base_url('contabilidad/remesas/') ?>' + id + '/conteo')
            .then(r => r.json())
            .then(d => { if (d.count !== undefined) $('#cnt-' + id).text(d.count); })
            .catch(() => {});
    });

    $('.remesas-index-table tbody').on('click', 'tr[data-href]', function(e) {
        if ($(e.target).closest('a, button, input, select, textarea').length) return;
        window.location = $(this).data('href');
    });
});
</script>

<?= $this->endSection() ?>
