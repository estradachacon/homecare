<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        body { font-size: 11px; }
    }
</style>

<?php
$totNotas     = array_sum(array_map(fn($c) => (int)$c->total_notas,    $clientes));
$totFacturas  = array_sum(array_map(fn($c) => (int)$c->total_facturas,  $clientes));
$totFacturado = array_sum(array_map(fn($c) => (float)$c->total_facturado, $clientes));
?>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h5 class="mb-1">Reporte: Clientes Facturados</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/consignaciones">Consignaciones</a></li>
                <li class="breadcrumb-item"><a href="/consignaciones/reportes">Reportes</a></li>
                <li class="breadcrumb-item active">Clientes Facturados</li>
            </ol>
        </nav>
    </div>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-print"></i> Imprimir
    </button>
</div>

<!-- Filtros -->
<div class="card mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                    value="<?= esc($filtros['fecha_inicio']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                    value="<?= esc($filtros['fecha_fin']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Vendedor</label>
                <select name="vendedor_id" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    <?php foreach ($vendedores as $v): ?>
                        <option value="<?= $v->id ?>" <?= $filtros['vendedor_id'] == $v->id ? 'selected' : '' ?>>
                            <?= esc($v->seller) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="d-none d-print-block mb-3">
    <h5 class="mb-0">Reporte: Clientes Facturados</h5>
    <small>Período: <?= esc($filtros['fecha_inicio']) ?> al <?= esc($filtros['fecha_fin']) ?></small>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="fw-bold small"><?= count($clientes) ?> cliente(s)</span>
        <span class="fw-bold small text-primary">Total facturado: $<?= number_format($totFacturado, 2) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($clientes)): ?>
            <p class="text-center text-muted py-4 mb-0">No hay datos para el período seleccionado.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Cliente</th>
                        <th class="text-center"># Notas con cliente</th>
                        <th class="text-center"># Facturas</th>
                        <th class="text-end">Total Facturado</th>
                        <th>Primera NE</th>
                        <th>Última NE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td class="fw-bold"><?= esc($c->cliente_nombre) ?></td>
                        <td class="text-center"><?= $c->total_notas ?></td>
                        <td class="text-center"><?= $c->total_facturas ?: '—' ?></td>
                        <td class="text-end">
                            <?= $c->total_facturado > 0 ? '$' . number_format($c->total_facturado, 2) : '—' ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($c->primera_fecha)) ?></td>
                        <td><?= date('d/m/Y', strtotime($c->ultima_fecha)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="text-end">Totales:</td>
                        <td class="text-center"><?= $totNotas ?></td>
                        <td class="text-center"><?= $totFacturas ?></td>
                        <td class="text-end">$<?= number_format($totFacturado, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
