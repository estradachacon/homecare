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
                <h4 class="header-title mb-0">Nueva Nota de Envío</h4>
                <a href="<?= base_url('consignaciones') ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card-body">
                <form id="formCrear" method="POST" action="<?= base_url('consignaciones/guardar') ?>">
                    <?= csrf_field() ?>

                    <!-- Fila 1 -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Número</label>
                            <input type="text" name="numero" class="form-control"
                                value="<?= esc($numero_sugerido) ?>" readonly required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Vendedor / Representante <span class="text-danger">*</span></label>
                            <select name="vendedor_id" id="selectVendedor" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($vendedores as $v): ?>
                                    <option value="<?= $v->id ?>"><?= esc($v->seller) ?></option>
                                <?php endforeach; ?>
                            </select>
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

                    <!-- Fila 2 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nombre / Referencia</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Paciente García, Clínica Médica...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Concepto</label>
                            <input type="text" name="concepto" class="form-control" placeholder="Descripción del propósito de la consignación">
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
                                <tr id="filaVacia">
                                    <td colspan="5" class="text-center text-muted py-3">
                                        Use el botón para agregar productos
                                    </td>
                                </tr>
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
                            <textarea name="observaciones" class="form-control" rows="2" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save"></i> Guardar Nota de Envío
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template fila producto (hidden) -->
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

<script>
let filaIdx = 0;
const vendedorSelect = document.getElementById('selectVendedor');

function calcularSubtotal(fila) {
    const cant   = parseFloat(fila.querySelector('.input-cantidad').value)  || 0;
    const precio = parseFloat(fila.querySelector('.input-precio').value)    || 0;
    const sub    = cant * precio;
    fila.querySelector('.input-subtotal').value   = sub.toFixed(2);
    fila.querySelector('.subtotal-texto').textContent = '$' + sub.toFixed(2);
    recalcularTotal();
}

function recalcularTotal() {
    let total = 0;
    document.querySelectorAll('.input-subtotal').forEach(i => total += parseFloat(i.value) || 0);
    document.getElementById('totalGeneral').textContent = '$' + total.toFixed(2);
}

function initSelectProducto(select, idx) {
    $(select).select2({
        language: 'es',
        placeholder: 'Buscar producto...',
        allowClear: true,
        ajax: {
            url: '<?= base_url('productos/searchAjax') ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.results
            }),
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

document.getElementById('btnAgregarProducto').addEventListener('click', function () {
    const tpl  = document.getElementById('tplFilaProducto').content.cloneNode(true);
    const fila = tpl.querySelector('tr');
    const idx  = filaIdx++;

    fila.innerHTML = fila.innerHTML.replaceAll('IDX', idx);
    document.getElementById('filaVacia')?.remove();
    document.getElementById('cuerpoProductos').appendChild(fila);

    const select = document.getElementById('cuerpoProductos').lastElementChild.querySelector('.select-producto');
    initSelectProducto(select, idx);

    fila.querySelector('.input-cantidad').addEventListener('input', () => calcularSubtotal(fila));
    fila.querySelector('.input-precio').addEventListener('input',   () => calcularSubtotal(fila));
    fila.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
        fila.remove();
        recalcularTotal();
        if (document.querySelectorAll('.fila-producto').length === 0) {
            const tr = document.createElement('tr');
            tr.id = 'filaVacia';
            tr.innerHTML = '<td colspan="5" class="text-center text-muted py-3">Use el botón para agregar productos</td>';
            document.getElementById('cuerpoProductos').appendChild(tr);
        }
    });
});

document.getElementById('formCrear').addEventListener('submit', function (e) {
    const filas = document.querySelectorAll('.fila-producto');
    if (filas.length === 0) {
        e.preventDefault();
        Swal.fire('Sin productos', 'Debe agregar al menos un producto.', 'warning');
        return;
    }

    const vendedor = vendedorSelect.options[vendedorSelect.selectedIndex]?.text ?? '';
    const numero   = document.querySelector('[name="numero"]').value;
    const total    = document.getElementById('totalGeneral').textContent;

    e.preventDefault();

    Swal.fire({
        title: 'Confirmar Nota de Envío',
        html: `<b>Número:</b> ${numero}<br><b>Vendedor:</b> ${vendedor}<br><br><b>Total: ${total}</b>`,
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
