<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Catálogo de Lotes
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" id="btnNuevoLote">
                        <i class="fa-solid fa-plus"></i> Nuevo Lote
                    </button>
                    <a href="<?= base_url('consignaciones') ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <?php $req = service('request'); ?>
            <div class="card-body border-bottom pb-2">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Producto</label>
                        <select name="producto_id" class="form-control form-control-sm" id="filtroProducto">
                            <option value="">Todos los productos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Estado</label>
                        <select name="activo" class="form-control form-control-sm">
                            <option value="" <?= ($filtros['activo'] ?? '') === '' ? 'selected' : '' ?>>Todos</option>
                            <option value="1" <?= ($filtros['activo'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                            <option value="0" <?= ($filtros['activo'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fa-solid fa-filter"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Número de Lote</th>
                                <th class="text-center">Vencimiento</th>
                                <th class="text-center">Manufactura</th>
                                <th>Descripción</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($lotes)): ?>
                                <tr><td colspan="9" class="text-center text-muted py-3">No hay lotes registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($lotes as $l): ?>
                                <tr>
                                    <td><?= $l->id ?></td>
                                    <td><small><?= esc($l->producto_codigo) ?></small></td>
                                    <td><?= esc($l->producto_nombre) ?></td>
                                    <td><strong><?= esc($l->numero_lote) ?></strong></td>
                                    <td class="text-center">
                                        <?= !empty($l->fecha_vencimiento)
                                            ? esc($l->fecha_vencimiento)
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <?= !empty($l->manufactura)
                                            ? esc($l->manufactura)
                                            : '<span class="text-muted">—</span>' ?>
                                    </td>
                                    <td><small><?= esc($l->descripcion ?: '—') ?></small></td>
                                    <td class="text-center">
                                        <?php if ($l->activo): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-outline-primary btn-editar-lote"
                                            data-id="<?= $l->id ?>"
                                            data-producto-id="<?= $l->producto_id ?>"
                                            data-producto-nombre="<?= esc($l->producto_codigo . ' — ' . $l->producto_nombre) ?>"
                                            data-numero="<?= esc($l->numero_lote) ?>"
                                            data-vence="<?= $l->fecha_vencimiento ?? '' ?>"
                                            data-manufactura="<?= $l->manufactura ?? '' ?>"
                                            data-desc="<?= esc($l->descripcion ?? '') ?>"
                                            title="Editar">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <?php if ($l->activo): ?>
                                        <button class="btn btn-xs btn-outline-danger btn-eliminar-lote"
                                            data-id="<?= $l->id ?>" title="Desactivar">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($pager)): ?>
                    <div class="mt-2"><?= $pager->links() ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal lote -->
<div class="modal fade" id="modalLote" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formLote">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLoteTitulo">Nuevo Lote</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="loteId" name="id" value="0">

                    <div class="mb-3">
                        <label class="form-label">Producto <span class="text-danger">*</span></label>
                        <select id="loteProducto" name="producto_id" class="form-control" required>
                            <option value="">Buscar producto...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de Lote <span class="text-danger">*</span></label>
                        <input type="text" name="numero_lote" id="loteNumero" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Vencimiento</label>
                            <input type="text" name="fecha_vencimiento" id="loteVence" class="form-control"
                                placeholder="Ej: 2025-12, Jun 2026, 12/2025…">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Manufactura</label>
                            <input type="text" name="manufactura" id="loteManufactura" class="form-control"
                                placeholder="Ej: Ene 2025, 2025-01-15…">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="loteDesc" class="form-control" placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    // Select2 para producto en modal
    $('#loteProducto').select2({
        dropdownParent: $('#modalLote'),
        language: 'es',
        placeholder: 'Buscar producto...',
        allowClear: true,
        ajax: {
            url: '<?= base_url('productos/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data.results }),
            cache: true,
        },
    });

    // Select2 filtro producto
    $('#filtroProducto').select2({
        language: 'es',
        placeholder: 'Todos los productos',
        allowClear: true,
        ajax: {
            url: '<?= base_url('productos/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data.results }),
            cache: true,
        },
    });

    // Preseleccionar filtro producto si viene en query
    <?php if (!empty($filtros['producto_id'])): ?>
    // Restore selected filter product via AJAX
    $.getJSON('<?= base_url('productos/searchAjax') ?>?q=', data => {}).always(function(){});
    <?php endif; ?>

    function limpiarModalLote() {
        $('#loteId').val(0);
        $('#loteNumero').val('');
        $('#loteVence').val('');
        $('#loteManufactura').val('');
        $('#loteDesc').val('');
        $('#loteProducto').val(null).trigger('change');
    }

    $('#btnNuevoLote').on('click', function () {
        $('#modalLoteTitulo').text('Nuevo Lote');
        limpiarModalLote();
        $('#modalLote').modal('show');
    });

    $(document).on('click', '.btn-editar-lote', function () {
        const b = $(this).data();
        $('#modalLoteTitulo').text('Editar Lote');
        $('#loteId').val(b.id);
        $('#loteNumero').val(b.numero);
        $('#loteVence').val(b.vence || '');
        $('#loteManufactura').val(b.manufactura || '');
        $('#loteDesc').val(b.desc || '');

        const opt = new Option(b.productoNombre, b.productoId, true, true);
        $('#loteProducto').html('').append(opt).trigger('change');

        $('#modalLote').modal('show');
    });

    $(document).on('click', '.btn-eliminar-lote', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Desactivar lote',
            text: '¿Desea desactivar este lote?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch('<?= base_url('consignaciones/lotes') ?>/' + id + '/eliminar', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
            }).then(r => r.json()).then(d => {
                if (d.success) location.reload();
                else Swal.fire('Error', d.message, 'error');
            });
        });
    });

    $('#formLote').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);

        fetch('<?= base_url('consignaciones/lotes/guardar') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
            body: fd,
        }).then(r => r.json()).then(d => {
            if (d.success) {
                $('#modalLote').modal('hide');
                location.reload();
            } else {
                Swal.fire('Error', d.message ?? 'Error al guardar.', 'error');
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
