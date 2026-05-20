<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .stock-badge {
        font-size: 14px;
        padding: 5px 16px;
        min-width: 40px;
        display: inline-block;
        text-align: center;
    }
</style>
<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0">Listado de productos</h4>

                <div class="ml-auto">
                    <button class="btn btn-primary btn-sm mr-1" id="btnNuevoProducto">
                        <i class="fa-solid fa-plus"></i> Nuevo
                    </button>
                    <button class="btn btn-outline-secondary btn-sm mr-1" id="btnPlantilla" title="Descargar plantilla Excel">
                        <i class="fa fa-file-excel"></i> Plantilla
                    </button>
                    <button class="btn btn-outline-info btn-sm mr-1" id="btnImportar" title="Importar productos desde Excel">
                        <i class="fa-solid fa-file-import"></i> Importar
                    </button>
                    <button class="btn btn-success btn-sm" id="btnExcel">
                        <i class="fa fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>


            <div class="card-body">

                <form onsubmit="return false" class="mb-3">

                    <div class="row g-2">

                        <div class="col-md-2">
                            <small class="text-muted">Buscar producto</small>
                            <input type="text" name="buscar" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Estado</small>
                            <select name="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Tipo</small>
                            <select name="tipo" class="form-control">
                                <option value="1" selected>Bien</option>
                                <option value="2">Servicio</option>
                                <option value="3">Otro</option>
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Stock</small>
                            <select name="stock" class="form-control">
                                <option value="">Todos</option>
                                <option value="con">Con stock</option>
                                <option value="sin">Sin stock</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Orden</small>
                            <select name="orden" class="form-control">
                                <option value="">Normal</option>
                                <option value="stock_desc">Mayor stock</option>
                                <option value="stock_asc">Menor stock</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <span class="text-muted small">
                                <i class="fa fa-list mr-1"></i> Mostrar
                            </span>
                            <div class="d-flex">
                                <select class="form-control form-control-m mr-2" name="perPage" style="width:100px;">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                    <option value="all">Todos</option>
                                </select>

                                <span class="text-muted small mt-4">
                                    registros
                                </span>

                            </div>

                        </div>
                    </div>
                </form>


                <table class="table table-striped table-bordered table-hover">

                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th width="200">Estado</th>
                            <th width="200" class="text-end">Stock</th>
                            <th width="170">Menú</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?= view('inventario/_rows', ['productos' => $productos]) ?>
                    </tbody>

                </table>

                <div class="row align-items-center mt-3">

                    <div class="col-md-4 text-muted small" id="infoResultados">
                        <?= $info ?>
                    </div>

                    <div class="col-md-8 d-flex justify-content-end" id="pagerContainer">
                        <?= $pager->links('default', 'bootstrap_full') ?>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(function () {

    // ── cargarProductos ────────────────────────────────────────
    function cargarProductos(page) {
        page = page || 1;
        const params = new URLSearchParams({
            page:    page,
            perPage: $('[name="perPage"]').val()  || 10,
            buscar:  $('[name="buscar"]').val()   || '',
            estado:  $('[name="estado"]').val()   || '',
            stock:   $('[name="stock"]').val()    || '',
            orden:   $('[name="orden"]').val()    || '',
            tipo:    $('[name="tipo"]').val()     || '',
        });
        fetch('<?= base_url('inventory') ?>?' + params, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            $('tbody').html(data.tbody);
            $('#pagerContainer').html(data.pager);
            $('#infoResultados').html(data.info);
        });
    }

    $('[name="buscar"]').on('input', () => cargarProductos(1));
    $('[name="estado"],[name="stock"],[name="orden"],[name="perPage"],[name="tipo"]').on('change', () => cargarProductos(1));
    cargarProductos(1);

    $(document).on('click', '#pagerContainer a', function (e) {
        e.preventDefault();
        cargarProductos(new URL($(this).attr('href')).searchParams.get('page'));
    });

    // ── Excel export ───────────────────────────────────────────
    $('#btnExcel').on('click', function () {
        const params = new URLSearchParams({
            buscar: $('[name="buscar"]').val() || '',
            estado: $('[name="estado"]').val() || '',
            stock:  $('[name="stock"]').val()  || '',
            orden:  $('[name="orden"]').val()  || '',
        });
        window.open('<?= base_url('inventory/excel') ?>?' + params, '_blank');
    });

    // ── Plantilla ──────────────────────────────────────────────
    $('#btnPlantilla').on('click', function () {
        window.location.href = '<?= base_url('inventory/plantilla-excel') ?>';
    });

    // ── Clasificaciones: cargar select ─────────────────────────
    function cargarClasificacionesSelect(selectedId) {
        selectedId = selectedId || '';
        fetch('<?= base_url('clasificaciones/lista') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            let opts = '<option value="">— Sin clasificación —</option>';
            d.data.forEach(c => {
                opts += `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.nombre}</option>`;
            });
            $('#pClasificacion').html(opts);
        });
    }

    // ── Clasificaciones: lista de gestión ──────────────────────
    function cargarListaGestion() {
        fetch('<?= base_url('clasificaciones/lista') ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => {
            if (!d.data.length) {
                $('#listaClasificaciones').html(
                    '<p class="text-muted text-center small mt-2 mb-0">No hay clasificaciones registradas.</p>'
                );
                return;
            }
            let html = '<ul class="list-group list-group-flush">';
            d.data.forEach(c => {
                html += `
                <li class="list-group-item py-1 px-0 d-flex align-items-center" data-id="${c.id}">
                    <span class="flex-grow-1 clas-view">${c.nombre}</span>
                    <div class="input-group input-group-sm d-none clas-edit" style="max-width:220px">
                        <input type="text" class="form-control form-control-sm inp-clas" value="${c.nombre}">
                        <div class="input-group-append">
                            <button class="btn btn-success btn-sm btn-save-clas" title="Guardar"><i class="fa fa-check"></i></button>
                            <button class="btn btn-secondary btn-sm btn-cancel-clas" title="Cancelar"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <button class="btn btn-xs btn-outline-warning ml-2 btn-edit-clas" title="Renombrar"><i class="fa fa-pen"></i></button>
                    <button class="btn btn-xs btn-outline-danger ml-1 btn-del-clas" title="Eliminar"><i class="fa fa-trash"></i></button>
                </li>`;
            });
            html += '</ul>';
            $('#listaClasificaciones').html(html);
        });
    }

    // ── Abrir modal Nuevo ──────────────────────────────────────
    $('#btnNuevoProducto').on('click', function () {
        $('#productoModalTitulo').text('Nuevo Producto');
        $('#productoId').val(0);
        $('#formProducto')[0].reset();
        cargarClasificacionesSelect('');
        $('#modalProducto').modal('show');
    });

    // ── Abrir modal Editar ─────────────────────────────────────
    $(document).on('click', '.btnEditar', function () {
        const b = $(this).data();
        $('#productoModalTitulo').text('Editar Producto');
        $('#productoId').val(b.id);
        $('#pDescripcion').val(b.descripcion);
        $('#pCodigo').val(b.codigo);
        $('#pTipo').val(b.tipo || 1);
        $('#pMarca').val(b.marca || '');
        $('#pActivo').val(b.activo);
        $('#pPrecioMinimo').val(parseFloat(b.precioMinimo || 0).toFixed(2));
        cargarClasificacionesSelect(b.clasificacionId || '');
        $('#modalProducto').modal('show');
    });

    // ── Guardar producto (create / update) ─────────────────────
    $('#formProducto').on('submit', function (e) {
        e.preventDefault();
        const id = parseInt($('#productoId').val());
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const payload = {
            descripcion:      $('#pDescripcion').val().trim(),
            codigo:           $('#pCodigo').val().trim(),
            tipo:             parseInt($('#pTipo').val()),
            marca:            $('#pMarca').val().trim() || null,
            clasificacion_id: $('#pClasificacion').val() || null,
            activo:           parseInt($('#pActivo').val()),
            precio_minimo:    parseFloat($('#pPrecioMinimo').val()) || 0,
        };
        const url = id > 0
            ? '<?= base_url('inventory/update') ?>/' + id
            : '<?= base_url('inventory/store') ?>';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(d => {
            if (d.success) {
                $('#modalProducto').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: id > 0 ? 'Producto actualizado' : 'Producto creado',
                    text: d.message,
                    timer: 1600,
                    showConfirmButton: false
                });
                setTimeout(() => cargarProductos(), 1500);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: d.message });
            }
        });
    });

    // ── Gestionar clasificaciones (modal anidado BS4) ──────────
    $('#btnGestionarClasificaciones').on('click', function () {
        $('#nuevaClasificacion').val('');
        cargarListaGestion();
        $('#modalClasificaciones').css('z-index', 1070).modal('show');
        setTimeout(() => { $('.modal-backdrop').not('.modal-stack').last().addClass('modal-stack').css('z-index', 1060); }, 10);
    });

    $('#modalClasificaciones').on('hidden.bs.modal', function () {
        cargarClasificacionesSelect($('#pClasificacion').val());
    });

    // Editar inline
    $(document).on('click', '.btn-edit-clas', function () {
        const $li = $(this).closest('li');
        $li.find('.clas-view, .btn-edit-clas, .btn-del-clas').addClass('d-none');
        $li.find('.clas-edit').removeClass('d-none').find('input').focus();
    });

    $(document).on('click', '.btn-cancel-clas', function () {
        const $li = $(this).closest('li');
        $li.find('.clas-view, .btn-edit-clas, .btn-del-clas').removeClass('d-none');
        $li.find('.clas-edit').addClass('d-none');
    });

    $(document).on('click', '.btn-save-clas', function () {
        const $li = $(this).closest('li');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        fetch('<?= base_url('clasificaciones/guardar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ id: $li.data('id'), nombre: $li.find('.inp-clas').val().trim() })
        }).then(r => r.json()).then(d => {
            if (d.success) cargarListaGestion();
            else Swal.fire({ icon: 'error', title: 'Error', text: d.message });
        });
    });

    // Guardar con Enter en el input inline
    $(document).on('keypress', '.inp-clas', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $(this).closest('li').find('.btn-save-clas').trigger('click'); }
    });

    $(document).on('click', '.btn-del-clas', function () {
        const id = $(this).closest('li').data('id');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        Swal.fire({
            title: '¿Eliminar clasificación?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch('<?= base_url('clasificaciones/eliminar') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
            }).then(r => r.json()).then(d => {
                if (d.success) cargarListaGestion();
                else Swal.fire({ icon: 'error', title: 'No se puede eliminar', text: d.message });
            });
        });
    });

    // Agregar nueva clasificación
    $('#btnAgregarClasificacion').on('click', function () {
        const nombre = $('#nuevaClasificacion').val().trim();
        if (!nombre) return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        fetch('<?= base_url('clasificaciones/guardar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ id: 0, nombre: nombre })
        }).then(r => r.json()).then(d => {
            if (d.success) { $('#nuevaClasificacion').val(''); cargarListaGestion(); }
            else Swal.fire({ icon: 'error', title: 'Error', text: d.message });
        });
    });

    $('#nuevaClasificacion').on('keypress', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#btnAgregarClasificacion').trigger('click'); }
    });

    // ── Importar Excel ─────────────────────────────────────────
    $('#btnImportar').on('click', function () {
        $('#inputArchivoImport').val('');
        $('#importResultados').addClass('d-none').html('');
        $('#modalImportar').modal('show');
    });

    $('#formImportar').on('submit', function (e) {
        e.preventDefault();
        const file = $('#inputArchivoImport')[0].files[0];
        if (!file) {
            Swal.fire({ icon: 'warning', title: 'Sin archivo', text: 'Selecciona un archivo .xlsx o .xls' });
            return;
        }
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const fd = new FormData();
        fd.append('archivo', file);
        fetch('<?= base_url('inventory/importar-excel') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            body: fd
        }).then(r => r.json()).then(d => {
            if (!d.success) { Swal.fire({ icon: 'error', title: 'Error', text: d.message }); return; }
            let html = `<div class="alert alert-success py-2 mb-2">
                <i class="fa fa-check-circle mr-1"></i>
                <strong>${d.importados}</strong> producto(s) importados correctamente.
            </div>`;
            if (d.errores && d.errores.length) {
                html += `<div class="alert alert-warning py-2 mb-0">
                    <strong>${d.errores.length} advertencia(s):</strong>
                    <ul class="mb-0 mt-1 small">${d.errores.map(e => `<li>${e}</li>`).join('')}</ul>
                </div>`;
            }
            $('#importResultados').html(html).removeClass('d-none');
            if (d.importados > 0) {
                setTimeout(() => { $('#modalImportar').modal('hide'); cargarProductos(1); }, 2500);
            }
        });
    });

});
</script>

