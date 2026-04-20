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
                <h4 class="header-title mb-0">Listado de Compras</h4>
                <div class="ml-auto d-flex gap-2">
                    <?php if (tienePermiso('ingresar_compras')): ?>
                        <a class="btn btn-primary btn-sm mr-2" href="<?= base_url('purchases/new') ?>">
                            <i class="fa-solid fa-plus"></i> Nueva compra
                        </a>
                    <?php endif; ?>
                    <?php if (tienePermiso('cargar_compras_json')): ?>
                        <a class="btn btn-success btn-sm" href="<?= base_url('purchases/load') ?>">
                            <i class="fa-solid fa-upload"></i> Cargar json
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
                            <small class="text-muted">N° Compra</small>
                            <input type="text" id="numeroCompra" class="form-control" placeholder="Ej: 000785">
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Tipo documento</small>
                            <select name="tipo_dte" class="form-control">
                                <option value="">Todos</option>
                                <?php
                                $siglas = dte_siglas();
                                $descripciones = dte_descripciones();
                                ?>
                                <?php foreach ($siglas as $key => $sigla): ?>
                                    <?php $nombre = $descripciones[$sigla] ?? $sigla; ?>
                                    <option value="<?= $key ?>"><?= esc($sigla . ' - ' . $nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Estado</small>
                            <select name="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="activa">Activas</option>
                                <option value="pagada">Pagadas</option>
                                <option value="parcial">Parciales</option>
                                <option value="anulada">Anuladas</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Fecha emisión</small>
                            <input type="text" name="fecha" id="fechaFiltro" class="form-control" placeholder="dd/mm/yyyy">
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
                            <th class="text-center" style="width:80px;"># DTE</th>
                            <th style="width:180px;">Tipo</th>
                            <th>Proveedor</th>
                            <th class="text-center" style="width:110px;">Fecha</th>
                            <th class="text-center" style="width:110px;">Condición</th>
                            <th class="text-end" style="width:110px;">Total</th>
                            <th class="text-end" style="width:110px;">Saldo</th>
                            <th class="text-center" style="width:100px;">Estado</th>
                            <th class="text-center" style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($compras)): ?>
                            <?php foreach ($compras as $compra): ?>
                                <?php
                                $siglas       = dte_siglas();
                                $descripciones = dte_descripciones();
                                $codigo       = $compra->tipo_dte ?? null;
                                $sigla        = $siglas[$codigo] ?? null;
                                $descripcion  = $sigla ? ($descripciones[$sigla] ?? null) : null;
                                $saldo        = (float)($compra->saldo ?? 0);
                                $total        = (float)($compra->total_pagar ?? 0);
                                $condicion    = $compra->condicion_operacion ?? 1;
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <?= esc(substr($compra->numero_control, -6)) ?>
                                    </td>
                                    <td>
                                        <?php if ($sigla && $descripcion): ?>
                                            <span class="badge badge-estado1 bg-info text-white"><?= esc($sigla) ?></span>
                                            <br>
                                            <small class="text-muted"><?= esc($descripcion) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Desconocido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($compra->proveedor_nombre ?? 'Sin proveedor') ?></td>
                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($compra->fecha_emision)) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($condicion == 1): ?>
                                            <span class="badge badge-estado bg-success text-white">Contado</span>
                                        <?php elseif ($condicion == 2): ?>
                                            <span class="badge badge-estado bg-warning text-dark">Crédito</span>
                                        <?php else: ?>
                                            <span class="badge badge-estado bg-secondary text-white">N/D</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">$ <?= number_format($total, 2) ?></td>
                                    <td class="text-end <?= $saldo > 0 ? 'text-danger fw-semibold' : 'text-success' ?>">
                                        $ <?= number_format($saldo, 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($compra->anulada ?? 0) == 1): ?>
                                            <span class="badge badge-estado bg-danger text-white">Anulada</span>
                                        <?php elseif ($saldo <= 0): ?>
                                            <span class="badge badge-estado bg-info text-white">
                                                <i class="fa-solid fa-check-circle"></i> Pagada
                                            </span>
                                        <?php elseif ($saldo < $total): ?>
                                            <span class="badge badge-estado bg-warning text-dark">Parcial</span>
                                        <?php else: ?>
                                            <span class="badge badge-estado bg-warning text-dark">Activa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('purchases/' . $compra->id) ?>" class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">No hay compras registradas</td>
                            </tr>
                        <?php endif; ?>
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
    $(document).ready(function() {

        $('form').on('submit', function(e) {
            e.preventDefault();
        });
        $('input').on('keydown', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });

        function cargarCompras() {

            let proveedorId = $('#proveedorSelect').val();
            let estado = $('[name="estado"]').val();
            let tipo_dte = $('[name="tipo_dte"]').val();
            let fecha = $('#fechaFiltro').val();
            let numero = $('#numeroCompra').val();
            let perPage = $('#perPage').val();

            if (fecha && fecha.length === 10) {
                let p = fecha.split('/');
                fecha = `${p[2]}-${p[1]}-${p[0]}`;
            }

            const params = new URLSearchParams({
                proveedor_id: proveedorId || '',
                estado: estado || '',
                tipo_dte: tipo_dte || '',
                fecha: fecha || '',
                numero_compra: numero || '',
                per_page: perPage || 25,
            });

            fetch('<?= base_url('purchases') ?>?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    $('tbody').html(data.tbody);
                    $('#pagerContainer').html(data.pager);
                });
        }

        // SELECT2 PROVEEDOR
        $('#proveedorSelect').select2({
            minimumInputLength: 2,
            language: 'es',
            placeholder: 'Buscar proveedor...',
            allowClear: true,
            ajax: {
                url: '<?= base_url("proveedores/searchAjax") ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term,
                    select2: 1
                }),
                processResults: data => data, // ya viene con { results: [...] }
                cache: true
            }
        });
        // LISTENERS
        $('#proveedorSelect').on('change', cargarCompras);
        $('[name="estado"], [name="tipo_dte"], #perPage').on('change', cargarCompras);
        $('#numeroCompra').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
            cargarCompras();
        });

        // FECHA MASK
        const fechaInput = document.getElementById('fechaFiltro');
        fechaInput.addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '');
            if (v.length > 8) v = v.substring(0, 8);
            if (v.length >= 5) this.value = v.substring(0, 2) + '/' + v.substring(2, 4) + '/' + v.substring(4);
            else if (v.length >= 3) this.value = v.substring(0, 2) + '/' + v.substring(2);
            else this.value = v;
            if (this.value === '' || this.value.length === 10) cargarCompras();
        });

        // PAGER AJAX
        $(document).on('click', '#pagerContainer a', function(e) {
            e.preventDefault();
            fetch($(this).attr('href'), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
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