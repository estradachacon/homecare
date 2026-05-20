<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: .75rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    /* Tarjeta de producto */
    .producto-card {
        border: 1px solid #dee2e6;
        border-radius: .4rem;
        margin-bottom: .75rem;
        overflow: hidden;
    }
    .producto-card .card-header-prod {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: .4rem .75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: .82rem;
        font-weight: 600;
        color: #495057;
    }
    .producto-card .card-body-prod { padding: .75rem; }

    /* Panel lotes */
    .lotes-panel-area {
        border-top: 2px solid #17a2b8;
        background: #f0f9fb;
        padding: .75rem;
    }
    .lote-row {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: .35rem;
        padding: .4rem .5rem;
        margin-bottom: .35rem;
    }
    .lote-row-nuevo { border: 1px dashed #0d6efd; background: #f8f9ff; }
    .badge-lotes-ok   { background: #28a745 !important; }
    .badge-lotes-warn { background: #ffc107 !important; color: #212529 !important; }
    .badge-lotes-none { background: #6c757d !important; }
</style>

<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-danger">
            <div class="card-header d-flex justify-content-between align-items-center"
                 style="background:#7b1a1a; color:#fff;">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-bolt mr-1"></i> NE Stock de Emergencia
                </h4>
                <a href="<?= base_url('consignaciones') ?>" class="btn btn-sm btn-light">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    El vendedor retira producto del stock directo. Asigna los lotes aquí — la nota queda
                    <strong>autorizada y aprobada</strong> al guardar.
                </div>

                <form id="formCrearEmergencia" method="POST"
                      action="<?= base_url('consignaciones/guardar-emergencia') ?>">

                    <!-- FILA 1 -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Número</label>
                            <input type="text" name="numero" class="form-control"
                                   value="<?= esc($numero_sugerido) ?>" readonly required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted small">Vendedor / Representante <span class="text-danger">*</span></label>
                            <?php if (!empty($vendedor_id)): ?>
                                <input type="text" id="vendedorNombre" class="form-control" value="<?= esc($vendedor_nombre) ?>" readonly>
                                <input type="hidden" name="vendedor_id" value="<?= esc($vendedor_id) ?>">
                            <?php else: ?>
                                <select name="vendedor_id" id="selectVendedor" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($vendedores as $v): ?>
                                        <option value="<?= $v->id ?>"><?= esc($v->seller) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small">Hora</label>
                            <input type="time" name="hora" class="form-control" value="<?= date('H:i') ?>">
                        </div>
                    </div>

                    <!-- FILA 2 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Paciente</label>
                            <div class="d-flex">
                                <select name="paciente_id" id="selectPaciente" class="form-control flex-grow-1">
                                    <option value=""></option>
                                </select>
                                <button type="button" class="btn btn-success ml-2"
                                        data-toggle="modal" data-target="#modalPaciente">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Doctor</label>
                            <div class="d-flex">
                                <select name="doctor_id" id="selectDoctor" class="form-control flex-grow-1">
                                    <option value=""></option>
                                </select>
                                <button type="button" class="btn btn-success ml-2"
                                        data-toggle="modal" data-target="#modalDoctor">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Tipo de nota</label>
                            <div class="d-flex">
                                <select name="tipo_nota_id" id="selectTipoNota" class="form-control flex-grow-1">
                                    <option value=""></option>
                                </select>
                                <button type="button" class="btn btn-success ml-2" id="btnNuevoTipoNota">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">Opcional: ejemplo "Colocación de terapia", "Cambio 1", "Retiro".</small>
                        </div>
                    </div>

                    <!-- FILA 3 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Cliente a facturar</label>
                            <select name="cliente_id" id="selectCliente" class="form-control">
                                <option value=""></option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Concepto</label>
                            <input type="text" name="concepto" class="form-control" placeholder="Motivo del retiro de stock">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>

                    <hr>

                    <!-- ── PRODUCTOS (cards) ── -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Productos y Lotes</h6>
                        <button type="button" id="btnAgregarProducto" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-plus"></i> Agregar producto
                        </button>
                    </div>

                    <div id="listaProductos">
                        <div id="filaVacia" class="text-center text-muted py-4 border rounded"
                             style="font-size:.85rem;">
                            <i class="fa-solid fa-box-open d-block mb-1" style="font-size:1.5rem;opacity:.35;"></i>
                            Use el botón para agregar productos
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-3">
                        <div class="px-3 py-2 rounded" style="background:#eef2ff; font-size:.9rem;">
                            Total: <strong class="text-primary" id="totalGeneral">$0.00</strong>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-danger">
                            <i class="fa-solid fa-bolt mr-1"></i> Guardar NE Emergencia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Paciente -->
<div class="modal fade" id="modalPaciente" tabindex="-1">
    <div class="modal-dialog">
        <form id="formPaciente">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Paciente</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="pacienteNombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Identificación</label>
                        <input type="text" name="identificacion" class="form-control" placeholder="DUI, pasaporte, etc.">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="correo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Foto / documento</label>
                        <input type="file" name="foto" id="pacienteFoto" accept="image/*" capture="environment" class="form-control">
                        <small class="form-text text-muted">Toca el ícono de cámara para tomar una foto desde el celular.</small>
                        <div id="pacienteFotoPreview" class="mt-2 d-none">
                            <img src="" class="img-fluid rounded" style="max-height: 180px;">
                        </div>
                    </div>
                    <div id="pacienteError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarPaciente">
                        <i class="fa-solid fa-save"></i> Guardar paciente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDoctor" tabindex="-1">
    <div class="modal-dialog">
        <form id="formDoctor">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Doctor</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre1" id="doctorNombre1" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>2do Nombre</label>
                            <input type="text" name="nombre2" id="doctorNombre2" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Apellido <span class="text-danger">*</span></label>
                            <input type="text" name="apellido1" id="doctorApellido1" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>2do Apellido</label>
                            <input type="text" name="apellido2" id="doctorApellido2" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Especialidad</label>
                        <input type="text" name="especialidad" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="correo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Foto / documento</label>
                        <input type="file" name="foto" id="doctorFoto" accept="image/*" capture="environment" class="form-control">
                        <small class="form-text text-muted">Toca el ícono de cámara para tomar una foto desde el celular.</small>
                        <div id="doctorFotoPreview" class="mt-2 d-none">
                            <img src="" class="img-fluid rounded" style="max-height: 180px;">
                        </div>
                    </div>
                    <div id="doctorError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarDoctor">
                        <i class="fa-solid fa-save"></i> Guardar doctor
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tipo de Nota -->
<div class="modal fade" id="tipoNotaModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="tipoNotaForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Tipo de Nota</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div id="tipoNotaError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="tipoNotaGuardar">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let filaIdx   = 0;
let numProd   = 0;
const lotesCtrl = {};

function getVendedorId() {
    const sel = document.getElementById('selectVendedor');
    if (sel) return sel.value;
    return (document.querySelector('input[name="vendedor_id"]') || {}).value || '';
}

function calcularSubtotal(card) {
    const cant   = parseFloat(card.querySelector('.input-cantidad').value) || 0;
    const precio = parseFloat(card.querySelector('.input-precio').value)   || 0;
    const sub    = cant * precio;
    card.querySelector('.input-subtotal').value       = sub.toFixed(2);
    card.querySelector('.subtotal-texto').textContent = '$' + sub.toFixed(2);
    recalcularTotal();
}

function recalcularTotal() {
    let t = 0;
    document.querySelectorAll('.input-subtotal').forEach(i => t += parseFloat(i.value) || 0);
    document.getElementById('totalGeneral').textContent = '$' + t.toFixed(2);
}

/* ── lotes helpers ── */
function getCard(idx)      { return document.querySelector(`.producto-card[data-idx="${idx}"]`); }
function getLotesArea(idx) { return document.querySelector(`.lotes-panel-area[data-idx="${idx}"]`); }
function getLotesBody(idx) { const a = getLotesArea(idx); return a ? a.querySelector('.lotes-body') : null; }

function actualizarResumenLotes(idx) {
    const card = getCard(idx);
    const area = getLotesArea(idx);
    if (!card || !area) return;

    const cantReq = parseFloat(card.querySelector('.input-cantidad').value) || 0;
    let asignada  = 0;
    area.querySelectorAll('.input-lote-cant').forEach(i => asignada += parseFloat(i.value) || 0);
    const pendiente = cantReq - asignada;
    const nFilas    = getLotesBody(idx).querySelectorAll('.lote-row').length;

    area.querySelector('.lotes-requerido').textContent = cantReq.toFixed(2);
    area.querySelector('.lotes-asignado').textContent  = asignada.toFixed(2);
    const pEl = area.querySelector('.lotes-pendiente');
    pEl.textContent = pendiente.toFixed(2);
    pEl.className   = 'lotes-pendiente fw-bold ' +
        (pendiente < -0.001 ? 'text-danger' : pendiente > 0.001 ? 'text-warning' : 'text-success');

    const badge = card.querySelector('.lotes-badge');
    badge.textContent = nFilas;
    badge.className   = 'badge lotes-badge ml-1 ' +
        (nFilas === 0 ? 'badge-lotes-none' :
         Math.abs(pendiente) <= 0.001 ? 'badge-lotes-ok' : 'badge-lotes-warn');
}

function initSelectLote(selectEl, idx) {
    $(selectEl).select2({
        language: 'es', placeholder: 'Buscar lote...', allowClear: true, width: '100%',
        ajax: {
            url: '<?= base_url('consignaciones/lotes-por-producto') ?>',
            dataType: 'json', delay: 250,
            data: params => ({
                producto_id: (getLotesArea(idx) || {}).dataset?.productoId || '',
                q: params.term || ''
            }),
            processResults: d => d, cache: true,
        }
    });
}

function agregarLoteExistente(idx) {
    const body    = getLotesBody(idx);
    const loteIdx = lotesCtrl[idx]++;
    body.querySelector('.sin-lotes')?.remove();

    const div = document.createElement('div');
    div.className       = 'lote-row';
    div.dataset.loteIdx = loteIdx;
    div.innerHTML = `
        <select name="productos[${idx}][lotes][${loteIdx}][lote_id]"
                class="form-control form-control-sm w-100 mb-1"></select>
        <div class="d-flex align-items-center">
            <input type="number" name="productos[${idx}][lotes][${loteIdx}][cantidad]"
                   class="form-control form-control-sm input-lote-cant flex-grow-1"
                   min="0.001" step="0.001" placeholder="Cantidad">
            <button type="button" class="btn btn-danger btn-xs btn-del-lote ml-1" title="Quitar">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>`;
    body.appendChild(div);

    initSelectLote(div.querySelector('select'), idx);
    div.querySelector('.input-lote-cant').addEventListener('input', () => actualizarResumenLotes(idx));
    div.querySelector('.btn-del-lote').addEventListener('click', () => {
        div.remove();
        if (!getLotesBody(idx).querySelector('.lote-row'))
            getLotesBody(idx).innerHTML = '<p class="text-muted small mb-0 sin-lotes"><em>Sin lotes. Agrega al menos uno.</em></p>';
        actualizarResumenLotes(idx);
    });
    actualizarResumenLotes(idx);
}

function agregarLoteNuevo(idx) {
    const body    = getLotesBody(idx);
    const loteIdx = lotesCtrl[idx]++;
    body.querySelector('.sin-lotes')?.remove();

    const div = document.createElement('div');
    div.className       = 'lote-row lote-row-nuevo';
    div.dataset.loteIdx = loteIdx;
    div.innerHTML = `
        <input type="hidden" name="productos[${idx}][lotes][${loteIdx}][nuevo]" value="1">
        <div class="d-flex align-items-center mb-1">
            <span class="badge badge-primary mr-2" style="font-size:.68rem;">Nuevo</span>
            <input type="text" name="productos[${idx}][lotes][${loteIdx}][numero_lote]"
                   class="form-control form-control-sm flex-grow-1" placeholder="Nro. Lote *">
        </div>
        <div class="form-row mb-1">
            <div class="col-6">
                <input type="date" name="productos[${idx}][lotes][${loteIdx}][vencimiento]"
                       class="form-control form-control-sm" title="Vencimiento">
                <small class="text-muted" style="font-size:.7rem;">Vencimiento</small>
            </div>
            <div class="col-6">
                <input type="date" name="productos[${idx}][lotes][${loteIdx}][manufactura]"
                       class="form-control form-control-sm" title="Manufactura">
                <small class="text-muted" style="font-size:.7rem;">Manufactura</small>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <input type="number" name="productos[${idx}][lotes][${loteIdx}][cantidad]"
                   class="form-control form-control-sm input-lote-cant flex-grow-1"
                   min="0.001" step="0.001" placeholder="Cantidad">
            <button type="button" class="btn btn-danger btn-xs btn-del-lote ml-1" title="Quitar">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>`;
    body.appendChild(div);

    div.querySelector('.input-lote-cant').addEventListener('input', () => actualizarResumenLotes(idx));
    div.querySelector('.btn-del-lote').addEventListener('click', () => {
        div.remove();
        if (!getLotesBody(idx).querySelector('.lote-row'))
            getLotesBody(idx).innerHTML = '<p class="text-muted small mb-0 sin-lotes"><em>Sin lotes. Agrega al menos uno.</em></p>';
        actualizarResumenLotes(idx);
    });
    actualizarResumenLotes(idx);
}

function initSelectProducto(select, idx) {
    $(select).select2({
        language: 'es', placeholder: 'Buscar producto...', allowClear: true, width: '100%',
        ajax: {
            url: '<?= base_url('productos/searchAjax') ?>',
            dataType: 'json', delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data.results }), cache: true,
        }
    }).on('select2:select', function(e) {
        const productoId = e.params.data.id;
        const area       = getLotesArea(idx);
        if (area) area.dataset.productoId = productoId;

        const vendedorId = getVendedorId();
        if (!vendedorId || !productoId) return;

        fetch(`<?= base_url('consignaciones/precio-ajax') ?>?vendedor_id=${vendedorId}&producto_id=${productoId}`)
            .then(r => r.json())
            .then(data => {
                if (data.precio !== null) {
                    const card = getCard(idx);
                    card.querySelector('.input-precio').value = parseFloat(data.precio).toFixed(2);
                    calcularSubtotal(card);
                }
            });
    }).on('select2:clear', function() {
        const area = getLotesArea(idx);
        if (area) area.dataset.productoId = '';
    });
}

document.getElementById('btnAgregarProducto').addEventListener('click', function() {
    const idx = filaIdx++;
    lotesCtrl[idx] = 0;
    numProd++;

    document.getElementById('filaVacia')?.remove();

    const card = document.createElement('div');
    card.className   = 'producto-card';
    card.dataset.idx = idx;
    card.innerHTML = `
        <div class="card-header-prod">
            <span><i class="fa-solid fa-box mr-1 text-muted"></i>Producto #${numProd}</span>
            <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
        <div class="card-body-prod">
            <div class="mb-2">
                <select name="productos[${idx}][producto_id]"
                        class="form-control select-producto" required></select>
            </div>
            <div class="row no-gutters mb-2">
                <div class="col-4 pr-1">
                    <label class="text-muted mb-0" style="font-size:.75rem;">Cantidad *</label>
                    <input type="number" name="productos[${idx}][cantidad]"
                           class="form-control form-control-sm input-cantidad"
                           min="0.01" step="0.01" value="1" required>
                </div>
                <div class="col-4 pr-1">
                    <label class="text-muted mb-0" style="font-size:.75rem;">Precio *</label>
                    <input type="number" name="productos[${idx}][precio_unitario]"
                           class="form-control form-control-sm input-precio"
                           min="0" step="0.01" value="0.00" required>
                </div>
                <div class="col-4">
                    <label class="text-muted mb-0" style="font-size:.75rem;">Subtotal</label>
                    <div class="form-control form-control-sm d-flex align-items-center justify-content-end"
                         style="background:#f0f4f8; font-weight:700; color:#1a56db;">
                        <input type="hidden" name="productos[${idx}][subtotal]" class="input-subtotal" value="0">
                        <span class="subtotal-texto">$0.00</span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-info btn-sm btn-block btn-toggle-lotes">
                <i class="fa-solid fa-vials mr-1"></i>Lotes
                <span class="badge badge-secondary lotes-badge ml-1">0</span>
            </button>
        </div>
        <div class="lotes-panel-area d-none" data-idx="${idx}" data-producto-id="">
            <div class="d-flex flex-wrap gap-2 mb-2" style="font-size:.82rem;">
                <span class="text-muted">Requerido: <strong class="lotes-requerido">0.00</strong></span>
                <span class="ml-3">Asignado: <strong class="lotes-asignado text-success">0.00</strong></span>
                <span class="ml-3">Pendiente: <strong class="lotes-pendiente text-danger">0.00</strong></span>
            </div>
            <div class="btn-group btn-group-sm mb-2">
                <button type="button" class="btn btn-outline-success btn-lote-existente">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i>Existente
                </button>
                <button type="button" class="btn btn-outline-primary btn-lote-nuevo">
                    <i class="fa-solid fa-plus mr-1"></i>Nuevo lote
                </button>
            </div>
            <div class="lotes-body">
                <p class="text-muted small mb-0 sin-lotes"><em>Sin lotes. Agrega al menos uno.</em></p>
            </div>
        </div>`;

    document.getElementById('listaProductos').appendChild(card);

    initSelectProducto(card.querySelector('.select-producto'), idx);

    card.querySelector('.input-cantidad').addEventListener('input', () => { calcularSubtotal(card); actualizarResumenLotes(idx); });
    card.querySelector('.input-precio').addEventListener('input',   () => calcularSubtotal(card));

    card.querySelector('.btn-toggle-lotes').addEventListener('click', () => {
        const area = getLotesArea(idx);
        area.classList.toggle('d-none');
        if (!area.classList.contains('d-none')) actualizarResumenLotes(idx);
    });

    card.querySelector('.btn-lote-existente').addEventListener('click', () => agregarLoteExistente(idx));
    card.querySelector('.btn-lote-nuevo').addEventListener('click',     () => agregarLoteNuevo(idx));

    card.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
        card.remove();
        delete lotesCtrl[idx];
        numProd--;
        recalcularTotal();
        if (!document.querySelector('.producto-card')) {
            numProd = 0;
            const div = document.createElement('div');
            div.id = 'filaVacia';
            div.className = 'text-center text-muted py-4 border rounded';
            div.style.fontSize = '.85rem';
            div.innerHTML = '<i class="fa-solid fa-box-open d-block mb-1" style="font-size:1.5rem;opacity:.35;"></i>Use el botón para agregar productos';
            document.getElementById('listaProductos').appendChild(div);
        }
    });
});

