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
                <h4 class="header-title mb-0">Catálogo de Doctores</h4>

                <button type="button" class="btn btn-primary btn-sm" id="btnNuevo">
                    <i class="fa-solid fa-plus mr-1"></i> Nuevo doctor
                </button>
            </div>

            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="input-group" style="max-width:400px;">
                        <input type="text" name="q" class="form-control"
                            placeholder="Buscar por nombre o especialidad..."
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
                                <th>Especialidad</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th class="text-center" style="width:120px">Foto</th>
                                <th class="text-center" style="width:100px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($doctores)): ?>
                                <?php foreach ($doctores as $d): ?>
                                    <tr>
                                        <td><?= esc($d->nombre) ?></td>
                                        <td><?= esc($d->especialidad ?? '—') ?></td>
                                        <td><?= esc($d->telefono ?? '—') ?></td>
                                        <td><?= esc($d->correo ?? '—') ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($d->foto)): ?>
                                                <button type="button" class="btn btn-sm btn-info btn-ver-foto"
                                                    data-foto="<?= base_url('upload/doctores/' . $d->foto) ?>"
                                                    data-nombre="<?= esc($d->nombre) ?>">
                                                    <i class="fa-solid fa-image"></i>
                                                </button>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-warning btnEditar"
                                                data-id="<?= $d->id ?>"
                                                data-nombre1="<?= esc($d->nombre1 ?? '') ?>"
                                                data-nombre2="<?= esc($d->nombre2 ?? '') ?>"
                                                data-apellido1="<?= esc($d->apellido1 ?? '') ?>"
                                                data-apellido2="<?= esc($d->apellido2 ?? '') ?>"
                                                data-especialidad="<?= esc($d->especialidad ?? '') ?>"
                                                data-telefono="<?= esc($d->telefono ?? '') ?>"
                                                data-correo="<?= esc($d->correo ?? '') ?>"
                                                data-foto="<?= esc($d->foto ?? '') ?>">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger btnEliminar"
                                                data-id="<?= $d->id ?>"
                                                data-nombre="<?= esc($d->nombre) ?>">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        No hay doctores registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?= $pager->links('default', 'bootstrap_full') ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDoctor" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Nuevo Doctor</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formDoctor">
                <div class="modal-body">
                    <input type="hidden" id="doctorId" name="id" value="">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="fNombre1" name="nombre1" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>2do Nombre</label>
                            <input type="text" id="fNombre2" name="nombre2" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Apellido <span class="text-danger">*</span></label>
                            <input type="text" id="fApellido1" name="apellido1" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>2do Apellido</label>
                            <input type="text" id="fApellido2" name="apellido2" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Especialidad</label>
                        <input type="text" id="fEspecialidad" name="especialidad" class="form-control"
                            placeholder="Ej. Medicina General">
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

                    <div class="form-group">
                        <label>Foto / documento</label>
                        <input type="file" id="fFoto" name="foto" accept="image/*" capture="environment" class="form-control">
                        <small class="form-text text-muted">Toma una foto desde el celular o sube un archivo existente.</small>
                        <div id="fotoPreview" class="mt-2 d-none">
                            <img src="" class="img-fluid rounded" style="max-height: 180px;">
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

    $('#btnNuevo').on('click', function() {
        $('#modalTitulo').text('Nuevo Doctor');
        $('#formDoctor')[0].reset();
        $('#doctorId').val('');
        $('#formError').addClass('d-none').text('');
        $('#modalDoctor').modal('show');
    });

    $(document).on('click', '.btnEditar', function() {
        const $btn = $(this);
        const fotoUrl = $btn.data('foto') ? '<?= base_url('upload/doctores/') ?>' + $btn.data('foto') : null;

        $('#modalTitulo').text('Editar Doctor');
        $('#doctorId').val($btn.data('id'));
        $('#fNombre1').val($btn.data('nombre1'));
        $('#fNombre2').val($btn.data('nombre2'));
        $('#fApellido1').val($btn.data('apellido1'));
        $('#fApellido2').val($btn.data('apellido2'));
        $('#fEspecialidad').val($btn.data('especialidad'));
        $('#fTelefono').val($btn.data('telefono'));
        $('#fCorreo').val($btn.data('correo'));
        $('#formError').addClass('d-none').text('');
        $('#fFoto').val('');

        if (fotoUrl) {
            $('#fotoPreview').removeClass('d-none').find('img').attr('src', fotoUrl);
        } else {
            $('#fotoPreview').addClass('d-none').find('img').attr('src', '');
        }

        $('#modalDoctor').modal('show');
    });

    $('#formDoctor').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnGuardar');
        const err = $('#formError');
        err.addClass('d-none').text('');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

        const formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('doctores/guardar') ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success(res) {
                if (!res.success) {
                    err.removeClass('d-none').text(res.message || 'Error al guardar.');
                    return;
                }
                $('#modalDoctor').modal('hide');
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

    function previewImage(input, previewSelector) {
        const file = input.files && input.files[0];
        const preview = $(previewSelector);
        if (!file) {
            preview.addClass('d-none').find('img').attr('src', '');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.removeClass('d-none').find('img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }

    $('#fFoto').on('change', function() {
        previewImage(this, '#fotoPreview');
    });

    $(document).on('click', '.btn-ver-foto', function() {
        const fotoUrl = $(this).data('foto');
        const nombre = $(this).data('nombre');
        $('#fotoModalLabel').text(`Foto de ${nombre}`);
        $('#fotoModalImg').attr('src', fotoUrl);
        $('#fotoModal').modal('show');
    });

    $(document).on('click', '.btnEliminar', function() {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Eliminar doctor?',
            html: `<strong>${nombre}</strong> será marcado como inactivo.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;

            $.ajax({
                url: `<?= base_url('doctores/eliminar') ?>/${id}`,
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

<div class="modal fade" id="fotoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fotoModalLabel">Foto del doctor</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="fotoModalImg" src="" class="img-fluid rounded" alt="Foto del doctor">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
