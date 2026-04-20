<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #ced4da; border-radius: .375rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: .75rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
</style>

<div class="row">
    <div class="col-md-12">

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">Precios Recomendados por Vendedor / Producto</h4>
                <?php if (tienePermiso('gestionar_precios_consignaciones')): ?>
                    <button class="btn btn-success btn-sm" id="btnNuevoPrecio">
                        <i class="fa-solid fa-plus"></i> Nuevo Precio
                    </button>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" action="<?= base_url('consignaciones/precios') ?>" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="vendedor_id" class="form-control form-control-sm">
                                <option value="">Todos los vendedores</option>
                                <?php foreach ($vendedores as $v): ?>
                                    <option value="<?= $v->id ?>" <?= ($filtros['vendedor_id'] == $v->id) ? 'selected' : '' ?>>
                                        <?= esc($v->seller) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-filter"></i> Filtrar
                            </button>
                            <a href="<?= base_url('consignaciones/precios') ?>" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover" id="tablaPrecios">
                        <thead class="table-dark">
                            <tr>
                                <th>Vendedor</th>
                                <th>Cliente (opcional)</th>
                                <th>Producto</th>
                                <th class="text-end">Precio</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($precios)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No hay precios registrados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($precios as $p): ?>
                                    <tr>
                                        <td><?= esc($p->vendedor_nombre) ?></td>
                                        <td><?= $p->cliente_nombre ? esc($p->cliente_nombre) : '<span class="text-muted">—</span>' ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= esc($p->producto_codigo) ?></span>
                                            <?= esc($p->producto_nombre) ?>
                                        </td>
                                        <td class="text-end fw-bold">$<?= number_format($p->precio, 2) ?></td>
                                        <td class="text-center">
                                            <?php if (tienePermiso('gestionar_precios_consignaciones')): ?>
                                                <button class="btn btn-warning btn-xs btn-editar"
                                                    data-id="<?= $p->id ?>"
                                                    data-vendedor="<?= $p->vendedor_id ?>"
                                                    data-vendedor-nombre="<?= esc($p->vendedor_nombre) ?>"
                                                    data-cliente="<?= $p->cliente_id ?>"
                                                    data-cliente-nombre="<?= esc($p->cliente_nombre ?? '') ?>"
                                                    data-producto="<?= $p->producto_id ?>"
                                                    data-producto-nombre="<?= esc($p->producto_nombre . ' (' . $p->producto_codigo . ')') ?>"
                                                    data-precio="<?= $p->precio ?>">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-xs btn-eliminar" data-id="<?= $p->id ?>">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Precio -->
<div class="modal fade" id="modalPrecio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Nuevo Precio</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="precioId" value="">

                <div class="mb-3">
                    <label class="form-label">Vendedor <span class="text-danger">*</span></label>
                    <select id="modalVendedor" class="form-control">
                        <option value="">Seleccione...</option>
                        <?php foreach ($vendedores as $v): ?>
                            <option value="<?= $v->id ?>"><?= esc($v->seller) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cliente <small class="text-muted">(dejar vacío para aplicar a todos)</small></label>
                    <select id="modalCliente" class="form-control"></select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select id="modalProducto" class="form-control"></select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Precio <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="inputPrecioValor" class="form-control" min="0" step="0.01" placeholder="0.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarPrecio">
                    <i class="fa-solid fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    // Select2 cliente modal
    $('#modalCliente').select2({
        language: 'es',
        placeholder: 'Buscar cliente... (opcional)',
        allowClear: true,
        dropdownParent: $('#modalPrecio'),
        ajax: {
            url: '<?= base_url('clientes/buscar') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data }),
            cache: true,
        },
    });

    // Select2 producto modal
    $('#modalProducto').select2({
        language: 'es',
        placeholder: 'Buscar producto...',
        allowClear: true,
        dropdownParent: $('#modalPrecio'),
        ajax: {
            url: '<?= base_url('productos/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.results
            }),
            cache: true,
        },
    });

    // Abrir modal nuevo
    $('#btnNuevoPrecio').on('click', function () {
        $('#modalTitulo').text('Nuevo Precio');
        $('#precioId').val('');
        $('#modalVendedor').val('').trigger('change');
        $('#modalCliente').val(null).trigger('change');
        $('#modalProducto').val(null).trigger('change');
        $('#inputPrecioValor').val('');
        $('#modalPrecio').modal('show');
    });

    // Abrir modal edición
    $(document).on('click', '.btn-editar', function () {
        const btn = $(this);
        $('#modalTitulo').text('Editar Precio');
        $('#precioId').val(btn.data('id'));
        $('#modalVendedor').val(btn.data('vendedor')).trigger('change');
        $('#inputPrecioValor').val(btn.data('precio'));

        const clienteId   = btn.data('cliente');
        const clienteText = btn.data('cliente-nombre');
        if (clienteId) {
            const opt = new Option(clienteText, clienteId, true, true);
            $('#modalCliente').append(opt).trigger('change');
        } else {
            $('#modalCliente').val(null).trigger('change');
        }

        const productoId   = btn.data('producto');
        const productoText = btn.data('producto-nombre');
        const optP = new Option(productoText, productoId, true, true);
        $('#modalProducto').append(optP).trigger('change');

        $('#modalPrecio').modal('show');
    });

    // Guardar precio
    $('#btnGuardarPrecio').on('click', function () {
        const vendedorId = $('#modalVendedor').val();
        const productoId = $('#modalProducto').val();
        const precio     = parseFloat($('#inputPrecioValor').val());
        const clienteId  = $('#modalCliente').val();
        const id         = $('#precioId').val();

        if (!vendedorId || !productoId || isNaN(precio) || precio <= 0) {
            Swal.fire('Error', 'Complete todos los campos obligatorios.', 'error');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        fetch('<?= base_url('consignaciones/precios/guardar') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({
                id:          id,
                vendedor_id: vendedorId,
                cliente_id:  clienteId ?? '',
                producto_id: productoId,
                precio:      precio,
                '<?= csrf_token() ?>': csrfToken,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $('#modalPrecio').modal('hide');
                Swal.fire('Guardado', 'Precio guardado correctamente.', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message ?? 'Error al guardar.', 'error');
            }
        });
    });

    // Eliminar precio
    $(document).on('click', '.btn-eliminar', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar precio?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(`<?= base_url('consignaciones/precios') ?>/${id}/eliminar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminado', '', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    });
});
</script>

<?= $this->endSection() ?>