<!-- Modal: Producto (crear / editar) -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formProducto">
                <div class="modal-header">
                    <h5 class="modal-title" id="productoModalTitulo">Nuevo Producto</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="productoId" value="0">

                    <div class="form-group">
                        <label>Descripción <span class="text-danger">*</span></label>
                        <textarea id="pDescripcion" class="form-control" rows="2" required
                            placeholder="Nombre completo del producto"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Código <span class="text-danger">*</span></label>
                                <input type="text" id="pCodigo" class="form-control" required placeholder="Ej: PRD-001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo</label>
                                <select id="pTipo" class="form-control">
                                    <option value="1">Bien</option>
                                    <option value="2">Servicio</option>
                                    <option value="3">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" id="pMarca" class="form-control" placeholder="Ej: 3M, Kendall, BBraun…">
                    </div>

                    <div class="form-group">
                        <label>Clasificación</label>
                        <div class="input-group">
                            <select id="pClasificacion" class="form-control">
                                <option value="">— Sin clasificación —</option>
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="btnGestionarClasificaciones"
                                    title="Gestionar catálogo de clasificaciones">
                                    <i class="fa fa-cog"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Precio Mínimo de Venta</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                            <input type="number" id="pPrecioMinimo" class="form-control" min="0" step="0.01" value="0.00" placeholder="0.00">
                        </div>
                        <small class="text-muted">Precio mínimo que los vendedores pueden ingresar en notas de pedido.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label>Estado</label>
                        <select id="pActivo" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Gestionar Clasificaciones (anidado) -->