/* ── Select2 cabecera ── */
$(function() {
    $('#selectPaciente').select2({
        language: 'es', placeholder: 'Buscar paciente...', allowClear: true, width: '100%',
        ajax: { url: '<?= base_url('pacientes/searchAjax') ?>', dataType: 'json', delay: 250,
            data: p => ({ q: p.term || '' }),
            processResults: d => ({ results: d.results }), cache: true }
    });

    $('#formPaciente').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn  = $('#btnGuardarPaciente'), err = $('#pacienteError');
        err.addClass('d-none').text('');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');
        $.ajax({
            url: '<?= base_url('pacientes/storeAjax') ?>', type: 'POST',
            data: new FormData(form), dataType: 'json', processData: false, contentType: false,
            success(res) {
                if (!res.success) { err.removeClass('d-none').text(res.message || 'No se pudo crear el paciente.'); return; }
                $('#selectPaciente').append(new Option(res.paciente.text, res.paciente.id, true, true)).trigger('change');
                $('#modalPaciente').modal('hide');
                form.reset();
                $('#pacienteFotoPreview').addClass('d-none').find('img').attr('src', '');
            },
            error() { err.removeClass('d-none').text('Error al comunicarse con el servidor.'); },
            complete() { btn.prop('disabled', false).html('<i class="fa-solid fa-save"></i> Guardar paciente'); }
        });
    });

    $('#selectDoctor').select2({
        language: 'es', placeholder: 'Buscar doctor...', allowClear: true, width: '100%',
        ajax: { url: '<?= base_url('doctores/searchAjax') ?>', dataType: 'json', delay: 250,
            data: p => ({ q: p.term || '' }),
            processResults: d => ({ results: d.results }), cache: true }
    });

    $('#formDoctor').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn  = $('#btnGuardarDoctor'), err = $('#doctorError');
        err.addClass('d-none').text('');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');
        $.ajax({
            url: '<?= base_url('doctores/storeAjax') ?>', type: 'POST',
            data: new FormData(form), dataType: 'json', processData: false, contentType: false,
            success(res) {
                if (!res.success) { err.removeClass('d-none').text(res.message || 'No se pudo crear el doctor.'); return; }
                $('#selectDoctor').append(new Option(res.doctor.text, res.doctor.id, true, true)).trigger('change');
                $('#modalDoctor').modal('hide');
                form.reset();
                $('#doctorFotoPreview').addClass('d-none').find('img').attr('src', '');
            },
            error() { err.removeClass('d-none').text('Error al comunicarse con el servidor.'); },
            complete() { btn.prop('disabled', false).html('<i class="fa-solid fa-save"></i> Guardar doctor'); }
        });
    });

    function initSelectTipoNota() {
        if (typeof $.fn.select2 === 'undefined') return;
        if ($('#selectTipoNota').data('select2')) return;
        $('#selectTipoNota').select2({
            language: 'es', placeholder: 'Seleccione tipo de nota...', allowClear: true, width: '100%',
            ajax: { url: '<?= base_url('tipo-notas/searchAjax') ?>', dataType: 'json', delay: 250,
                data: p => ({ q: p.term || '' }),
                processResults: d => ({ results: d.results }), cache: true }
        });
    }
    initSelectTipoNota();

    $('#btnNuevoTipoNota').on('click', function() {
        $('#tipoNotaModal').modal('show');
        $('#tipoNotaForm')[0].reset();
        $('#tipoNotaError').addClass('d-none').text('');
    });

    $('#tipoNotaForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#tipoNotaGuardar'), err = $('#tipoNotaError');
        err.addClass('d-none').text(''); btn.prop('disabled', true).text('Guardando...');
        $.ajax({
            url: '<?= base_url('tipo-notas/storeAjax') ?>', method: 'POST',
            data: $(this).serialize(), dataType: 'json',
            success(res) {
                if (!res.success) { err.removeClass('d-none').text(res.message || 'Error'); return; }
                $('#selectTipoNota').append(new Option(res.tipo_nota.text, res.tipo_nota.id, true, true)).trigger('change');
                $('#tipoNotaModal').modal('hide');
            },
            error() { err.removeClass('d-none').text('Error de conexión.'); },
            complete() { btn.prop('disabled', false).text('Guardar'); }
        });
    });

    $('#selectCliente').select2({
        language: 'es', placeholder: 'Buscar cliente...', allowClear: true, width: '100%',
        ajax: { url: '<?= base_url('clientes/searchAjax') ?>', dataType: 'json', delay: 250,
            data: p => ({ q: p.term || '' }),
            processResults: d => ({ results: d.results }), cache: true }
    });

    function previewImage(input, previewSelector) {
        const file = input.files && input.files[0];
        const preview = $(previewSelector);
        if (!file) { preview.addClass('d-none').find('img').attr('src', ''); return; }
        const reader = new FileReader();
        reader.onload = e => preview.removeClass('d-none').find('img').attr('src', e.target.result);
        reader.readAsDataURL(file);
    }
    $('#pacienteFoto').on('change', function() { previewImage(this, '#pacienteFotoPreview'); });
    $('#doctorFoto').on('change',   function() { previewImage(this, '#doctorFotoPreview'); });
});

