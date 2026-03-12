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
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de pagos</h4>
                <?php if (tienePermiso('crear_pagos')): ?>
                    <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('payments/new') ?>"><i
                            class="fa-solid fa-plus"></i> Nuevo</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form onsubmit="return false" class="mb-3">
                    <div class="row g-2">

                        <div class="col-md-2">
                            <small class="text-muted">Cliente</small>
                            <select id="clienteSelect" class="form-control"></select>
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

                        <div class="col-md-2">
                            <small class="text-muted">Fecha de recepción</small>
                            <input
                                type="text"
                                name="fecha"
                                id="fechaFiltro"
                                class="form-control"
                                placeholder="dd/mm/yyyy">
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Aplicación</small>
                            <select name="tipo_aplicacion" class="form-control">
                                <option value="">Todos</option>
                                <option value="normal">Solo aplicados normales</option>
                                <option value="con_anulaciones">Con facturas anuladas</option>
                                <option value="sin_efecto">Sin efecto (todo anulado)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Factura</small>
                            <input
                                type="text"
                                name="factura"
                                id="facturaFiltro"
                                class="form-control"
                                placeholder="Número de factura">
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha de pago</th>
                            <th>Cliente</th>
                            <th>Forma pago</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                            <th>Menú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pagos)): ?>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td>
                                        <?= esc($pago->id) ?>
                                    </td>

                                    <td>
                                        <?= date('d/m/Y', strtotime($pago->fecha_pago)) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($pago->created_at)) ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= esc($pago->cliente_nombre ?? 'Sin cliente') ?>
                                    </td>

                                    <td>
                                        <?= esc(ucfirst($pago->forma_pago)) ?>
                                    </td>

                                    <td class="text-end">

                                        <div class="fw-semibold fs-6">
                                            $<?= number_format($pago->total, 2) ?>
                                        </div>

                                        <?php if ($pago->total_anulado > 0): ?>

                                            <div class="small text-muted mt-1">
                                                Anulado:
                                                <span class="fw-semibold">
                                                    - $<?= number_format($pago->total_anulado, 2) ?>
                                                </span>
                                            </div>

                                            <div class="small mt-1">
                                                <span class="text-muted">Total efectivo:</span>
                                                <span class="fw-bold">
                                                    $<?= number_format($pago->total_aplicado, 2) ?>
                                                </span>
                                            </div>

                                        <?php endif; ?>

                                    </td>

                                    <td class="text-center">

                                        <div class="d-flex flex-column align-items-center gap-1 text-white">

                                            <?php if ($pago->anulado): ?>

                                                <span class="badge bg-danger w-100 mb-1">
                                                    <i class="fa-solid fa-ban me-1"></i> Anulado
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-success w-100 mb-1">
                                                    <i class="fa-solid fa-check me-1"></i> Aplicado
                                                </span>

                                                <?php if (!empty($pago->total_anulado) && $pago->total_anulado > 0): ?>

                                                    <span class="badge bg-warning text-dark w-100">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                                        Con facturas anuladas
                                                    </span>

                                                <?php endif; ?>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                    <td class="text-center">
                                        <a href="<?= base_url('payments/' . $pago->id) ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay pagos registrados
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div id="pagerContainer" class="d-flex justify-content-center mt-3">
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

        function cargarPagos() {

            let clienteId = $('#clienteSelect').val();
            let estado = $('[name="estado"]').val();
            let fecha = $('#fechaFiltro').val();
            let tipoAplicacion = $('[name="tipo_aplicacion"]').val();
            let factura = $('#facturaFiltro').val();

            if (fecha && fecha.length === 10) {
                let p = fecha.split('/');
                fecha = `${p[2]}-${p[1]}-${p[0]}`;
            }

            const params = new URLSearchParams({
                cliente_id: clienteId || '',
                estado: estado || '',
                fecha: fecha || '',
                tipo_aplicacion: tipoAplicacion || '',
                factura: factura || ''
            });

            fetch('<?= base_url('payments') ?>?' + params.toString(), {
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

        // ================= LISTENERS =================

        $('#clienteSelect').on('change', cargarPagos);
        $('[name="estado"]').on('change', cargarPagos);
        $('[name="tipo_aplicacion"]').on('change', cargarPagos);
        $('#facturaFiltro').on('input', function() {
            cargarPagos();
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
                cargarPagos();
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