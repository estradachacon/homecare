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
    #tablaProductos td { vertical-align: middle; }
    .box-totales { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 16px; }
    .box-totales .row-total { font-size: 1.1rem; font-weight: 700; }
    .alerta-doc { font-size: 0.88rem; }

    /* ── Chip del producto seleccionado ──────────────────────────────────── */
    .prod-chip {
        display: flex; align-items: flex-start; gap: .4rem;
        background: #f0f7ff; border: 1px solid #b6d4fe;
        border-radius: .375rem; padding: .3rem .6rem;
        font-size: .875rem; width: 100%; max-width: 100%; box-sizing: border-box;
        overflow: hidden;
    }
    .prod-chip-nombre { flex: 1; min-width: 0; max-width: 100%; overflow-wrap: anywhere; word-break: break-word; line-height: 1.4; }
    .prod-chip-clear { border: none; background: transparent; color: #9ca3af; cursor: pointer; padding: 0 0 0 .3rem; line-height: 1.4; flex-shrink: 0; font-size: .8rem; }
    .prod-chip-clear:hover { color: #dc3545; }

    @media (max-width: 767px) {
        .card, .card-body, #formCrear { min-width: 0; }
        .table-responsive { display: block; width: 100%; max-width: 100%; overflow-x: hidden; }
        #tablaProductos,
        #tablaProductos tbody {
            display: block; width: 100% !important; max-width: 100%;
            min-width: 0 !important; table-layout: fixed;
        }
        #tablaProductos thead { display: none; }
        #tablaProductos th,
        #tablaProductos td {
            width: auto !important; max-width: 100% !important; min-width: 0 !important;
            white-space: normal !important;
        }
        #tablaProductos tbody tr.fila-producto {
            display: block; border: 1px solid #dee2e6;
            border-radius: .5rem; margin-bottom: .75rem; padding: .5rem .75rem;
            width: 100%; max-width: 100%; box-sizing: border-box; overflow: hidden;
        }
        #tablaProductos tbody tr.fila-producto td {
            display: flex; align-items: center; border: none; padding: .3rem 0; gap: .5rem;
            width: 100%; max-width: 100%; min-width: 0; box-sizing: border-box;
        }
        #tablaProductos tbody tr.fila-producto td::before {
            content: attr(data-label); font-size: .75rem; font-weight: 600;
            color: #6c757d; text-transform: uppercase; flex-shrink: 0; width: 70px;
        }
        #tablaProductos td[data-label="Producto"] { flex-direction: column; align-items: flex-start; gap: .2rem; }
        #tablaProductos td[data-label="Producto"]::before { width: auto; }
        #tablaProductos td[data-label="Producto"],
        #tablaProductos td[data-label="Producto"] .select2-selection--single {
            overflow: hidden;
        }
        #tablaProductos td[data-label="Producto"] .select2-container,
        #tablaProductos td[data-label="Producto"] .prod-chip { width: 100% !important; max-width: 100% !important; min-width: 0 !important; }
        #tablaProductos td[data-label="Producto"] .prod-chip { align-items: flex-start; }
        #tablaProductos td[data-label="Producto"] .prod-chip-nombre {
            display: block; overflow: visible; text-overflow: clip;
            white-space: normal; overflow-wrap: anywhere; word-break: break-word;
        }
        #tablaProductos td[data-label="Producto"] .select2-selection__rendered {
            display: block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        #tablaProductos .input-cantidad,
        #tablaProductos .input-precio { flex: 1; min-width: 0; }
        #tablaProductos td[data-label="Subtotal"] { justify-content: space-between; }
        #tablaProductos .td-eliminar { justify-content: flex-end; border-top: 1px solid #f0f0f0; margin-top: .2rem; padding-top: .4rem; }
        #tablaProductos .td-eliminar::before { display: none; }
        .box-totales { margin-top: 1rem; }
    }
</style>

