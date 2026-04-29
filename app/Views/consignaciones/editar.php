<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #ced4da; border-radius: .375rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: .75rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    #tablaProductos td { vertical-align: middle; }
</style>

<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">
                    Editar Nota de Envío <strong><?= esc($consignacion->numero) ?></strong>
                </h4>
                <a href="<?= base_url('consignaciones/' . $consignacion->id) ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Cancelar
                </a>
            </div>

            <div class="card-body">
                <form id="formEditar" method="POST" action="<?= base_url('consignaciones/' . $consignacion->id . '/actualizar') ?>">
                    <?= csrf_field() ?>

                    <!-- Fila 1 -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Número</label>
                            <input type="text" class="form-control" value="<?= esc($consignacion->numero) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Vendedor / Representante <span class="text-danger">*</span></label>
                            <select name="vendedor_id" id="selectVendedor" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($vendedores as $v): ?>
                                    <option value="<?= $v->id ?>" <?= $v->id == $consignacion->vendedor_id ? 'selected' : '' ?>>
                                        <?= esc($v->seller) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control"
                                value="<?= date('Y-m-d', strtotime($consignacion->fecha)) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Hora</label>
                            <input type="time" name="hora" class="form-control"
                                value="<?= $consignacion->hora ? substr($consignacion->hora, 0, 5) : '' ?>">
                        </div>
                    </div>

                    <!-- Fila 2 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Paciente</label>
                            <input type="text" name="nombre" class="form-control"
                                value="<?= esc($consignacion->nombre) ?>" placeholder="Ej: Paciente García">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small">Doctor</label>
                            <div class="d-flex">
                                <select name="doctor_id" id="selectDoctor" class="form-control flex-grow-1">
                                    <?php if (!empty($doctor)): ?>
                                        <option value="<?= $doctor->id ?>" selected><?= esc($doctor->nombre) ?></option>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-success ml-2"
                                    data-toggle="modal" data-target="#modalDoctor">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Cliente a facturar</label>
                            <select name="cliente_id" id="selectCliente" class="form-control">
                                <?php if (!empty($cliente)): ?>
                                    <option value="<?= $cliente->id ?>" selected><?= esc($cliente->nombre) ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Concepto</label>
                            <input type="text" name="concepto" class="form-control"
                                value="<?= esc($consignacion->concepto) ?>" placeholder="Descripción del propósito de la consignación">
                        </div>
                    </div>

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
                                    <th style="min-width:300px">Producto</th>
                                    <th style="width:110px">Cantidad</th>
                                    <th style="width:130px">Precio Unit.</th>
                                    <th style="width:130px" class="text-end">Subtotal</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoProductos">
                                <?php if (empty($detalles)): ?>
                                    <tr id="filaVacia">
                                        <td colspan="5" class="text-center text-muted py-3">
                                            Use el botón para agregar productos
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($detalles as $i => $d): ?>
                                    <tr class="fila-producto" data-idx="<?= $i ?>">
                                        <td>
                                            <select name="productos[<?= $i ?>][producto_id]"
                                                class="form-control form-control-sm select-producto" required>
                                                <option value="<?= $d->producto_id ?>" selected>
                                                    <?= esc($d->producto_codigo . ' — ' . $d->producto_nombre) ?>
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="productos[<?= $i ?>][cantidad]"
                                                class="form-control form-control-sm input-cantidad"
                                                value="<?= $d->cantidad ?>" min="0.01" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" name="productos[<?= $i ?>][precio_unitario]"
                                                class="form-control form-control-sm input-precio"
                                                value="<?= $d->precio_unitario ?>" min="0" step="0.01" required>
                                        </td>
                                        <td class="text-end">
                                            <input type="hidden" name="productos[<?= $i ?>][subtotal]"
                                                class="input-subtotal" value="<?= $d->subtotal ?>">
                                            <span class="subtotal-texto">$<?= number_format($d->subtotal, 2) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end fw-bold text-primary" id="totalGeneral">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Observaciones -->
                    <div class="row mb-3 mt-2">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"><?= esc($consignacion->observaciones) ?></textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template fila nueva -->
<template id="tplFilaProducto">
    <tr class="fila-producto">
        <td>
            <select name="productos[IDX][producto_id]" class="form-control form-control-sm select-producto" required></select>
        </td>
        <td>
            <input type="number" name="productos[IDX][cantidad]" class="form-control form-control-sm input-cantidad"
                min="0.01" step="0.01" value="1" required>
        </td>
        <td>
            <input type="number" name="productos[IDX][precio_unitario]" class="form-control form-control-sm input-precio"
                min="0" step="0.01" value="0.00" required>
        </td>
        <td class="text-end">
            <input type="hidden" name="productos[IDX][subtotal]" class="input-subtotal" value="0">
            <span class="subtotal-texto">$0.00</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    </tr>
</template>

