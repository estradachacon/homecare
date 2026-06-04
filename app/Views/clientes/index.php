<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-0">
                    <i class="fa-solid fa-users me-2"></i> Clientes
                </h4>

                <?php if (tienePermiso('crear_clientes')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCrearCliente">
                        <i class="fa-solid fa-plus"></i> Nuevo
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Modo Escritorio -->
                <!-- Buscador -->
                <form method="get" class="row mb-3">

                    <div class="col-md-10">
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            placeholder="Buscar por nombre, documento, NRC o cuenta contable"
                            value="<?= esc($q ?? '') ?>">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100">
                            <i class="fa-solid fa-search"></i> Buscar
                        </button>
                    </div>

                </form>

                <div class="table-responsive clientes-table-wrap">
                    <table class="table table-bordered table-hover align-middle clientes-mobile-table">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Documento</th>
                                <th>NRC</th>
                                <th>Teléfono</th>
                                <th style="width:100px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($clientes)): ?>

                            <?php foreach ($clientes as $c): ?>
                                <tr class="cliente-mobile-row" data-href="<?= base_url('clientes/'.$c->id) ?>">
                                    <td class="cliente-id-cell" data-label="#">
                                        <span class="badge bg-light text-dark">#<?= $c->id ?></span>
                                    </td>

                                    <td class="cliente-name-cell" data-label="Cliente">
                                        <strong><?= esc($c->nombre) ?></strong>
                                    </td>

                                    <td class="cliente-doc-cell" data-label="Documento">
                                        <?= esc($c->numero_documento) ?>
                                    </td>

                                    <td class="cliente-nrc-cell" data-label="NRC">
                                        <?= esc($c->nrc) ?>
                                    </td>

                                    <td class="cliente-phone-cell" data-label="Telefono">
                                        <?= esc($c->telefono) ?>
                                    </td>

                                    <td class="text-center cliente-actions-cell" data-label="Acciones">

                                        <a href="<?= base_url('clientes/'.$c->id) ?>"
                                           class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <?php if (tienePermiso('editar_clientes')): ?>
                                            <a href="<?= base_url('clientes/edit/'.$c->id) ?>"
                                               class="btn btn-sm btn-warning">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                            <?php endforeach ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No se encontraron clientes
                                </td>
                            </tr>
                        <?php endif ?>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div class="d-flex justify-content-center mt-3">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (tienePermiso('crear_clientes')): ?>
<div class="modal fade" id="modalCrearCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="formCrearCliente">
            <?= csrf_field() ?>
            <input type="hidden" name="desc_actividad" id="crear_desc_actividad">

            <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-user-plus mr-1"></i> Crear cliente
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tipo Documento</label>
                                <select name="tipo_documento" class="form-control">
                                    <option value="DUI">DUI</option>
                                    <option value="NIT">NIT</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Número Documento</label>
                                <input type="text" name="numero_documento" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>NRC</label>
                                <input type="text" name="nrc" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap mb-3" style="gap:.5rem 2.5rem;">
                                <input type="hidden" name="gran_contribuyente" value="0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="crear_gran_contribuyente" name="gran_contribuyente" value="1">
                                    <label class="custom-control-label" for="crear_gran_contribuyente">Gran Contribuyente</label>
                                </div>

                                <input type="hidden" name="exento_iva" value="0">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="crear_exento_iva" name="exento_iva" value="1">
                                    <label class="custom-control-label" for="crear_exento_iva">Exento de IVA</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="crear_nombre" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Correo</label>
                                <input type="email" name="correo" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Código de actividad / Giro</label>
                                <select name="cod_actividad" id="crear_cod_actividad" class="form-control">
                                    <option value="">Sin actividad asignada</option>
                                    <?php foreach (config('ActividadesEconomicas')->actividades as $cod => $desc): ?>
                                        <option value="<?= esc($cod) ?>"><?= esc($cod) ?> - <?= esc($desc) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Departamento</label>
                                <select name="departamento" id="crear_departamento" class="form-control">
                                    <option value="">Seleccione...</option>
                                    <?php foreach (($departamentos ?? []) as $dep): ?>
                                        <option value="<?= esc($dep->codigo) ?>"><?= esc($dep->nombre) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Municipio</label>
                                <select name="municipio" id="crear_municipio" class="form-control">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cuenta Contable</label>
                                <select name="cuenta_contable_id" id="crear_cuenta_contable_id" class="form-control"></select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Dirección</label>
                        <textarea name="direccion" rows="3" class="form-control"></textarea>
                    </div>

                    <div id="crearClienteError" class="alert alert-danger d-none mt-3 mb-0"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarCliente">
                        <i class="fa fa-save mr-1"></i> Guardar cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferModalLabel">Hacer Transferencia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="transferForm" action="<?= base_url('accounts-transfer') ?>" method="post">
                    <div class="mt-3 divAccount" id="gastoCuenta<?= esc($q['id'] ?? '') ?>">
                        <label class="form-label">Cuenta inicial</label>
                        <select name="account_source"
                            id="account_source"
                            class="form-control select2-account"
                            data-initial-id=""
                            data-initial-text="">
                        </select>
                    </div>

                    <!-- Cuenta destino -->
                    <div class="form-group mt-3">
                        <label for="cuentaDestino">Cuenta Destino</label>
                        <select name="account_destination"
                            id="account_destination"
                            class="form-control select2-account"
                            data-initial-id=""
                            data-initial-text="">
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="montoTransferir">Monto</label>
                        <input type="number" class="form-control" id="montoTransferir" name="monto" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcionTransferencia">Descripción</label>
                        <input type="text" class="form-control" id="descripcionTransferencia" name="descripcion" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Realizar Transferencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const accountSearchUrl = "<?= base_url('accounts-list') ?>";
    const clientesActividadesMap = <?= json_encode(config('ActividadesEconomicas')->actividades) ?>;

    $(document).ready(function() {
        if (!$('#modalCrearCliente').length) return;

        $.fn.modal.Constructor.prototype._enforceFocus = function() {};

        $('#crear_cod_actividad').select2({
            placeholder: 'Buscar por codigo o nombre de actividad...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalCrearCliente')
        });

        $('#crear_cod_actividad').on('change', function() {
            const cod = $(this).val();
            $('#crear_desc_actividad').val(clientesActividadesMap[cod] || '');
        });

        $('#crear_cuenta_contable_id').select2({
            placeholder: 'Buscar cuenta contable',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalCrearCliente'),
            ajax: {
                url: "<?= base_url('clientes/cuentas-contables-select2') ?>",
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term || '' }),
                processResults: data => data
            }
        });

        $('#crear_departamento').on('change', function() {
            const departamento = $(this).val();
            $('#crear_municipio').html('<option value="">Cargando...</option>');

            if (!departamento) {
                $('#crear_municipio').html('<option value="">Seleccione...</option>');
                return;
            }

            $.ajax({
                url: "<?= base_url('clientes/municipios-por-departamento') ?>",
                type: "GET",
                dataType: "json",
                data: { departamento },
                success: function(response) {
                    let options = '<option value="">Seleccione...</option>';
                    response.forEach(mun => {
                        options += `<option value="${mun.codigo}">${mun.nombre}</option>`;
                    });
                    $('#crear_municipio').html(options);
                },
                error: function() {
                    $('#crear_municipio').html('<option value="">Seleccione...</option>');
                }
            });
        });

        $('#modalCrearCliente').on('shown.bs.modal', function() {
            $('#crear_nombre').trigger('focus');
        });

        $('#modalCrearCliente').on('hidden.bs.modal', function() {
            $('#formCrearCliente')[0].reset();
            $('#crear_cod_actividad').val(null).trigger('change');
            $('#crear_cuenta_contable_id').val(null).trigger('change');
            $('#crear_municipio').html('<option value="">Seleccione...</option>');
            $('#crearClienteError').addClass('d-none').text('');
        });

        $('#formCrearCliente').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const btn = $('#btnGuardarCliente');
            const errorBox = $('#crearClienteError');

            errorBox.addClass('d-none').text('');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Guardando...');

            $.ajax({
                url: "<?= base_url('clientes/store-ajax') ?>",
                method: "POST",
                data: form.serialize(),
                dataType: "json",
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    if (response.csrf) {
                        form.find('input[name="<?= csrf_token() ?>"]').val(response.csrf);
                    }

                    if (!response.success) {
                        errorBox.removeClass('d-none').text(response.message || 'No se pudo crear el cliente.');
                        return;
                    }

                    $('#modalCrearCliente').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Cliente creado',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: 'Ver cliente',
                        cancelButtonText: 'Cerrar',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary m-2',
                            cancelButton: 'btn btn-secondary m-2'
                        }
                    }).then(result => {
                        if (result.isConfirmed && response.cliente?.id) {
                            window.location.href = "<?= base_url('clientes') ?>/" + response.cliente.id;
                        } else {
                            window.location.reload();
                        }
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || {};
                    if (response.csrf) {
                        form.find('input[name="<?= csrf_token() ?>"]').val(response.csrf);
                    }
                    errorBox.removeClass('d-none').text(response.message || 'No se pudo crear el cliente.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-save mr-1"></i> Guardar cliente');
                }
            });
        });
    });

    $('#transferForm').on('submit', function(e) {
        e.preventDefault(); // Evita recargar la página

        $.ajax({
            url: "/accounts-transfer",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {

                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: response.message,
                        confirmButtonText: "Aceptar"
                    }).then(() => {
                        window.location.href = "/accounts";
                    });

                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo procesar la transferencia."
                });
            }
        });
    });

    $(document).ready(function() {
        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
        // Interceptar SOLO los forms de agregar destino
        $('.select2-account').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar cuenta...',
            allowClear: true,
            minimumInputLength: 1,
            dropdownParent: $('#transferModal'), // importante dentro del modal
            ajax: {
                url: accountSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },

                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.name + "  ||  Saldo: $" + item.balance
                        }))
                    };
                }
            }
        });

    });