<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">Nueva Nota de Pedido</h4>
                <a href="<?= base_url('pedidos') ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card-body">
                <form id="formCrear" method="POST" action="<?= base_url('pedidos/guardar') ?>">
                    <?= csrf_field() ?>

                    <!-- FILA 1: Número / Vendedor -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Número</label>
                            <input type="text" class="form-control" value="<?= esc($numero_sugerido) ?>" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small">Vendedor</label>
                            <input type="text" class="form-control" value="<?= esc($vendedor_nombre) ?>" readonly>
                            <input type="hidden" name="vendedor_id" value="<?= $vendedor_id ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small">Cliente <span class="text-danger">*</span></label>
                            <div class="d-flex">
                                <select name="cliente_id" id="selectCliente" class="form-control flex-grow-1" required></select>
                                <?php if (tienePermiso('crear_clientes')): ?>
                                    <button type="button" class="btn btn-success ml-2" data-toggle="modal" data-target="#modalCliente" title="Nuevo cliente">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- FILA 2: Tipo documento / Tipo pago / Días -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Documento final <span class="text-danger">*</span></label>
                            <select name="tipo_documento" id="selectTipoDoc" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <option value="factura">Factura</option>
                                <option value="credito_fiscal">Crédito Fiscal</option>
                                <option value="nota_remision">Nota de Remisión</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Tipo de pago <span class="text-danger">*</span></label>
                            <select name="tipo_pago" id="selectTipoPago" class="form-control" required>
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="wrapDiasCredito" style="display:none;">
                            <label class="form-label text-muted small">Días plazo <span class="text-danger">*</span></label>
                            <select name="dias_credito" id="selectDiasCredito" class="form-control">
                                <option value="15">15 días</option>
                                <option value="30" selected>30 días</option>
                                <option value="45">45 días</option>
                                <option value="60">60 días</option>
                                <option value="90">90 días</option>
                                <option value="120">120 días</option>
                            </select>
                        </div>
                    </div>

                    <!-- Alerta tipo documento -->
                    <div id="alertaDoc" class="alert alerta-doc d-none"></div>

                    <hr>

                    <!-- Tabla de productos -->
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">Productos</h6>
                        <button type="button" id="btnAgregarProducto" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-plus"></i> Agregar producto
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tablaProductos">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width:280px">Producto</th>
                                    <th style="width:100px">Cantidad</th>
                                    <th style="width:140px">Precio Unit.</th>
                                    <th style="width:130px" class="text-end">Subtotal</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoProductos">
                                <tr id="filaVacia">
                                    <td colspan="5" class="text-center text-muted py-3">Use el botón para agregar productos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Box de totales -->
                    <div class="row justify-content-end mt-2">
                        <div class="col-md-4">
                            <div class="box-totales">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Subtotal:</span>
                                    <span id="dispSubtotal">$0.00</span>
                                </div>
                                <div id="rowIva" class="d-flex justify-content-between mb-1 d-none">
                                    <span>IVA (13%):</span>
                                    <span id="dispIva">$0.00</span>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between row-total">
                                    <span>Total:</span>
                                    <span id="dispTotal" class="text-primary">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="row mb-3 mt-3">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Notas</label>
                            <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones del pedido..."></textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save"></i> Guardar Nota de Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template fila producto -->
<template id="tplFila">
    <tr class="fila-producto">
        <td data-label="Producto">
            <select name="productos[IDX][producto_id]" class="form-control form-control-sm select-producto"></select>
            <input type="hidden" name="productos[IDX][precio_minimo]" class="input-precio-minimo" value="0">
            <div class="prod-chip d-none">
                <span class="prod-chip-nombre"></span>
                <button type="button" class="prod-chip-clear" title="Cambiar producto"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </td>
        <td data-label="Cantidad">
            <input type="number" name="productos[IDX][cantidad]" class="form-control form-control-sm input-cantidad"
                min="0.01" step="0.01" value="1" required>
        </td>
        <td data-label="Precio">
            <input type="number" name="productos[IDX][precio_unitario]" class="form-control form-control-sm input-precio"
                min="0" step="0.01" value="0.00" required>
            <small class="text-muted input-precio-hint"></small>
        </td>
        <td data-label="Subtotal">
            <input type="hidden" name="productos[IDX][subtotal]" class="input-subtotal" value="0">
            <span class="subtotal-texto">$0.00</span>
        </td>
        <td class="td-eliminar">
            <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="formCliente">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Tipo Documento <span class="text-danger">*</span></label>
                            <select name="tipo_documento" class="form-control" required>
                                <option value="DUI">DUI</option>
                                <option value="NIT">NIT</option>
                                <option value="PASAPORTE">Pasaporte</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Número Documento</label>
                            <input type="text" name="numero_documento" class="form-control" placeholder="00000000-0">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>NRC</label>
                            <input type="text" name="nrc" class="form-control" placeholder="NRC (si aplica)">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label>Nombre / Razón Social <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Correo</label>
                            <input type="email" name="correo" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Dirección</label>
                            <input type="text" name="direccion" class="form-control">
                        </div>
                    </div>
                    <div id="clienteError" class="alert alert-danger d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarCliente">
                        <i class="fa-solid fa-save"></i> Guardar cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let filaIdx = 0;
