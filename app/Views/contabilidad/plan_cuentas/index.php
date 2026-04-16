<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0"><i class="fa-solid fa-sitemap me-2"></i>Plan de Cuentas</h4>
                <div class="ms-auto d-flex gap-2">
                    <?php if (tienePermiso('crear_cuenta_contable')): ?>
                    <button class="btn btn-primary btn-sm" onclick="abrirModalNuevo()">
                        <i class="fa-solid fa-plus"></i> Nueva Cuenta
                    </button>
                    <?php endif; ?>
                    <input type="text" id="buscarCuenta" class="form-control form-control-sm" placeholder="Buscar..." style="width:200px">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="tablaCuentas">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:140px">Código</th>
                                <th>Nombre</th>
                                <th class="text-center" style="width:100px">Tipo</th>
                                <th class="text-center" style="width:100px">Naturaleza</th>
                                <th class="text-center" style="width:70px">Nivel</th>
                                <th class="text-center" style="width:90px">Movim.</th>
                                <th class="text-center" style="width:70px">Estado</th>
                                <th class="text-center" style="width:90px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuentas as $c): ?>
                            <tr class="cuenta-row" data-buscar="<?= strtolower($c->codigo . ' ' . $c->nombre) ?>">
                                <td>
                                    <code class="fw-bold" style="padding-left:<?= ($c->nivel - 1) * 16 ?>px">
                                        <?= esc($c->codigo) ?>
                                    </code>
                                </td>
                                <td class="<?= $c->nivel <= 2 ? 'fw-bold' : ($c->nivel == 3 ? 'fw-semibold' : '') ?>">
                                    <?= esc($c->nombre) ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $tipoBadge = ['ACTIVO'=>'primary','PASIVO'=>'danger','CAPITAL'=>'warning','INGRESO'=>'success','COSTO'=>'secondary','GASTO'=>'dark'];
                                    ?>
                                    <span class="badge bg-<?= $tipoBadge[$c->tipo] ?? 'secondary' ?>" style="font-size:0.68rem"><?= $c->tipo ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $c->naturaleza === 'DEUDORA' ? 'bg-info text-dark' : 'bg-light text-dark border' ?>" style="font-size:0.68rem"><?= $c->naturaleza ?></span>
                                </td>
                                <td class="text-center text-muted small"><?= $c->nivel ?></td>
                                <td class="text-center">
                                    <?php if ($c->acepta_movimientos): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $c->activo ? 'bg-success' : 'bg-secondary' ?>"><?= $c->activo ? 'Activa' : 'Inactiva' ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (tienePermiso('editar_cuenta_contable')): ?>
                                    <button class="btn btn-sm btn-outline-warning py-0 px-1" onclick="editarCuenta(<?= $c->id ?>)" title="Editar">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if (tienePermiso('eliminar_cuenta_contable')): ?>
                                    <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="eliminarCuenta(<?= $c->id ?>, '<?= esc($c->nombre) ?>')" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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

<!-- MODAL CREAR/EDITAR -->
<div class="modal fade" id="modalCuenta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCuentaTitulo">Nueva Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCuenta()"><i class="fa-solid fa-save me-1"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
const cuentas = <?= json_encode(array_map(fn($c) => ['id'=>$c->id,'text'=>$c->codigo.' - '.$c->nombre], $cuentas)) ?>;

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
    new bootstrap.Modal(document.getElementById('modalCuenta')).show();
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
            new bootstrap.Modal(document.getElementById('modalCuenta')).show();
        });
}

function guardarCuenta() {
    const id     = $('#cuentaId').val();
    const url    = id ? `<?= base_url('contabilidad/plan-cuentas/update/') ?>${id}` : '<?= base_url('contabilidad/plan-cuentas/store') ?>';
    const method = id ? 'POST' : 'POST';

    const form = new FormData();
    if (!id) form.append('codigo', $('#cCodigo').val());
    form.append('nombre',             $('#cNombre').val());
    form.append('tipo',               $('#cTipo').val());
    form.append('naturaleza',         $('#cNaturaleza').val());
    form.append('nivel',              $('#cNivel').val());
    form.append('cuenta_padre_id',    $('#cPadre').val());
    form.append('acepta_movimientos', $('#cAceptaMovimientos').val());
    form.append('activo',             $('#cActivo').val());

    fetch(url, { method, body: form })
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
        const form = new FormData(); form.append('id', id);
        fetch('<?= base_url('contabilidad/plan-cuentas/delete') ?>', { method: 'POST', body: form })
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
document.getElementById('buscarCuenta').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.cuenta-row').forEach(r => {
        r.style.display = r.dataset.buscar.includes(q) ? '' : 'none';
    });
});
</script>

<?= $this->endSection() ?>
