<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$origenLabels = [
    'producto'  => ['label' => 'Regla por Producto', 'badge' => 'primary'],
    'vendedor'  => ['label' => 'Regla por Vendedor', 'badge' => 'info'],
    'general'   => ['label' => 'Porcentaje General', 'badge' => 'secondary'],
    'manual'    => ['label' => 'Manual',              'badge' => 'warning'],
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <div class="card-header py-2 d-flex justify-content-between">
                <h5 class="mb-0">
                    <i class="fa-solid fa-chart-bar text-primary mr-2"></i>
                    Reporte de Comisiones
                </h5>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm mr-1">
                        <i class="fa-solid fa-print mr-1"></i> Imprimir
                    </button>
                    <a href="<?= base_url('comisiones') ?>" class="btn btn-light btn-sm border">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>

            <div class="card-body">

                <!-- Filtros -->
                <form method="get" action="<?= base_url('comisiones/reportes') ?>" class="mb-4">
                    <div class="row g-2 align-items-end">

                        <div class="col-md-3">
                            <label class="small text-muted mb-1">Vendedor</label>
                            <select name="seller_id" class="form-control form-control-sm">
                                <option value="">Todos los vendedores</option>
                                <?php foreach ($sellers as $s): ?>
                                    <option value="<?= $s->id ?>" <?= $sellerId == $s->id ? 'selected' : '' ?>>
                                        <?= esc($s->seller) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="small text-muted mb-1">Desde</label>
                            <input type="date" name="desde" value="<?= esc($desde) ?>"
                                   class="form-control form-control-sm">
                        </div>

                        <div class="col-md-2">
                            <label class="small text-muted mb-1">Hasta</label>
                            <input type="date" name="hasta" value="<?= esc($hasta) ?>"
                                   class="form-control form-control-sm">
                        </div>

                        <div class="col-md-2">
                            <label class="small text-muted mb-1">Estado</label>
                            <select name="estado" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="generado"  <?= $estado === 'generado'  ? 'selected' : '' ?>>Generado</option>
                                <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm mr-1">
                                <i class="fa-solid fa-filter mr-1"></i> Filtrar
                            </button>
                            <?php if ($sellerId || $desde || $hasta || $estado): ?>
                                <a href="<?= base_url('comisiones/reportes') ?>"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-solid fa-xmark mr-1"></i> Limpiar
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </form>

                <!-- KPIs -->
                <div class="row g-3 mb-4">

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body py-3 text-center">
                                <small class="text-muted d-block">Total Ventas</small>
                                <span class="h5 fw-bold text-primary">
                                    $ <?= number_format($totalVentas, 2) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body py-3 text-center">
                                <small class="text-muted d-block">Total Comisión</small>
                                <span class="h5 fw-bold text-success">
                                    $ <?= number_format($totalComision, 2) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body py-3 text-center">
                                <small class="text-muted d-block">N° Liquidaciones</small>
                                <span class="h5 fw-bold text-dark">
                                    <?= number_format($numComisiones) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body py-3 text-center">
                                <small class="text-muted d-block">% Promedio Global</small>
                                <span class="h5 fw-bold text-info">
                                    <?= number_format($promGlobal, 2) ?>%
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sin datos -->
                <?php if (empty($porVendedor)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        No hay comisiones registradas para los filtros seleccionados.
                    </div>
                <?php else: ?>

                    <!-- Tabla por vendedor -->
                    <h6 class="text-muted text-uppercase small fw-bold mb-2">Resumen por Vendedor</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Vendedor</th>
                                    <th class="text-center">Liquidaciones</th>
                                    <th>Período</th>
                                    <th class="text-end">Total Ventas</th>
                                    <th class="text-end">Total Comisión</th>
                                    <th class="text-center">% Prom.</th>
                                    <th class="text-center no-print"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($porVendedor as $v): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= esc($v->vendedor_nombre ?? 'Sin nombre') ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-secondary"><?= $v->num_comisiones ?></span>
                                        </td>
                                        <td class="small text-muted">
                                            <?php if ($v->primera_fecha && $v->ultima_fecha): ?>
                                                <?= date('d/m/Y', strtotime($v->primera_fecha)) ?>
                                                &rarr;
                                                <?= date('d/m/Y', strtotime($v->ultima_fecha)) ?>
                                            <?php else: ?>
                                                &mdash;
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">$ <?= number_format($v->total_ventas, 2) ?></td>
                                        <td class="text-end text-success fw-semibold">
                                            $ <?= number_format($v->total_comision, 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info text-white">
                                                <?= number_format($v->prom_porcentaje, 2) ?>%
                                            </span>
                                        </td>
                                        <td class="text-center no-print">
                                            <?php
                                            $params = http_build_query([
                                                'seller_id' => $v->vendedor_id,
                                                'desde'     => $desde,
                                                'hasta'     => $hasta,
                                                'estado'    => $estado,
                                            ]);
                                            ?>
                                            <a href="<?= base_url('comisiones?' . $params) ?>"
                                               class="btn btn-xs btn-outline-primary"
                                               title="Ver liquidaciones de este vendedor">
                                                <i class="fa-solid fa-list"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold">
                                    <td colspan="3" class="text-end">TOTALES:</td>
                                    <td class="text-end text-primary">$ <?= number_format($totalVentas, 2) ?></td>
                                    <td class="text-end text-success">$ <?= number_format($totalComision, 2) ?></td>
                                    <td class="text-center text-info">
                                        <?= number_format($promGlobal, 2) ?>%
                                    </td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Desglose por origen -->
                    <?php if (!empty($porOrigen)): ?>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Desglose por Origen de Comisión</h6>
                        <div class="row g-2 mb-3">
                            <?php foreach ($porOrigen as $o): ?>
                                <?php
                                $key    = $o->origen_comision ?? 'general';
                                $info   = $origenLabels[$key] ?? ['label' => ucfirst($key), 'badge' => 'dark'];
                                $pct    = $totalComision > 0 ? ($o->total / $totalComision) * 100 : 0;
                                ?>
                                <div class="col-6 col-md-3">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body py-2 px-3">
                                            <span class="badge badge-<?= $info['badge'] ?> mb-1">
                                                <?= $info['label'] ?>
                                            </span>
                                            <div class="fw-bold">$ <?= number_format($o->total, 2) ?></div>
                                            <small class="text-muted"><?= number_format($pct, 1) ?>% del total</small>
                                            <div class="progress mt-1" style="height:4px">
                                                <div class="progress-bar bg-<?= $info['badge'] === 'warning' ? 'warning' : $info['badge'] ?>"
                                                     style="width:<?= number_format($pct, 1) ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { background: none !important; color: #000 !important; }
    .btn { display: none !important; }
    form { display: none !important; }
}
</style>

<?= $this->endSection() ?>
