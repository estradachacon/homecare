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
$estClases = [
    'abierta' => 'badge-success',
    'cerrada' => 'badge-secondary',
    'anulada' => 'badge-danger',
];
$apClases = [
    'aprobada'  => 'badge-success',
    'rechazada' => 'badge-danger',
    'pendiente' => 'badge-warning',
];
$totalMonto = array_sum(array_map(fn($n) => (float)$n->subtotal, $notas));
?>

<!-- Cabecera -->
<div class="d-flex justify-content-between mb-3 no-print">
    <div>
        <h5 class="mb-1">Reporte: Notas de Envío</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/consignaciones">Consignaciones</a></li>
                <li class="breadcrumb-item"><a href="/consignaciones/reportes">Reportes</a></li>
                <li class="breadcrumb-item active">Notas de Envío</li>
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
                    <option value="">Todos</option>
                    <option value="abierta"  <?= $filtros['estado'] === 'abierta'  ? 'selected' : '' ?>>Abierta</option>
                    <option value="cerrada"  <?= $filtros['estado'] === 'cerrada'  ? 'selected' : '' ?>>Cerrada</option>
                    <option value="anulada"  <?= $filtros['estado'] === 'anulada'  ? 'selected' : '' ?>>Anulada</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Aprobación</label>
                <select name="aprobacion_estado" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    <option value="pendiente"  <?= $filtros['aprobacion_estado'] === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                    <option value="aprobada"   <?= $filtros['aprobacion_estado'] === 'aprobada'   ? 'selected' : '' ?>>Aprobada</option>
                    <option value="rechazada"  <?= $filtros['aprobacion_estado'] === 'rechazada'  ? 'selected' : '' ?>>Rechazada</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Encabezado de impresión -->
<div class="d-none d-print-block mb-3">
    <h5 class="mb-0">Reporte: Notas de Envío</h5>
    <small>Período: <?= esc($filtros['fecha_inicio']) ?> al <?= esc($filtros['fecha_fin']) ?></small>
</div>

<!-- Tabla -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="fw-bold small"><?= count($notas) ?> nota(s) encontrada(s)</span>
        <span class="fw-bold small text-primary">Total: $<?= number_format($totalMonto, 2) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($notas)): ?>
            <p class="text-center text-muted py-4 mb-0">No hay datos para el período seleccionado.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>NE</th>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Paciente</th>
                        <th>Doctor</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Aprobación</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notas as $n): ?>
                    <tr>
                        <td>
                            <a href="/consignaciones/<?= $n->id ?>" class="no-print">
                                <?= esc($n->numero) ?>
                            </a>
                            <span class="d-none d-print-inline"><?= esc($n->numero) ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($n->fecha)) ?></td>
                        <td><?= esc($n->vendedor_nombre) ?></td>
                        <td><?= esc($n->nombre ?: '—') ?></td>
                        <td><?= esc($n->doctor_nombre ?: '—') ?></td>
                        <td><?= esc($n->cliente_nombre ?: '—') ?></td>
                        <td>
                            <span class="badge <?= $estClases[$n->estado] ?? 'badge-secondary' ?>">
                                <?= ucfirst($n->estado) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $apClases[$n->aprobacion_estado ?? 'pendiente'] ?? 'badge-warning' ?>">
                                <?= ucfirst($n->aprobacion_estado ?? 'pendiente') ?>
                            </span>
                        </td>
                        <td class="text-end">$<?= number_format($n->subtotal, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="8" class="text-end">Total:</td>
                        <td class="text-end">$<?= number_format($totalMonto, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