const PRECIO_MIN_ALERT = 'No puede ingresar un precio menor al mínimo configurado ($';
function notificarPrecioSuperior(valor) {
    if (typeof Swal === 'undefined') return;
    Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    }).fire({
        icon: 'info',
        title: 'Precio superior al configurado',
        text: 'Se usará el precio ingresado de $' + valor.toFixed(2) + '.',
    });
}

function validarPrecioConfigurado(fila, mostrarAvisoSuperior = true) {
    const min = parseFloat(fila.querySelector('.input-precio-minimo').value) || 0;
    const inp = fila.querySelector('.input-precio');
    const val = parseFloat(inp.value) || 0;

    if (min <= 0) {
        calcularSubtotal(fila);
        return true;
    }

    if (val < min) {
        Swal.fire('Precio no permitido', PRECIO_MIN_ALERT + min.toFixed(2) + ').', 'warning');
        inp.value = min.toFixed(2);
        inp.focus();
        calcularSubtotal(fila);
        return false;
    }

    if (mostrarAvisoSuperior && val > min) {
        notificarPrecioSuperior(val);
    }

    calcularSubtotal(fila);
    return true;
}

// ── Tipo documento → alerta + IVA ────────────────────────────────────────────
function onTipoDocChange() {
    const val    = document.getElementById('selectTipoDoc').value;
    const alerta = document.getElementById('alertaDoc');
    const rowIva = document.getElementById('rowIva');

    alerta.className = 'alert alerta-doc';

    if (val === 'factura') {
        alerta.classList.remove('d-none');
        alerta.classList.add('alert-info');
        alerta.innerHTML = '<i class="fa-solid fa-circle-info mr-1"></i><strong>Factura:</strong> Los precios ingresados deben ser <strong>ya con IVA incluido</strong>.';
        rowIva.classList.add('d-none');
    } else if (val === 'credito_fiscal') {
        alerta.classList.remove('d-none');
        alerta.classList.add('alert-warning');
        alerta.innerHTML = '<i class="fa-solid fa-triangle-exclamation mr-1"></i><strong>Crédito Fiscal:</strong> Los precios deben ser <strong>sin IVA</strong>. El IVA del 13% se calculará automáticamente.';
        rowIva.classList.remove('d-none');
        validarCCFConNRC();
    } else if (val === 'nota_remision') {
        alerta.classList.remove('d-none');
        alerta.classList.add('alert-secondary');
        alerta.innerHTML = '<i class="fa-solid fa-circle-info mr-1"></i><strong>Nota de Remisión:</strong> Los precios deben ser <strong>sin IVA</strong>.';
        rowIva.classList.add('d-none');
    } else {
        alerta.classList.add('d-none');
        rowIva.classList.add('d-none');
    }

    recalcularTotal();
}

document.getElementById('selectTipoDoc').addEventListener('change', onTipoDocChange);

// ── Tipo pago → días de crédito ───────────────────────────────────────────────
document.getElementById('selectTipoPago').addEventListener('change', function () {
    const wrap = document.getElementById('wrapDiasCredito');
    wrap.style.display = this.value === 'credito' ? '' : 'none';
    if (this.value === 'credito') {
        document.getElementById('selectDiasCredito').required = true;
    } else {
        document.getElementById('selectDiasCredito').required = false;
    }
});

