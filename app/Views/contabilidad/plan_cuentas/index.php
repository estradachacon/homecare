<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .card {
        border-radius: 12px;
    }

    .table thead th {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .modal-content {
        border-radius: 10px;
    }

    .modal-header {
        border-bottom: 1px solid #eee;
    }

    .modal-footer {
        border-top: 1px solid #eee;
    }

    .toggle-cuenta {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 8px;
    }

    .toggle-cuenta i {
        transition: transform .15s ease;
    }
</style>
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0"><i class="fa-solid fa-sitemap me-2"></i>Plan de Cuentas</h4>
                <div class="ms-auto d-flex gap-2">
                    <?php if (tienePermiso('crear_cuenta_contable')): ?>
                        <button class="btn btn-primary btn-sm" onclick="abrirModalNuevo()">
                            <i class="fa-solid fa-plus"></i> Nueva Cuenta
                        </button>
                    <?php endif; ?>
                </div>

            </div>

            <div class="card-body">

                <!-- Filtro -->
                <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                    <input type="text" id="buscarCuenta"
                        class="form-control form-control-sm shadow-sm"
                        placeholder="🔍 Buscar cuenta..."
                        style="max-width:250px">
                </div>

                <!-- Contenedor tipo sub-card -->
                <div class="border rounded-3 overflow-hidden shadow-sm">
                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0" id="tablaCuentas">

                            <thead class="bg-dark text-white">
                                <tr class="text-center">
                                    <th style="width:140px">Código</th>
                                    <th class="text-start">Nombre</th>
                                    <th style="width:110px">Tipo</th>
                                    <th style="width:120px">Naturaleza</th>
                                    <th style="width:70px">Nivel</th>
                                    <th style="width:90px">Movim.</th>
                                    <th style="width:90px">Estado</th>
                                    <th style="width:100px">Acciones</th>
                                </tr>
                            </thead>
                            <?php
                            $padresConHijos = [];

                            foreach ($cuentas as $cuentaTmp) {
                                if (!empty($cuentaTmp->cuenta_padre_id)) {
                                    $padresConHijos[$cuentaTmp->cuenta_padre_id] = true;
                                }
                            }
                            ?>
                            <tbody>
                                <?php foreach ($cuentas as $c): ?>
                                    <tr class="cuenta-row"
                                        data-id="<?= $c->id ?>"
                                        data-padre="<?= esc($c->cuenta_padre_id ?? '') ?>"
                                        data-nivel="<?= esc($c->nivel) ?>"
                                        data-buscar="<?= strtolower($c->codigo . ' ' . $c->nombre) ?>"
                                        style="<?= !empty($c->cuenta_padre_id) ? 'display:none;' : '' ?>">

                                        <td>
                                            <div class="d-flex align-items-center" style="padding-left:<?= ($c->nivel - 1) * 18 ?>px">

                                                <?php if (isset($padresConHijos[$c->id])): ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-light border mr-1 toggle-cuenta"
                                                        data-id="<?= $c->id ?>">
                                                        <i class="fa-solid fa-chevron-right"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span style="width:34px; display:inline-block;"></span>
                                                <?php endif; ?>

                                                <code class="fw-semibold text-primary">
                                                    <?= esc($c->codigo) ?>
                                                </code>
                                            </div>
                                        </td>

                                        <td class="<?= $c->nivel <= 2 ? 'fw-bold' : ($c->nivel == 3 ? 'fw-semibold' : '') ?>">
                                            <?= esc($c->nombre) ?>
                                        </td>

                                        <td class="text-center text-white">
                                            <?php
                                            $tipoBadge = [
                                                'ACTIVO' => 'primary',
                                                'PASIVO' => 'danger',
                                                'CAPITAL' => 'warning',
                                                'INGRESO' => 'success',
                                                'COSTO' => 'secondary',
                                                'GASTO' => 'dark'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $tipoBadge[$c->tipo] ?? 'secondary' ?>">
                                                <?= $c->tipo ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?= $c->naturaleza === 'DEUDORA'
                                                                    ? 'bg-info text-white'
                                                                    : 'bg-light text-dark border' ?>">
                                                <?= $c->naturaleza ?>
                                            </span>
                                        </td>

                                        <td class="text-center text-muted small">
                                            <?= $c->nivel ?>
                                        </td>

                                        <td class="text-center">
                                            <?= $c->acepta_movimientos
                                                ? '<span class="badge bg-success"><i class="fa-solid fa-check"></i></span>'
                                                : '<span class="badge bg-light text-muted border">No</span>' ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge <?= $c->activo ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $c->activo ? 'Activa' : 'Inactiva' ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <div class="d-flex justify-content-between gap-1">
                                                <?php if (tienePermiso('editar_cuenta_contable')): ?>
                                                    <button class="btn btn-sm btn-outline-warning" onclick="editarCuenta(<?= $c->id ?>)">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if (tienePermiso('eliminar_cuenta_contable')): ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarCuenta(<?= $c->id ?>, '<?= esc($c->nombre) ?>')">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR/EDITAR -->
<div class="modal fade" id="modalCuenta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCuentaTitulo">Nueva Cuenta</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cuentaId">
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Código <span class="text-danger">*</span></label>
                        <input type="text" id="cCodigo" class="form-control" placeholder="Ej: 1.1.1.04">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="cNombre" class="form-control" placeholder="Nombre de la cuenta">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tipo <span class="text-danger">*</span></label>
                        <select id="cTipo" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="PASIVO">PASIVO</option>
                            <option value="CAPITAL">CAPITAL</option>
                            <option value="INGRESO">INGRESO</option>
                            <option value="COSTO">COSTO</option>
                            <option value="GASTO">GASTO</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Naturaleza <span class="text-danger">*</span></label>
                        <select id="cNaturaleza" class="form-select">
                            <option value="DEUDORA">DEUDORA</option>
                            <option value="ACREEDORA">ACREEDORA</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nivel</label>
                        <input type="number" id="cNivel" class="form-control" min="1" max="5" value="4">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Cuenta Padre</label>
                        <select id="cPadre" class="form-select"></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Acepta Movimientos</label>
                        <select id="cAceptaMovimientos" class="form-select">
                            <option value="1">Sí</option>
                            <option value="0">No (Grupo)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select id="cActivo" class="form-select">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>
                <div id="erroresCuenta" class="alert alert-danger mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCuenta()"><i class="fa-solid fa-save me-1"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    const cuentas = <?= json_encode(array_map(fn($c) => ['id' => $c->id, 'text' => $c->codigo . ' - ' . $c->nombre], $cuentas)) ?>;

    function abrirModalNuevo() {
        $('#modalCuentaTitulo').text('Nueva Cuenta');
        $('#cuentaId').val('');
        $('#cCodigo').val('').prop('disabled', false);
        $('#cNombre,#cNivel').val('');
        $('#cNivel').val(4);
        $('#cTipo').val('');
        $('#cNaturaleza').val('DEUDORA');
        $('#cAceptaMovimientos').val('1');
        $('#cActivo').val('1');
        $('#erroresCuenta').addClass('d-none').html('');
        cargarSelectPadre(null);
        $('#modalCuenta').modal('show');
    }

    function cargarSelectPadre(selectedId) {
        let opts = '<option value="">Sin padre (raíz)</option>';
        cuentas.forEach(c => {
            opts += `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.text}</option>`;
        });
        $('#cPadre').html(opts);
    }

    function editarCuenta(id) {
        fetch(`<?= base_url('contabilidad/plan-cuentas/get/') ?>${id}`)
            .then(r => r.json()).then(data => {
                const c = data.cuenta;

                $('#modalCuentaTitulo').text('Editar Cuenta');
                $('#cuentaId').val(c.id);
                $('#cCodigo').val(c.codigo).prop('disabled', true);
                $('#cNombre').val(c.nombre);
                $('#cTipo').val(c.tipo);
                $('#cNaturaleza').val(c.naturaleza);
                $('#cNivel').val(c.nivel);
                $('#cAceptaMovimientos').val(c.acepta_movimientos);
                $('#cActivo').val(c.activo);
                $('#erroresCuenta').addClass('d-none').html('');

                cargarSelectPadre(c.cuenta_padre_id);

                $('#modalCuenta').modal('show');
            });
    }

    function guardarCuenta() {
        const id = $('#cuentaId').val();
        const url = id ? `<?= base_url('contabilidad/plan-cuentas/update/') ?>${id}` : '<?= base_url('contabilidad/plan-cuentas/store') ?>';
        const method = id ? 'POST' : 'POST';

        const form = new FormData();
        if (!id) form.append('codigo', $('#cCodigo').val());
        form.append('nombre', $('#cNombre').val());
        form.append('tipo', $('#cTipo').val());
        form.append('naturaleza', $('#cNaturaleza').val());
        form.append('nivel', $('#cNivel').val());
        form.append('cuenta_padre_id', $('#cPadre').val());
        form.append('acepta_movimientos', $('#cAceptaMovimientos').val());
        form.append('activo', $('#cActivo').val());

        fetch(url, {
                method,
                body: form
            })
            .then(r => r.json()).then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.message, 'success').then(() => location.reload());
                } else {
                    const msgs = data.errors ? Object.values(data.errors).join('<br>') : data.message;
                    $('#erroresCuenta').removeClass('d-none').html(msgs);
                }
            });
    }

    function eliminarCuenta(id, nombre) {
        Swal.fire({
            title: '¿Eliminar cuenta?',
            html: `<strong>${nombre}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar'
        }).then(r => {
            if (!r.isConfirmed) return;
            const form = new FormData();
            form.append('id', id);
            fetch('<?= base_url('contabilidad/plan-cuentas/delete') ?>', {
                    method: 'POST',
                    body: form
                })
                .then(r => r.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Eliminado', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        });
    }

    // Búsqueda en tabla
    function ocultarDescendientes(parentId) {
        document.querySelectorAll(`.cuenta-row[data-padre="${parentId}"]`).forEach(hijo => {
            hijo.style.display = 'none';
            hijo.classList.remove('is-open');

            const btn = hijo.querySelector('.toggle-cuenta i');
            if (btn) {
                btn.classList.remove('fa-chevron-down');
                btn.classList.add('fa-chevron-right');
            }

            ocultarDescendientes(hijo.dataset.id);
        });
    }

    function mostrarHijos(parentId) {
        document.querySelectorAll(`.cuenta-row[data-padre="${parentId}"]`).forEach(hijo => {
            hijo.style.display = '';
        });
    }

    document.querySelectorAll('.toggle-cuenta').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const row = document.querySelector(`.cuenta-row[data-id="${id}"]`);
            const icon = this.querySelector('i');

            const abierto = row.classList.contains('is-open');

            if (abierto) {
                row.classList.remove('is-open');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
                ocultarDescendientes(id);
            } else {
                row.classList.add('is-open');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
                mostrarHijos(id);
            }
        });
    });

    // Búsqueda en tabla
    document.getElementById('buscarCuenta').addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();

        document.querySelectorAll('.cuenta-row').forEach(r => {
            r.classList.remove('is-open');
            r.style.display = 'none';

            const icon = r.querySelector('.toggle-cuenta i');
            if (icon) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        });

        if (q === '') {
            document.querySelectorAll('.cuenta-row').forEach(r => {
                if (!r.dataset.padre) {
                    r.style.display = '';
                }
            });
            return;
        }

        document.querySelectorAll('.cuenta-row').forEach(r => {
            if (r.dataset.buscar.includes(q)) {
                r.style.display = '';
                mostrarPadres(r);
            }
        });
    });

    function mostrarPadres(row) {
        const padreId = row.dataset.padre;

        if (!padreId) return;

        const padre = document.querySelector(`.cuenta-row[data-id="${padreId}"]`);

        if (padre) {
            padre.style.display = '';
            padre.classList.add('is-open');

            const icon = padre.querySelector('.toggle-cuenta i');
            if (icon) {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            }

            mostrarPadres(padre);
        }
    }
</script>

<?= $this->endSection() ?>