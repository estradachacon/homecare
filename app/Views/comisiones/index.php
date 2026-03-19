<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .select2-container .select2-selection--single {
        height: 41px !important;
        /* altura estándar Bootstrap */
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        /* centra texto */
        padding-left: .75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    /* focus igual que form-control */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
    }

    .table td,
    .table th {
        padding: 8px 8px !important;
        /* 🔥 reduce altura */
        line-height: 1.4 !important;
        /* 🔥 compacta texto */
        vertical-align: middle;
    }
</style>
<div class="row">
    <div class="col-md-12">

        <div class="card shadow-sm">

            <div class="card-header d-flex">
                <h4 class="mb-0">Resumen de Comisiones</h4>

                <?php if (tienePermiso('generar_comisiones')): ?>
                    <a href="<?= base_url('comisiones/generar') ?>"
                        class="btn btn-primary btn-sm ml-auto">
                        Generar Comisión
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <form onsubmit="return false" class="mb-3">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <small class="text-muted">Vendedor</small>
                            <select id="sellerSelect" class="form-control"></select>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Estado</small>
                            <select id="estadoFiltro" class="form-control">
                                <option value="">Todos</option>
                                <option value="generado">Generado</option>
                                <option value="pendiente">Pendiente</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Fecha inicio</small>
                            <input type="date" id="fechaInicio" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Fecha fin</small>
                            <input type="date" id="fechaFin" class="form-control">
                        </div>

                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">

                        <colgroup>
                            <col style="width: 3%;">
                            <col style="width: 17%;">
                            <col style="width: 17%;">
                            <col style="width: 9%;">
                            <col style="width: 9%;">
                            <col style="width: 9%;">
                            <col style="width: 9%;">
                            <col style="width: 6%;">
                        </colgroup>
                        <tr class="text-center">
                            <th>#</th>
                            <th>Vendedor</th>
                            <th>Rango</th>
                            <th>Total</th>
                            <th>Comisión</th>
                            <th>%</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody id="tbodyComisiones">
                            <?= view('comisiones/_tabla', ['comisiones' => $comisiones]) ?>
                        </tbody>
                    </table>

                    <div id="pagerContainer" class="d-flex mt-3">
                        <?= $pager->links('default', 'bootstrap_full') ?>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
<script>
    $(document).ready(function() {

        function cargarComisiones(url = null) {

            let seller = $('#sellerSelect').val();
            let estado = $('#estadoFiltro').val();
            let inicio = $('#fechaInicio').val();
            let fin = $('#fechaFin').val();

            const params = new URLSearchParams({
                seller_id: seller || '',
                estado: estado || '',
                fecha_inicio: inicio || '',
                fecha_fin: fin || ''
            });

            let endpoint = url ?? "<?= base_url('comisiones') ?>";

            fetch(endpoint + '?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    $('#tbodyComisiones').html(data.tbody);
                    $('#pagerContainer').html(data.pager);
                });
        }

        // SELECT2 vendedor
        $('#sellerSelect').select2({
            placeholder: 'Buscar vendedor...',
            minimumInputLength: 2,
            ajax: {
                url: "<?= base_url('sellers/searchAjax') ?>",
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term,
                    select2: 1
                }),
                processResults: data => data
            }
        });

        // listeners
        $('#sellerSelect, #estadoFiltro, #fechaInicio, #fechaFin')
            .on('change', () => cargarComisiones());

        // paginación AJAX
        $(document).on('click', '#pagerContainer a', function(e) {
            e.preventDefault();
            cargarComisiones($(this).attr('href'));
        });

    });
</script>
<?= $this->endSection() ?>