// ── Cálculos ─────────────────────────────────────────────────────────────────
function calcularSubtotal(fila) {
    const cant   = parseFloat(fila.querySelector('.input-cantidad').value) || 0;
    const precio = parseFloat(fila.querySelector('.input-precio').value) || 0;
    const sub    = cant * precio;
    fila.querySelector('.input-subtotal').value    = sub.toFixed(2);
    fila.querySelector('.subtotal-texto').textContent = '$' + sub.toFixed(2);
    recalcularTotal();
}

function recalcularTotal() {
    let subtotal = 0;
    document.querySelectorAll('.input-subtotal').forEach(i => subtotal += parseFloat(i.value) || 0);

    const tipoDoc = document.getElementById('selectTipoDoc').value;
    const iva     = (tipoDoc === 'credito_fiscal') ? subtotal * 0.13 : 0;
    const total   = subtotal + iva;

    document.getElementById('dispSubtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('dispIva').textContent      = '$' + iva.toFixed(2);
    document.getElementById('dispTotal').textContent    = '$' + total.toFixed(2);
}

// ── Select2 producto + chip de display ───────────────────────────────────────
function initSelectProducto(select) {
    const td        = select.closest('td');
    const fila      = select.closest('tr');
    const chip      = td.querySelector('.prod-chip');
    const chipNom   = td.querySelector('.prod-chip-nombre');
    const chipClear = td.querySelector('.prod-chip-clear');

    $(select).select2({
        language: 'es',
        placeholder: 'Buscar producto...',
        allowClear: false,
        width: '100%',
        ajax: {
            url: '<?= base_url('productos/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data.results }),
            cache: true,
        },
    }).on('select2:select', function (e) {
        const id     = e.params.data.id;
        const nombre = e.params.data.text;

        chipNom.textContent = nombre;
        chip.classList.remove('d-none');
        $(select).next('.select2-container').hide();

        const clienteId = $('#selectCliente').val() || '';
        fetch(`<?= base_url('pedidos/precio-producto') ?>?producto_id=${id}&cliente_id=${clienteId}`)
            .then(r => r.json())
            .then(data => {
                const min   = parseFloat(data.precio_minimo) || 0;
                const rec   = data.precio_recomendado !== null ? parseFloat(data.precio_recomendado) : null;
                const floor = rec !== null ? Math.max(min, rec) : min;
                const inp   = fila.querySelector('.input-precio');
                const hint  = fila.querySelector('.input-precio-hint');

                fila.querySelector('.input-precio-minimo').value = floor.toFixed(2);
                inp.value = floor > 0 ? floor.toFixed(2) : inp.value;

                if (floor > 0) {
                    inp.min = floor.toFixed(2);
                    hint.textContent = 'Mín: $' + floor.toFixed(2);
                } else {
                    inp.min = '0';
                    hint.textContent = '';
                }
                calcularSubtotal(fila);
            });
    });

    chipClear.addEventListener('click', () => {
        chipNom.textContent = '';
        chip.classList.add('d-none');
        $(select).val(null).trigger('change');
        $(select).next('.select2-container').show();
        fila.querySelector('.input-precio-minimo').value = '0';
        fila.querySelector('.input-precio').min = '0';
        fila.querySelector('.input-precio-hint').textContent = '';
        calcularSubtotal(fila);
    });
}

document.getElementById('btnAgregarProducto').addEventListener('click', function () {
    const tpl  = document.getElementById('tplFila').content.cloneNode(true);
    const fila = tpl.querySelector('tr');
    const idx  = filaIdx++;

    fila.innerHTML = fila.innerHTML.replaceAll('IDX', idx);
    document.getElementById('filaVacia')?.remove();
    document.getElementById('cuerpoProductos').appendChild(fila);

    const filaEl = document.getElementById('cuerpoProductos').lastElementChild;
    initSelectProducto(filaEl.querySelector('.select-producto'));
    filaEl.querySelector('.input-cantidad').addEventListener('input', () => calcularSubtotal(filaEl));

    filaEl.querySelector('.input-precio').addEventListener('change', () => validarPrecioConfigurado(filaEl));

    filaEl.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
        filaEl.remove();
        recalcularTotal();
        if (document.querySelectorAll('.fila-producto').length === 0) {
            const tr = document.createElement('tr');
            tr.id = 'filaVacia';
            tr.innerHTML = '<td colspan="5" class="text-center text-muted py-3">Use el botón para agregar productos.</td>';
            document.getElementById('cuerpoProductos').appendChild(tr);
        }
    });
});