</script>

<style>
    @media (max-width: 767.98px) {
        .card-header.d-flex {
            gap: .75rem;
            align-items: center;
            justify-content: space-between;
        }

        .clientes-table-wrap {
            overflow: visible;
        }

        .clientes-mobile-table {
            border-collapse: separate;
            border-spacing: 0 .65rem;
        }

        .clientes-mobile-table thead {
            display: none;
        }

        .clientes-mobile-table tbody,
        .clientes-mobile-table tr,
        .clientes-mobile-table td {
            display: block;
            width: 100%;
        }

        .clientes-mobile-table tr.cliente-mobile-row {
            position: relative;
            cursor: pointer;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem;
            background: #fff;
            box-shadow: 0 .125rem .45rem rgba(15, 23, 42, .06);
        }

        .clientes-mobile-table tr.cliente-mobile-row:active {
            background: #f8fafc;
        }

        .clientes-mobile-table td {
            border: 0;
            padding: .18rem 0;
        }

        .clientes-mobile-table .cliente-id-cell,
        .clientes-mobile-table .cliente-doc-cell,
        .clientes-mobile-table .cliente-nrc-cell,
        .clientes-mobile-table .cliente-phone-cell {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            font-size: .9rem;
        }

        .clientes-mobile-table .cliente-id-cell::before,
        .clientes-mobile-table .cliente-doc-cell::before,
        .clientes-mobile-table .cliente-nrc-cell::before,
        .clientes-mobile-table .cliente-phone-cell::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: .78rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .clientes-mobile-table .cliente-name-cell {
            margin: .35rem 0 .45rem;
            font-size: 1rem;
            line-height: 1.25;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: normal;
        }

        .clientes-mobile-table .cliente-nrc-cell {
            display: none;
        }

        .clientes-mobile-table .cliente-actions-cell {
            display: none;
        }

        .clientes-mobile-table .text-muted[colspan] {
            display: table-cell;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cliente-mobile-row[data-href]').forEach(row => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('a, button')) return;
                window.location.href = this.dataset.href;
            });
        });

        const searchInput = document.getElementById('searchInput');
        const tableContainer = document.getElementById('table-container');
        const loadingSpinner = document.getElementById('loading-spinner');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const perPageSelect = document.getElementById('perPageSelect');
        if (!searchInput || !tableContainer || !loadingSpinner || !clearSearchBtn || !perPageSelect) {
            return;
        }
        const baseUrl = '<?= base_url('accounts/searchAjax') ?>';

        let searchTimeout;

        function loadResults(query = '', page = 1) {
            const perPage = perPageSelect.value;
            const url = `${baseUrl}?q=${encodeURIComponent(query)}&page=${page}&perPage=${perPage}`;

            loadingSpinner.style.display = 'inline-block';

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    loadingSpinner.style.display = 'none';
                    updateClearButton(query);
                    rebindEvents();
                })
                .catch(() => {
                    loadingSpinner.style.display = 'none';
                    tableContainer.innerHTML =
                        '<div class="alert alert-danger">Error al cargar los datos.</div>';
                });
        }
        perPageSelect.addEventListener('change', () => {
            loadResults(searchInput.value.trim(), 1);
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            searchTimeout = setTimeout(() => {
                loadResults(query, 1);
            }, 300);
        });

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            loadResults('', 1);
            updateClearButton('');
        });

        function updateClearButton(query) {
            clearSearchBtn.style.display = query.length ? 'inline-block' : 'none';
        }

        function rebindEvents() {

            document.querySelectorAll('#pagination-links a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    loadResults(searchInput.value.trim(), page);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.onclick = handleDelete;
            });
            document.querySelectorAll('.toggle-details').forEach(btn => {
                btn.onclick = function() {
                    const details = this.closest('.card').querySelector('.details');
                    details.classList.toggle('d-none');
                    this.textContent = details.classList.contains('d-none') ?
                        'Ver' :
                        'Ocultar';
                };
            });
        }

        function handleDelete() {
            const id = this.dataset.id;

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch("<?= base_url('accounts/delete') ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire({
                            icon: data.status,
                            title: data.status === 'success' ? 'Éxito' : 'Error',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        if (data.status === 'success') {
                            loadResults(searchInput.value.trim());
                        }
                    });
            });
        }

        rebindEvents();
        updateClearButton(searchInput.value.trim());
    });
</script>

<?= $this->endSection() ?>
