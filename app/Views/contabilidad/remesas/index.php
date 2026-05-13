<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

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
    <div class="card-header d-flex justify-content-between py-2">
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
            <div class="col-md-3 mb-2">
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
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
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
                            ?>
                            <tr class="<?= $anulado ? 'table-danger' : ($cerrado ? 'table-secondary' : '') ?>">
                                <td>
                                    <a href="<?= base_url('contabilidad/remesas/' . $r->id) ?>"
                                       class="font-weight-bold text-dark">
                                        <?= esc($r->numero_remesa) ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y', strtotime($r->fecha)) ?></td>
                                <td class="small"><?= esc($r->descripcion) ?></td>
                                <td>
                                    <?php if ($r->tipo_partida_nombre): ?>
                                        <span class="badge badge-light border"><?= esc($r->tipo_partida_nombre) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?= (int)($r->num_asientos ?? 0) ?></span>
                                </td>
                                <td class="text-right font-weight-bold">
                                    <?= $anulado
                                        ? '<s class="text-muted">$' . number_format($r->total, 2) . '</s>'
                                        : '$' . number_format($r->total, 2) ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $color = $anulado ? 'danger' : ($cerrado ? 'secondary' : 'success');
                                    $icon  = $anulado ? 'fa-ban' : ($cerrado ? 'fa-lock' : 'fa-circle-check');
                                    ?>
                                    <span class="badge badge-<?= $color ?>">
                                        <i class="fa-solid <?= $icon ?> mr-1"></i><?= $r->estado ?>
                                    </span>
                                </td>
                                <td class="text-center">
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
});
</script>

<?= $this->endSection() ?>
