<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    /* Unificar altura Select2 con Bootstrap */

    .select2-container .select2-selection--single {
        height: 38px !important;
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

    .badge-estado{
        font-size: 0.75rem;
        padding: 5px 12px;
        border-radius: 10px;
        font-weight: 500;
    }

    .badge-estado1{
        font-size: 0.65rem;
        padding: 4px 12px;
        border-radius: 5px;
        font-weight: 500;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de facturas</h4>
                <?php if (tienePermiso('cargar_facturas')): ?>
                    <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('facturas/carga') ?>"><i
                            class="fa-solid fa-plus"></i> Cargar..</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form onsubmit="return false" class="mb-3">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <small class="text-muted">Cliente</small>
                            <select id="clienteSelect" class="form-control"></select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">N° Factura</small>
                            <input
                                type="text"
                                id="numeroFactura"
                                class="form-control"
                                placeholder="Ej: 000123">
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Vendedor</small>
                            <select id="sellerSelect" class="form-control"></select>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Estado</small>
                            <select name="estado" class="form-control">
                                <option value="">Todos</option>
                                <option value="activa">Activas</option>
                                <option value="anulada">Anuladas</option>
                                <option value="pagada">Pagadas</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted">Tipo documento</small>
                            <select name="tipo_dte" class="form-control">
                                <option value="">Todos</option>

                                <?php
                                $siglas = dte_siglas();
                                $descripciones = dte_descripciones();
                                ?>

                                <?php foreach ($siglas as $key => $sigla): ?>
                                    <?php $nombre = $descripciones[$sigla] ?? $sigla; ?>

                                    <option value="<?= $key ?>">
                                        <?= esc($sigla . ' - ' . $nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Tipo venta</small>
                            <select name="tipo_venta" id="tipoVentaSelect" class="form-control">
                                <option value="">Todos</option>

                                <?php foreach ($tiposVenta as $tv): ?>
                                    <option value="<?= $tv->id ?>">
                                        <?= esc($tv->nombre_tipo_venta) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Fecha emisión</small>
                            <input
                                type="text"
                                name="fecha"
                                id="fechaFiltro"
                                class="form-control"
                                placeholder="dd/mm/yyyy">
                        </div>

                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Correlativo</th>
                            <th class="col-2">Tipo DOC</th>
                            <th class="col-3">Cliente</th>
                            <th>Fecha/Hora</th>
                            <th class="col-1">Condición</th>
                            <th class="col-1">Total</th>
                            <th class="col-1">Saldo</th>
                            <th class="col-1">Estado</th>
                            <th class="col-1">Menú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facturas)): ?>
                            <?php foreach ($facturas as $factura): ?>
                                <tr>
                                    <td class="text-center">
                                        <?= esc(substr($factura->numero_control, -6)) ?>
                                    </td>

                                    <td>
                                        <?php
                                        $siglas = dte_siglas();
                                        $descripciones = dte_descripciones();

                                        $codigo = $factura->tipo_dte;
                                        $sigla = $siglas[$codigo] ?? null;
                                        $descripcion = $sigla ? ($descripciones[$sigla] ?? null) : null;
                                        ?>

                                        <?php if ($sigla && $descripcion): ?>
                                            <span class="badge badge-estado1 bg-info text-white">
                                                <?= esc($sigla) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?= esc($descripcion) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Desconocido</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= esc($factura->cliente_nombre ?? 'Sin cliente') ?>
                                        <div class="text-right">
                                            <small class="text-muted">
                                                Vendedor: <?= esc($factura->vendedor ?? 'Sin vendedor') ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($factura->fecha_emision)) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($factura->hora_emision)) ?>
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                        $condicion = $factura->condicion_operacion ?? 1;

                                        if ($condicion == 1) {
                                            echo '<span class="badge badge-estado bg-success text-white">Contado</span>';
                                        } elseif ($condicion == 2) {
                                            echo '<span class="badge badge-estado bg-warning text-dark">Crédito</span>';
                                        } else {
                                            echo '<span class="badge badge-estado bg-secondary text-white">N/D</span>';
                                        }
                                        ?>
                                    </td>

                                    <td class="text-end">
                                        $ <?= number_format($factura->total_pagar, 2) ?>
                                    </td>

                                    <td class="text-end">
                                        $ <?= number_format($factura->saldo, 2) ?>
                                    </td>

                                    <td class="text-center">

                                        <?php if (($factura->anulada ?? 0) == 1): ?>

                                            <span class="badge badge-estado bg-danger text-white">
                                                Anulado
                                            </span>

                                        <?php elseif (($factura->saldo ?? 0) == 0): ?>

                                            <span class="badge bg-info badge-estado text-white">
                                                <i class="fa-solid fa-check-circle"></i> Pagada
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning badge-estado text-dark">
                                                Activa
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="text-center">
                                        <a href="<?= base_url('facturas/' . $factura->id) ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    No hay facturas registradas
                                </td>
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
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        function cargarFacturas() {

            let clienteId = $('#clienteSelect').val();
            let sellerId = $('#sellerSelect').val();
            let estado = $('[name="estado"]').val();
            let tipo_dte = $('[name="tipo_dte"]').val();
            let fecha = $('#fechaFiltro').val();
            let tipoVenta = $('#tipoVentaSelect').val();
            let numeroFactura = $('#numeroFactura').val();

            if (fecha && fecha.length === 10) {
                let p = fecha.split('/');
                fecha = `${p[2]}-${p[1]}-${p[0]}`;
            }

            const params = new URLSearchParams({
                cliente_id: clienteId || '',
                seller_id: sellerId || '',
                estado: estado || '',
                tipo_dte: tipo_dte || '',
                fecha: fecha || '',
                tipo_venta: tipoVenta || '',
                numero_factura: numeroFactura || ''
            });

            fetch('<?= base_url('facturas') ?>?' + params.toString(), {
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

        // ================= SELECT2 =================

        $('#clienteSelect').select2({
            language: 'es',
            placeholder: 'Buscar cliente...',
            ajax: {
                url: '<?= base_url("clientes/buscar") ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: data => ({
                    results: data
                }),
                cache: true
            }
        });

        $('#sellerSelect').select2({
            language: 'es',
            placeholder: 'Buscar vendedor...',
            minimumInputLength: 2,
            ajax: {
                url: '<?= base_url("sellers/searchAjax") ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term,
                    select2: 1
                }),
                processResults: data => data
            }
        });

        // ================= LISTENERS =================

        $('#clienteSelect, #sellerSelect').on('change', cargarFacturas);
        $('[name="estado"], [name="tipo_dte"]').on('change', cargarFacturas);
        $('#tipoVentaSelect').on('change', cargarFacturas);
        $('#numeroFactura').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
            cargarFacturas();
        });

        // ================= FECHA MASK =================

        const fechaInput = document.getElementById('fechaFiltro');

        fechaInput.addEventListener('input', function() {

            let v = this.value.replace(/\D/g, '');

            if (v.length > 8) v = v.substring(0, 8);

            if (v.length >= 5) {
                this.value = v.substring(0, 2) + '/' + v.substring(2, 4) + '/' + v.substring(4);
            } else if (v.length >= 3) {
                this.value = v.substring(0, 2) + '/' + v.substring(2);
            } else {
                this.value = v;
            }

            if (this.value === '' || this.value.length === 10) {
                cargarFacturas();
            }

        });

    });
    $(document).on('click', '#pagerContainer a', function(e) {

        e.preventDefault();

        const url = $(this).attr('href');

        fetch(url, {
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
</script>
<?= $this->endSection() ?>