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
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
    }
    .badge-estado {
        font-size: 0.75rem;
        padding: 5px 12px;
        border-radius: 10px;
        font-weight: 500;
    }
    .badge-estado1 {
        font-size: 0.65rem;
        padding: 4px 12px;
        border-radius: 5px;
        font-weight: 500;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0">Pagos a Proveedores</h4>
                <div class="ml-auto d-flex gap-2">
                    <?php if (tienePermiso('registrar_pagos_a_compras')): ?>
                        <a class="btn btn-primary btn-sm" href="<?= base_url('compraspagos/new') ?>">
                            <i class="fa-solid fa-plus"></i> Nuevo pago
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">

                <!-- FILTROS -->
                <form onsubmit="return false" class="mb-3">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <small class="text-muted">Proveedor</small>
                            <select id="proveedorSelect" class="form-control"></select>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">N° Pago</small>
                            <input type="text" id="numeroPago" class="form-control" placeholder="Ej: 000123">
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Fecha pago</small>
                            <input type="text" id="fechaFiltro" class="form-control" placeholder="dd/mm/yyyy">
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Estado</small>
                            <select name="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="activo">Activos</option>
                                <option value="anulado">Anulados</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Registros por página</small>
                            <select id="perPage" class="form-control">
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="99999">Todos</option>
                            </select>
                        </div>

                    </div>
                </form>

                <!-- TABLA -->
                <table class="table table-striped table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:100px;">N° Pago</th>
                            <th>Proveedor</th>
                            <th class="text-center" style="width:110px;">Fecha</th>
                            <th class="text-center" style="width:120px;">Forma pago</th>
                            <th class="text-end"    style="width:120px;">Total</th>
                            <th class="text-center" style="width:100px;">Estado</th>
                            <th class="text-center" style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?= view('ComprasPagos/_tbody', ['pagos' => $pagos]) ?>
                    </tbody>
                </table>

                <div id="pagerContainer" class="d-flex mt-3">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    $('form').on('submit', function (e) { e.preventDefault(); });
    $('input').on('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); });

    function cargarPagos() {

        let proveedorId = $('#proveedorSelect').val();
        let estado      = $('[name="estado"]').val();
        let fecha       = $('#fechaFiltro').val();
        let numero      = $('#numeroPago').val();
        let perPage     = $('#perPage').val();

        if (fecha && fecha.length === 10) {
            let p = fecha.split('/');
            fecha = `${p[2]}-${p[1]}-${p[0]}`;
        }

        const params = new URLSearchParams({
            proveedor_id: proveedorId || '',
            estado:       estado      || '',
            fecha:        fecha       || '',
            numero_pago:  numero      || '',
            per_page:     perPage     || 25,
        });

        fetch('<?= base_url('compraspagos') ?>?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            $('tbody').html(data.tbody);
            $('#pagerContainer').html(data.pager);
        });
    }

    // SELECT2 PROVEEDOR
    $('#proveedorSelect').select2({
        language: 'es',
        placeholder: 'Buscar proveedor...',
        allowClear: true,
        ajax: {
            url: '<?= base_url("proveedores/searchAjax") ?>',
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term, select2: 1 }),
            processResults: data => data,
            cache: true
        }
    });

    // LISTENERS
    $('#proveedorSelect').on('change', cargarPagos);
    $('[name="estado"], #perPage').on('change', cargarPagos);
    $('#numeroPago').on('input', function () {
        this.value = this.value.replace(/\D/g, '');
        cargarPagos();
    });

    // FECHA MASK
    const fechaInput = document.getElementById('fechaFiltro');
    fechaInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '');
        if (v.length > 8) v = v.substring(0, 8);
        if (v.length >= 5)      this.value = v.substring(0,2) + '/' + v.substring(2,4) + '/' + v.substring(4);
        else if (v.length >= 3) this.value = v.substring(0,2) + '/' + v.substring(2);
        else                    this.value = v;
        if (this.value === '' || this.value.length === 10) cargarPagos();
    });

    // PAGER AJAX
    $(document).on('click', '#pagerContainer a', function (e) {
        e.preventDefault();
        fetch($(this).attr('href'), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            $('tbody').html(data.tbody);
            $('#pagerContainer').html(data.pager);
        });
    });

});
</script>

<?= $this->endSection() ?>