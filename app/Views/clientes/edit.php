<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .client-edit-card {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
    }

    .client-edit-header {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: white;
        padding: 1.25rem 1.5rem;
    }

    .section-title {
        font-size: .85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05rem;
        color: #6c757d;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: .5rem;
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
        font-size: .9rem;
    }

    .form-control,
    .select2-container .select2-selection--single {
        border-radius: 10px !important;
        min-height: 38px;
    }

    .input-group .form-control {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    .input-group-append .btn {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .soft-box {
        background: #f8f9fa;
        border: 1px solid #edf0f2;
        border-radius: 14px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .sticky-actions {
        position: sticky;
        bottom: 0;
        background: white;
        border-top: 1px solid #e9ecef;
        padding: 1rem;
        margin: 1.5rem -1.25rem -1.25rem;
        z-index: 5;
    }
</style>

<div class="row">
    <div class="col-md-12">

        <div class="card shadow-sm client-edit-card">

            <div class="card-header d-flex align-items-center">
                <div>
                    <h4 class="mb-1">Editar Cliente
                    </h4>
                    <small><?= esc($cliente->nombre) ?></small>
                </div>

                <a href="<?= base_url('clientes') ?>" class="btn btn-light btn-sm ml-auto">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card-body">

                <form action="<?= base_url('clientes/update/' . $cliente->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="soft-box">
                        <div class="section-title">
                            <i class="fa-solid fa-id-card"></i> Datos fiscales
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tipo Documento</label>
                                    <select name="tipo_documento" class="form-control">
                                        <option value="DUI" <?= $cliente->tipo_documento == 'DUI' ? 'selected' : '' ?>>DUI</option>
                                        <option value="NIT" <?= $cliente->tipo_documento == 'NIT' ? 'selected' : '' ?>>NIT</option>
                                        <option value="PASAPORTE" <?= $cliente->tipo_documento == 'PASAPORTE' ? 'selected' : '' ?>>Pasaporte</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Número Documento</label>
                                    <input type="text" name="numero_documento" class="form-control"
                                           value="<?= esc($cliente->numero_documento) ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>NRC</label>
                                    <input type="text" name="nrc" class="form-control"
                                           value="<?= esc($cliente->nrc) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="soft-box">
                        <div class="section-title">
                            <i class="fa-solid fa-address-book"></i> Información general
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" class="form-control"
                                           value="<?= esc($cliente->nombre) ?>" required>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="text" name="telefono" class="form-control"
                                           value="<?= esc($cliente->telefono) ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Correo</label>
                                    <input type="email" name="correo" class="form-control"
                                           value="<?= esc($cliente->correo) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="soft-box">
                        <div class="section-title">
                            <i class="fa-solid fa-location-dot"></i> Ubicación
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Departamento</label>
                                    <select name="departamento" id="departamento" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($departamentos as $dep): ?>
                                            <option value="<?= esc($dep->codigo) ?>"
                                                <?= $cliente->departamento == $dep->codigo ? 'selected' : '' ?>>
                                                <?= esc($dep->nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Municipio</label>
                                    <select name="municipio" id="municipio" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($municipios as $mun): ?>
                                            <option value="<?= esc($mun->codigo) ?>"
                                                <?= $cliente->municipio == $mun->codigo ? 'selected' : '' ?>>
                                                <?= esc($mun->nombre) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <textarea name="direccion" rows="3" class="form-control"><?= esc($cliente->direccion) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="soft-box mb-0">
                        <div class="section-title">
                            <i class="fa-solid fa-scale-balanced"></i> Configuración contable
                        </div>

                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group mb-0">
                                    <label>Cuenta Contable</label>

                                    <div class="input-group">
                                        <select name="cuenta_contable_id" id="cuenta_contable_id" class="form-control">
                                            <?php if (!empty($cuentaSeleccionada)): ?>
                                                <option value="<?= esc($cuentaSeleccionada->id) ?>" selected>
                                                    <?= esc($cuentaSeleccionada->codigo . ' - ' . $cuentaSeleccionada->nombre) ?>
                                                </option>
                                            <?php endif; ?>
                                        </select>

                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearCuentaModal">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <small class="text-muted">
                                        Asociar una subcuenta hija de 110201 CLIENTES LOCALES.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="sticky-actions text-right">
                        <a href="<?= base_url('clientes') ?>" class="btn btn-light">
                            Cancelar
                        </a>

                        <button class="btn btn-primary">
                            <i class="fa fa-save"></i> Actualizar Cliente
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

<div class="modal fade" id="crearCuentaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formCrearCuenta">
            <?= csrf_field() ?>

            <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-plus-circle"></i> Crear cuenta contable
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        Se creará como hija de <strong>110201 CLIENTES LOCALES</strong>.
                    </div>

                    <div class="form-group">
                        <label>Nombre de la cuenta</label>
                        <input type="text"
                               name="nombre"
                               id="nombre_cuenta"
                               class="form-control"
                               value="<?= esc($cliente->nombre) ?>"
                               required>
                    </div>

                    <div id="crearCuentaError" class="alert alert-danger d-none"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-success" id="btnCrearCuenta">
                        <i class="fa fa-save"></i> Crear y asociar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
$(document).ready(function () {

    $('#cuenta_contable_id').select2({
        placeholder: 'Buscar cuenta contable',
        allowClear: true,
        width: '100%',
        ajax: {
            url: "<?= base_url('clientes/cuentas-contables-select2') ?>",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || ''
                };
            },
            processResults: function (data) {
                return data;
            }
        }
    });
    $('#departamento').on('change', function () {
        let departamento = $(this).val();

        $('#municipio').html('<option value="">Cargando...</option>');

        $.ajax({
            url: "<?= base_url('clientes/municipios-por-departamento') ?>",
            type: "GET",
            dataType: "json",
            data: {
                departamento: departamento
            },
            success: function (response) {
                let options = '<option value="">Seleccione...</option>';

                response.forEach(function (mun) {
                    options += `<option value="${mun.codigo}">${mun.nombre}</option>`;
                });

                $('#municipio').html(options);
            }
        });
    });
    $('#formCrearCuenta').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let btn = $('#btnCrearCuenta');
        let errorBox = $('#crearCuentaError');

        errorBox.addClass('d-none').text('');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creando...');

        $.ajax({
            url: "<?= base_url('clientes/cuentas-contables-crear') ?>",
            type: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function (response) {

                if (response.csrf) {
                    form.find('input[name="<?= csrf_token() ?>"]').val(response.csrf);
                }

                if (!response.success) {
                    errorBox.removeClass('d-none').text(response.message || 'No se pudo crear la cuenta');
                    return;
                }

                let option = new Option(response.cuenta.text, response.cuenta.id, true, true);

                $('#cuenta_contable_id')
                    .append(option)
                    .trigger('change');

                $('#crearCuentaModal').modal('hide');

                $('#nombre_cuenta').val('');
            },
            error: function () {
                errorBox.removeClass('d-none').text('Error al comunicarse con el servidor');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Crear y asociar');
            }
        });
    });

});
</script>

<?= $this->endSection() ?>