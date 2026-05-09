<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-tags me-2 mr-1"></i>Tipos de Partida
                </h4>
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalTipo">
                    <i class="fa-solid fa-plus"></i> Nuevo tipo
                </button>
            </div>

            <div class="card-body">
                <p class="text-muted small mb-3">
                    Define los tipos de partida que se asignarán a los asientos contables.
                    En <strong>Configuración</strong> podrás vincular cada tipo a los procesos automáticos (cargas de factura, pagos, etc.).
                </p>

                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center" style="width:80px">Estado</th>
                            <th class="text-center" style="width:90px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTipos">
                        <?php if (empty($tipos)): ?>
                            <tr id="filaVacia">
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay tipos de partida. Crea el primero.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tipos as $t): ?>
                            <tr id="fila-<?= $t->id ?>">
                                <td><?= $t->id ?></td>
                                <td class="fw-semibold"><?= esc($t->nombre) ?></td>
                                <td class="text-muted small"><?= esc($t->descripcion ?? '—') ?></td>
                                <td class="text-center">
                                    <?php if ($t->activo): ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-primary btn-sm btn-editar"
                                        data-id="<?= $t->id ?>"
                                        data-nombre="<?= esc($t->nombre) ?>"
                                        data-descripcion="<?= esc($t->descripcion ?? '') ?>"
                                        data-activo="<?= $t->activo ?>"
                                        title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <?php if ($t->activo): ?>
                                    <button class="btn btn-danger btn-sm btn-desactivar"
                                        data-id="<?= $t->id ?>"
                                        data-nombre="<?= esc($t->nombre) ?>"
                                        title="Desactivar">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <a href="<?= base_url('contabilidad') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear / Editar -->
<div class="modal fade" id="modalTipo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTipoTitulo">Nuevo Tipo de Partida</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId" value="">
                <div class="form-group mb-3">
                    <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="inputNombre" class="form-control" placeholder="Ej: Partida de Venta">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label small fw-semibold">Descripción</label>
                    <input type="text" id="inputDescripcion" class="form-control" placeholder="Descripción opcional">
                </div>
                <div class="form-group mb-2" id="rowActivo" style="display:none;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="inputActivo" checked>
                        <label class="form-check-label" for="inputActivo">Activo</label>
                    </div>
                </div>
                <div id="modalError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarTipo">
                    <i class="fa-solid fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {

    // ── Abrir modal para CREAR ────────────────────────────────────────────────
    $('[data-target="#modalTipo"]').on('click', function () {
        $('#modalTipoTitulo').text('Nuevo Tipo de Partida');
        $('#editId').val('');
        $('#inputNombre').val('');
        $('#inputDescripcion').val('');
        $('#rowActivo').hide();
        $('#modalError').addClass('d-none').text('');
        $('#modalTipo').modal('show');
    });

    // ── Abrir modal para EDITAR ───────────────────────────────────────────────
    $(document).on('click', '.btn-editar', function () {
        $('#modalTipoTitulo').text('Editar Tipo de Partida');
        $('#editId').val($(this).data('id'));
        $('#inputNombre').val($(this).data('nombre'));
        $('#inputDescripcion').val($(this).data('descripcion'));
        $('#inputActivo').prop('checked', $(this).data('activo') == 1);
        $('#rowActivo').show();
        $('#modalError').addClass('d-none').text('');
        $('#modalTipo').modal('show');
    });

    // ── Guardar ───────────────────────────────────────────────────────────────
    $('#btnGuardarTipo').on('click', function () {
        const id          = $('#editId').val();
        const nombre      = $('#inputNombre').val().trim();
        const descripcion = $('#inputDescripcion').val().trim();
        const activo      = $('#inputActivo').is(':checked') ? 1 : 0;
        const $btn        = $(this);

        if (!nombre) {
            $('#modalError').removeClass('d-none').text('El nombre es requerido.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

        const url  = id
            ? '<?= base_url('contabilidad/mantenimientos/tipos-partida/update') ?>/' + id
            : '<?= base_url('contabilidad/mantenimientos/tipos-partida/store') ?>';

        const body = new FormData();
        body.append('nombre', nombre);
        body.append('descripcion', descripcion);
        if (id) body.append('activo', activo);

        fetch(url, { method: 'POST', body })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    $('#modalError').removeClass('d-none').text(data.message);
                    return;
                }
                $('#modalTipo').modal('hide');
                Swal.fire({
                    icon: 'success', title: data.message,
                    timer: 1400, showConfirmButton: false,
                }).then(() => location.reload());
            })
            .finally(() => $btn.prop('disabled', false).html('<i class="fa-solid fa-save"></i> Guardar'));
    });

    // ── Desactivar ────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-desactivar', function () {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Desactivar tipo?',
            html: `El tipo <strong>${nombre}</strong> quedará inactivo y no aparecerá en los selectores.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch('<?= base_url('contabilidad/mantenimientos/tipos-partida/delete') ?>/' + id, { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) location.reload();
                    else Swal.fire('Error', data.message, 'error');
                });
        });
    });
});
</script>

<?= $this->endSection() ?>
