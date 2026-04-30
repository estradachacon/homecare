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
$totEnviado    = array_sum(array_map(fn($p) => (float)$p->total_enviado,    $productos));
$totFacturado  = array_sum(array_map(fn($p) => (float)$p->total_facturado,  $productos));
$totDevuelto   = array_sum(array_map(fn($p) => (float)$p->total_devuelto,   $productos));
$totStock      = array_sum(array_map(fn($p) => (float)$p->total_stock,      $productos));
$totValor      = array_sum(array_map(fn($p) => (float)$p->valor_enviado,    $productos));
?>

<div class="d-flex justify-content-between mb-3 no-print">
    <div>
        <h5 class="mb-1">Reporte: Productos</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/consignaciones">Consignaciones</a></li>
                <li class="breadcrumb-item"><a href="/consignaciones/reportes">Reportes</a></li>
                <li class="breadcrumb-item active">Productos</li>
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
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Estado NE</label>
                <select name="estado" class="form-control form-control-sm">
                    <option value="">Todas</option>
                    <option value="abierta" <?= $filtros['estado'] === 'abierta' ? 'selected' : '' ?>>Abiertas</option>
                    <option value="cerrada" <?= $filtros['estado'] === 'cerrada' ? 'selected' : '' ?>>Cerradas</option>
                    <option value="anulada" <?= $filtros['estado'] === 'anulada' ? 'selected' : '' ?>>Anuladas</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="d-none d-print-block mb-3">
    <h5 class="mb-0">Reporte: Productos Consignados</h5>
    <small>Período: <?= esc($filtros['fecha_inicio']) ?> al <?= esc($filtros['fecha_fin']) ?></small>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="fw-bold small"><?= count($productos) ?> producto(s)</span>
        <span class="fw-bold small text-primary">Valor enviado total: $<?= number_format($totValor, 2) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($productos)): ?>
            <p class="text-center text-muted py-4 mb-0">No hay datos para el período seleccionado.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th class="text-center"># NE</th>
                        <th class="text-end">Enviado</th>
                        <th class="text-end">Facturado</th>
                        <th class="text-end">Devuelto</th>
                        <th class="text-end">Stock Vend.</th>
                        <th class="text-end">Valor Enviado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td class="text-muted small"><?= esc($p->producto_codigo) ?></td>
                        <td><?= esc($p->producto_nombre) ?></td>
                        <td class="text-center"><?= $p->total_notas ?></td>
                        <td class="text-end"><?= number_format($p->total_enviado, 2) ?></td>
                        <td class="text-end"><?= number_format($p->total_facturado, 2) ?></td>
                        <td class="text-end"><?= number_format($p->total_devuelto, 2) ?></td>
                        <td class="text-end"><?= number_format($p->total_stock, 2) ?></td>
                        <td class="text-end">$<?= number_format($p->valor_enviado, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Totales:</td>
                        <td class="text-end"><?= number_format($totEnviado,   2) ?></td>
                        <td class="text-end"><?= number_format($totFacturado, 2) ?></td>
                        <td class="text-end"><?= number_format($totDevuelto,  2) ?></td>
                        <td class="text-end"><?= number_format($totStock,     2) ?></td>
                        <td class="text-end">$<?= number_format($totValor,    2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
