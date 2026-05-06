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
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="header-title mb-0">Catálogo de Pacientes</h4>

                <button type="button" class="btn btn-primary btn-sm" id="btnNuevo">
                    <i class="fa-solid fa-plus mr-1"></i> Nuevo paciente
                </button>
            </div>

            <div class="card-body">
                <!-- Filtro búsqueda -->
                <form method="GET" class="mb-3">
                    <div class="input-group" style="max-width:400px;">
                        <input type="text" name="q" class="form-control"
                            placeholder="Buscar por nombre o identificación..."
                            value="<?= esc($q) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fa-solid fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Identificación</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th class="text-center" style="width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pacientes)): ?>
                                <?php foreach ($pacientes as $p): ?>
                                    <tr>
                                        <td><?= esc($p->nombre) ?></td>
                                        <td><?= esc($p->identificacion ?? '—') ?></td>
                                        <td><?= esc($p->telefono ?? '—') ?></td>
                                        <td><?= esc($p->correo ?? '—') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning btnEditar"
                                                data-id="<?= $p->id ?>"
                                                data-nombre="<?= esc($p->nombre) ?>"
                                                data-identificacion="<?= esc($p->identificacion ?? '') ?>"
                                                data-telefono="<?= esc($p->telefono ?? '') ?>"
                                                data-correo="<?= esc($p->correo ?? '') ?>">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btnEliminar"
                                                data-id="<?= $p->id ?>"
                                                data-nombre="<?= esc($p->nombre) ?>">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No hay pacientes registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?= $pager->links() ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal crear / editar -->
<div class="modal fade" id="modalPaciente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Nuevo Paciente</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formPaciente">
                <div class="modal-body">
                    <input type="hidden" id="pacienteId" name="id" value="">

                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="fNombre" name="nombre" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Identificación</label>
                        <input type="text" id="fIdentificacion" name="identificacion" class="form-control"
                            placeholder="DUI, pasaporte, etc.">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" id="fTelefono" name="telefono" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Correo</label>
                                <input type="email" id="fCorreo" name="correo" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div id="formError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <i class="fa-solid fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Abrir modal nuevo ────────────────────────────────────────────
    $('#btnNuevo').on('click', function() {
        $('#modalTitulo').text('Nuevo Paciente');
        $('#formPaciente')[0].reset();
        $('#pacienteId').val('');
        $('#formError').addClass('d-none').text('');
        $('#modalPaciente').modal('show');
    });

    // ── Abrir modal editar ───────────────────────────────────────────
    $(document).on('click', '.btnEditar', function() {
        const $btn = $(this);
        $('#modalTitulo').text('Editar Paciente');
        $('#pacienteId').val($btn.data('id'));
        $('#fNombre').val($btn.data('nombre'));
        $('#fIdentificacion').val($btn.data('identificacion'));
        $('#fTelefono').val($btn.data('telefono'));
        $('#fCorreo').val($btn.data('correo'));
        $('#formError').addClass('d-none').text('');
        $('#modalPaciente').modal('show');
    });

    // ── Guardar ──────────────────────────────────────────────────────
    $('#formPaciente').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnGuardar');
        const err = $('#formError');
        err.addClass('d-none').text('');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '<?= base_url('pacientes/guardar') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success(res) {
                if (!res.success) {
                    err.removeClass('d-none').text(res.message || 'Error al guardar.');
                    return;
                }
                $('#modalPaciente').modal('hide');
                location.reload();
            },
            error() {
                err.removeClass('d-none').text('Error de conexión.');
            },
            complete() {
                btn.prop('disabled', false).html('<i class="fa-solid fa-save mr-1"></i> Guardar');
            },
        });
    });

    // ── Eliminar ─────────────────────────────────────────────────────
    $(document).on('click', '.btnEliminar', function() {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Eliminar paciente?',
            html: `<strong>${nombre}</strong> será marcado como inactivo.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;

            $.ajax({
                url: `<?= base_url('pacientes/eliminar') ?>/${id}`,
                method: 'POST',
                data: { '<?= csrf_token() ?>': CSRF },
                dataType: 'json',
                success(res) {
                    if (res.success) location.reload();
                    else Swal.fire('Error', res.message, 'error');
                },
                error() { Swal.fire('Error', 'Error de conexión.', 'error'); },
            });
        });
    });
</script>

<?= $this->endSection() ?>