<!-- Modal Nuevo Doctor -->
<div class="modal fade" id="modalDoctor" tabindex="-1">
    <div class="modal-dialog">
        <form id="formDoctor">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Doctor</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del doctor</label>
                        <input type="text" name="nombre" id="doctorNombre" class="form-control" required>
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

<script>
let filaIdx = <?= count($detalles) ?>;
const vendedorSelect = document.getElementById('selectVendedor');
const cuerpoProductos = document.getElementById('cuerpoProductos');

function calcularSubtotal(fila) {
    const cant   = parseFloat(fila.querySelector('.input-cantidad').value)  || 0;
    const precio = parseFloat(fila.querySelector('.input-precio').value)    || 0;
    const sub    = cant * precio;
    fila.querySelector('.input-subtotal').value       = sub.toFixed(2);
    fila.querySelector('.subtotal-texto').textContent = '$' + sub.toFixed(2);
    recalcularTotal();
}

function recalcularTotal() {
    let total = 0;
    document.querySelectorAll('.input-subtotal').forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('totalGeneral').textContent = '$' + total.toFixed(2);
}

function initSelectProducto(select) {
    $(select).select2({
        language: 'es',
        placeholder: 'Buscar producto...',
        allowClear: true,
        ajax: {
            url: '<?= base_url('productos/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({ results: data.results }),
            cache: true,
        },
    }).on('select2:select', function (e) {
        const productoId = e.params.data.id;
        const vendedorId = vendedorSelect.value;
        if (!vendedorId || !productoId) return;

        fetch(`<?= base_url('consignaciones/precio-ajax') ?>?vendedor_id=${vendedorId}&producto_id=${productoId}`)
            .then(r => r.json())
            .then(data => {
                if (data.precio !== null) {
                    const fila = $(select).closest('tr')[0];
                    fila.querySelector('.input-precio').value = parseFloat(data.precio).toFixed(2);
                    calcularSubtotal(fila);
                }
            });
    });
}

// Event delegation: cálculo de subtotales
cuerpoProductos.addEventListener('input', function (e) {
    if (e.target.classList.contains('input-cantidad') || e.target.classList.contains('input-precio')) {
        const fila = e.target.closest('.fila-producto');
        if (fila) calcularSubtotal(fila);
    }
});

// Event delegation: eliminar fila
cuerpoProductos.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-eliminar-fila');
    if (!btn) return;
    const fila = btn.closest('.fila-producto');
    if (!fila) return;
    fila.remove();
    recalcularTotal();
    if (!cuerpoProductos.querySelector('.fila-producto')) {
        const tr = document.createElement('tr');
        tr.id = 'filaVacia';
        tr.innerHTML = '<td colspan="5" class="text-center text-muted py-3">Use el botón para agregar productos</td>';
        cuerpoProductos.appendChild(tr);
    }
});

// Inicializar Select2 en filas existentes
window.addEventListener('load', function () {

    if (typeof $ === 'undefined') {
        console.error('jQuery no está cargado');
        return;
    }

    if (!$.fn.select2) {
        console.error('Select2 no está cargado');
        return;
    }

    document.querySelectorAll('.fila-producto').forEach(fila => {
        initSelectProducto(fila.querySelector('.select-producto'));
    });

    recalcularTotal();

    document.getElementById('btnAgregarProducto').addEventListener('click', function () {
        const tpl  = document.getElementById('tplFilaProducto').content.cloneNode(true);
        const fila = tpl.querySelector('tr');
        const idx  = filaIdx++;

        fila.innerHTML = fila.innerHTML.replaceAll('IDX', idx);
        document.getElementById('filaVacia')?.remove();
        cuerpoProductos.appendChild(fila);

        const insertedFila = cuerpoProductos.lastElementChild;
        initSelectProducto(insertedFila.querySelector('.select-producto'));
    });

    $('#selectDoctor').select2({
        language: 'es',
        placeholder: 'Buscar doctor...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: '<?= base_url('doctores/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term || '' }),
            processResults: data => ({ results: data.results || [] }),
            cache: true
        }
    });

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
            processResults: data => ({ results: data.results || [] }),
            cache: true
        }
    });

});

// Confirmación antes de guardar
document.getElementById('formEditar').addEventListener('submit', function (e) {
    if (!cuerpoProductos.querySelector('.fila-producto')) {
        e.preventDefault();
        Swal.fire('Sin productos', 'Debe agregar al menos un producto.', 'warning');
        return;
    }

    const vendedor = vendedorSelect.options[vendedorSelect.selectedIndex]?.text ?? '';
    const total    = document.getElementById('totalGeneral').textContent;

    e.preventDefault();

    Swal.fire({
        title: 'Confirmar cambios',
        html: `<b>Vendedor:</b> ${vendedor}<br><b>Total: ${total}</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
    }).then(r => {
        if (r.isConfirmed) document.getElementById('formEditar').submit();
    });
});

</script>

<?= $this->endSection() ?>