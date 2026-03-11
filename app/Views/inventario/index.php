<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .stock-badge,
    .badge {
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

            <div class="card-header d-flex">
                <h4 class="header-title">Listado de productos</h4>

                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('productos/new') ?>">
                    <i class="fa-solid fa-plus"></i> Nuevo
                </a>
                <a class="btn btn-success btn-sm ml-2" id="btnExcel">
                    <i class="fa fa-file-excel"></i> Excel
                </a>
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
    $(document).ready(function() {

        function cargarProductos(page = 1) {

            let buscar = $('[name="buscar"]').val();
            let estado = $('[name="estado"]').val();
            let stock = $('[name="stock"]').val();
            let orden = $('[name="orden"]').val();
            let perPage = $('[name="perPage"]').val();
            let tipo = $('[name="tipo"]').val();

            const params = new URLSearchParams({
                page: page,
                perPage: perPage,
                buscar: buscar || '',
                estado: estado || '',
                stock: stock || '',
                orden: orden || '',
                tipo: tipo || ''
            });

            fetch('<?= base_url('inventory') ?>?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {

                    $('tbody').html(data.tbody);
                    $('#pagerContainer').html(data.pager);
                    $('#infoResultados').html(data.info);

                });

        }

        // LISTENERS FILTROS
        $('[name="buscar"]').on('keyup', function() {
            cargarProductos();
        });

        $('[name="estado"]').on('change', function() {
            cargarProductos();
        });

        $('[name="stock"]').on('change', function() {
            cargarProductos();
        });

        $('[name="orden"]').on('change', function() {
            cargarProductos();
        });

        $('[name="buscar"]').on('keyup', function() {
            cargarProductos(1);
        });

        $('[name="estado"], [name="stock"], [name="orden"], [name="perPage"]').on('change', function() {
            cargarProductos(1);
        });

        $('[name="tipo"]').on('change', function() {
            cargarProductos(1);
        });
        cargarProductos(1);
    });

    $(document).on('click', '.btnEditar', function() {

        let id = $(this).data('id');
        let descripcion = $(this).data('descripcion');
        let codigo = $(this).data('codigo');
        let activo = $(this).data('activo');

        Swal.fire({
            title: 'Editar producto',
            width: 500,
            html: `
        <div style="text-align:left">

            <label class="text-muted small">Descripción</label>
            <textarea id="swalDescripcion" 
                class="form-control mb-2" 
                style="font-size:15px;height:80px">${descripcion}</textarea>

            <label class="text-muted small">Código</label>
            <input id="swalCodigo" 
                class="form-control mb-2" 
                style="font-size:15px"
                value="${codigo}">

            <label class="text-muted small">Estado</label>
            <select id="swalActivo" 
                class="form-control"
                style="font-size:15px">

                <option value="1" ${activo == 1 ? 'selected' : ''}>Activo</option>
                <option value="0" ${activo == 0 ? 'selected' : ''}>Inactivo</option>

            </select>

        </div>
        `,
            showCancelButton: true,
            confirmButtonText: 'Guardar cambios',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            focusConfirm: false,

            preConfirm: () => {

                return {
                    descripcion: $('#swalDescripcion').val(),
                    codigo: $('#swalCodigo').val(),
                    activo: $('#swalActivo').val()
                };

            }

        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("<?= base_url('inventory/update') ?>/" + id, {

                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(result.value)

                    })
                    .then(r => r.json())
                    .then(data => {

                        if (data.success) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Producto actualizado',
                                text: 'Los cambios fueron guardados correctamente',
                                timer: 1600,
                                showConfirmButton: false
                            });

                            setTimeout(function() {

                                location.reload();

                            }, 1500);

                        } else {

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });

                        }

                    });

            }

        });

    });

    $(document).on('click', '#pagerContainer a', function(e) {

        e.preventDefault();

        let url = new URL($(this).attr('href'));
        let page = url.searchParams.get('page');

        cargarProductos(page);

    });

    $('#btnExcel').on('click', function() {

        let buscar = $('[name="buscar"]').val();
        let estado = $('[name="estado"]').val();
        let stock = $('[name="stock"]').val();
        let orden = $('[name="orden"]').val();
        let tipo = $('[name="tipo"]').val();

        const params = new URLSearchParams({
            buscar: buscar || '',
            estado: estado || '',
            stock: stock || '',
            orden: orden || ''
        });

        window.open('<?= base_url('inventory/excel') ?>?' + params.toString(), '_blank');

    });
</script>

<?= $this->endSection() ?>