// ── NRC del cliente seleccionado ─────────────────────────────────────────────
let clienteNrcActual = '';

function validarCCFConNRC() {
    const tipoDoc = document.getElementById('selectTipoDoc').value;
    if (tipoDoc !== 'credito_fiscal') return true;
    if (!clienteNrcActual) {
        Swal.fire({
            icon: 'warning',
            title: 'NRC requerido',
            text: 'El Crédito Fiscal requiere que el cliente tenga NRC registrado. Seleccione otro cliente o edite el cliente para agregar su NRC.',
        });
        return false;
    }
    return true;
}

// ── Select2 Cliente ───────────────────────────────────────────────────────────
$(function () {
    $('#selectCliente').select2({
        language: 'es',
        placeholder: 'Buscar cliente...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '<?= base_url('clientes/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term || '' }),
            processResults: data => ({ results: data.results }),
            cache: true,
        },
    }).on('select2:select', function (e) {
        clienteNrcActual = e.params.data.nrc || '';
        // Si ya está elegido CCF, avisar inmediatamente
        if (document.getElementById('selectTipoDoc').value === 'credito_fiscal' && !clienteNrcActual) {
            Swal.fire({
                icon: 'warning',
                title: 'Cliente sin NRC',
                text: 'Este cliente no tiene NRC registrado. El Crédito Fiscal requiere NRC. Seleccione otro cliente o actualice los datos del cliente.',
            });
        }
    }).on('select2:clear', function () {
        clienteNrcActual = '';
    });

    // Modal crear cliente
    $('#formCliente').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#btnGuardarCliente');
        const err = $('#clienteError');
        err.addClass('d-none').text('');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '<?= base_url('pedidos/cliente-store-ajax') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (!res.success) {
                    err.removeClass('d-none').text(res.message || 'Error al crear cliente.');
                    return;
                }
                const opt = new Option(res.cliente.text, res.cliente.id, true, true);
                $('#selectCliente').append(opt).trigger('change');
                $('#modalCliente').modal('hide');
                $('#formCliente')[0].reset();
            },
            error: function () { err.removeClass('d-none').text('Error de comunicación.'); },
            complete: function () { btn.prop('disabled', false).html('<i class="fa-solid fa-save"></i> Guardar cliente'); },
        });
    });
});

// ── Submit con confirmación ───────────────────────────────────────────────────
document.getElementById('formCrear').addEventListener('submit', function (e) {
    e.preventDefault();

    if (document.querySelectorAll('.fila-producto').length === 0) {
        Swal.fire('Sin productos', 'Debe agregar al menos un producto.', 'warning');
        return;
    }

    let sinSeleccion = false;
    document.querySelectorAll('.fila-producto').forEach(f => {
        if (!f.querySelector('.select-producto').value) sinSeleccion = true;
    });
    if (sinSeleccion) {
        Swal.fire('Producto requerido', 'Seleccione un producto en todas las filas.', 'warning');
        return;
    }

    const tipoDoc  = document.getElementById('selectTipoDoc');
    const tipoPago = document.getElementById('selectTipoPago');

    if (!tipoDoc.value) {
        Swal.fire('Atención', 'Debe seleccionar el tipo de documento.', 'warning');
        tipoDoc.focus();
        return;
    }

    // Validar precios configurados antes de enviar
    let precioError = false;
    document.querySelectorAll('.fila-producto').forEach(fila => {
        if (!validarPrecioConfigurado(fila, false)) precioError = true;
    });

    if (precioError) {
        Swal.fire('Precio inválido', 'Uno o más productos tienen un precio menor al mínimo permitido.', 'warning');
        return;
    }

    if (!validarCCFConNRC()) return;

    const total = document.getElementById('dispTotal').textContent;

    Swal.fire({
        title: 'Confirmar Nota de Pedido',
        html: `<b>Documento:</b> ${tipoDoc.options[tipoDoc.selectedIndex].text}<br>
               <b>Pago:</b> ${tipoPago.options[tipoPago.selectedIndex].text}<br><br>
               <b>Total: ${total}</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('formCrear').submit();
    });
});
</script>

<?= $this->endSection() ?>
