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
$totNotas    = array_sum(array_map(fn($p) => (int)$p->total_notas,    $pacientes));
$totProductos= array_sum(array_map(fn($p) => (float)$p->total_productos, $pacientes));
$totValor    = array_sum(array_map(fn($p) => (float)$p->total_valor,   $pacientes));
?>

<div class="d-flex justify-content-between mb-3 no-print">
    <div>
        <h5 class="mb-1">Reporte: Pacientes</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/consignaciones">Consignaciones</a></li>
                <li class="breadcrumb-item"><a href="/consignaciones/reportes">Reportes</a></li>
                <li class="breadcrumb-item active">Pacientes</li>
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
    <h5 class="mb-0">Reporte: Pacientes</h5>
    <small>Período: <?= esc($filtros['fecha_inicio']) ?> al <?= esc($filtros['fecha_fin']) ?></small>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="fw-bold small"><?= count($pacientes) ?> paciente(s)</span>
        <span class="fw-bold small text-primary">Valor total: $<?= number_format($totValor, 2) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($pacientes)): ?>
            <p class="text-center text-muted py-4 mb-0">No hay datos para el período seleccionado.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Paciente</th>
                        <th>Doctor</th>
                        <th>Vendedor</th>
                        <th class="text-center"># NE</th>
                        <th class="text-end">Unidades</th>
                        <th class="text-end">Valor Total</th>
                        <th>Primera NE</th>
                        <th>Última NE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $p): ?>
                    <tr>
                        <td class="fw-bold"><?= esc($p->paciente) ?></td>
                        <td><?= esc($p->doctor_nombre ?: '—') ?></td>
                        <td><?= esc($p->vendedor_nombre ?: '—') ?></td>
                        <td class="text-center"><?= $p->total_notas ?></td>
                        <td class="text-end"><?= number_format($p->total_productos, 2) ?></td>
                        <td class="text-end">$<?= number_format($p->total_valor, 2) ?></td>
                        <td><?= date('d/m/Y', strtotime($p->primera_fecha)) ?></td>
                        <td><?= date('d/m/Y', strtotime($p->ultima_fecha)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Totales:</td>
                        <td class="text-center"><?= $totNotas ?></td>
                        <td class="text-end"><?= number_format($totProductos, 2) ?></td>
                        <td class="text-end">$<?= number_format($totValor, 2) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