<div class="modal fade" id="modalClasificaciones" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-tags mr-1"></i> Clasificaciones</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="listaClasificaciones" style="max-height:300px;overflow-y:auto;min-height:40px;"></div>
                <div class="input-group mt-3">
                    <input type="text" id="nuevaClasificacion" class="form-control form-control-sm"
                        placeholder="Nueva clasificación…" maxlength="150">
                    <div class="input-group-append">
                        <button class="btn btn-primary btn-sm" id="btnAgregarClasificacion">
                            <i class="fa fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Importar Excel -->
<div class="modal fade" id="modalImportar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formImportar" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-file-import mr-1"></i> Importar Productos desde Excel</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3 small">
                        <i class="fa fa-info-circle mr-1"></i>
                        Columnas requeridas: <strong>descripcion</strong>, <strong>codigo</strong>,
                        <strong>tipo</strong> (1=Bien, 2=Servicio, 3=Otro).
                        Descarga la plantilla con el botón <strong>Plantilla</strong>.
                    </div>
                    <div class="form-group mb-0">
                        <label>Archivo Excel (.xlsx / .xls) <span class="text-danger">*</span></label>
                        <input type="file" id="inputArchivoImport" name="archivo" class="form-control-file"
                            accept=".xlsx,.xls" required>
                    </div>
                    <div id="importResultados" class="d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload mr-1"></i> Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>