/* ── Validación y submit ── */
document.getElementById('formCrearEmergencia').addEventListener('submit', function(e) {
    e.preventDefault();

    const cards = document.querySelectorAll('.producto-card');
    if (!cards.length) {
        Swal.fire('Sin productos', 'Debe agregar al menos un producto.', 'warning');
        return;
    }

    const errores = [];
    cards.forEach(card => {
        const idx     = card.dataset.idx;
        const cantReq = parseFloat(card.querySelector('.input-cantidad').value) || 0;
        const texto   = ($(card.querySelector('.select-producto')).find(':selected').text() || '').trim()
                      || `Producto #${parseInt(idx) + 1}`;
        const body    = getLotesBody(idx);
        const rows    = body?.querySelectorAll('.lote-row') ?? [];

        if (!rows.length) {
            errores.push(`<b>${texto}</b>: sin lotes asignados.`);
            return;
        }

        let asignada = 0;
        getLotesArea(idx).querySelectorAll('.input-lote-cant').forEach(i => asignada += parseFloat(i.value) || 0);
        if (Math.abs(asignada - cantReq) > 0.001)
            errores.push(`<b>${texto}</b>: lotes (${asignada.toFixed(3)}) ≠ cantidad (${cantReq.toFixed(3)}).`);

        rows.forEach(lr => {
            const nro = lr.querySelector('input[name*="[numero_lote]"]');
            if (nro && !nro.value.trim())
                errores.push(`<b>${texto}</b>: un lote nuevo no tiene número de lote.`);
        });
    });

    if (errores.length) {
        Swal.fire({
            title: 'Corrija los lotes',
            html: errores.map(e => `<div class="text-left small">• ${e}</div>`).join(''),
            icon: 'error',
        });
        return;
    }

    const numero = document.querySelector('[name="numero"]').value;
    const total  = document.getElementById('totalGeneral').textContent;

    Swal.fire({
        title: 'Confirmar NE Emergencia',
        html: `<b>Número:</b> ${numero}<br><b>Total:</b> ${total}<br><br>
               <small class="text-muted">La nota quedará <strong>autorizada y aprobada</strong>,
               lista para levantar NP.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#7b1a1a',
    }).then(r => { if (r.isConfirmed) document.getElementById('formCrearEmergencia').submit(); });
});
</script>

<?= $this->endSection() ?>
