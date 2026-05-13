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
    <div class="card-header d-flex justify-content-between py-2">
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
            <div class="col-md-3 mb-2 d-flex gap-1">
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
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>N° Recupero</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Forma de cobro</th>
                            <th>Referencia</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recuperos as $r): ?>
                            <?php
                            $fc    = $formaCobro[$r->forma_cobro] ?? ['label' => ucfirst($r->forma_cobro), 'color' => 'secondary'];
                            $anulado  = ($r->estado === 'ANULADO');
                            $aplicado = ($r->estado === 'APLICADO');
                            ?>
                            <tr class="<?= $anulado ? 'table-danger' : ($aplicado ? 'table-primary' : '') ?>">
                                <td>
                                    <a href="<?= base_url('recuperos/' . $r->id) ?>"
                                       class="font-weight-bold text-dark">
                                        <?= esc($r->numero_recupero) ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y', strtotime($r->fecha)) ?></td>
                                <td><?= esc($r->cliente_nombre) ?></td>
                                <td>
                                    <span class="badge badge-<?= $fc['color'] ?>">
                                        <?= $fc['label'] ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= esc($r->referencia ?? '—') ?></td>
                                <td class="text-right font-weight-bold">
                                    <?= $anulado
                                        ? '<s class="text-muted">$ ' . number_format($r->total, 2) . '</s>'
                                        : '$ ' . number_format($r->total, 2) ?>
                                </td>
                                <td class="text-center">
                                    <?php $estadoColor = $anulado ? 'danger' : ($aplicado ? 'primary' : 'success'); ?>
                                    <span class="badge badge-<?= $estadoColor ?>">
                                        <?= $r->estado ?>
                                    </span>
                                </td>
                                <td class="text-center">
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
});
</script>
<?= $this->endSection() ?>
