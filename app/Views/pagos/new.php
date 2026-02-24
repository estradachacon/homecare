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

    /*Loader en tabla*/
    .loader-row td {
        padding: 25px !important;
    }

    .mini-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #6c757d;
    }

    .mini-spinner {
        width: 18px;
        height: 18px;
        border: 2px solid #dee2e6;
        border-top-color: #0d6efd;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }
</style>
<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex">
                <h4 class="header-title">Nuevo pago</h4>
            </div>
            <div class="card-body">
                <form id="formPago">
                    <div class="row">
                        <!-- CLIENTE -->
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Cliente</label>
                            <select id="clienteSelect" class="form-control"></select>
                        </div>

                        <!-- FECHA -->
                        <div class="col-md-3 mb-3">
                            <label class="text-muted">Fecha pago</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="text-muted">Tipo de pago</label>
                            <select class="form-control" id="tipoPago">
                                <option value="">Seleccione</option>
                                <option value="recupero">Recupero</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-none" id="boxRecupero">
                            <label class="text-muted">Numero de recupero</label>
                            <input type="text" class="form-control" id="descripcionRecupero">
                        </div>

                        <div class="col-md-6 d-none" id="boxTransferencia">
                            <label class="text-muted">Cuenta bancaria destino</label>
                            <select class="form-control" id="cuentaTransferencia" style="width:100%"></select>
                        </div>

                    </div>

                    <hr>

                    <!-- FACTURAS -->
                    <h6 class="mb-2">Facturas pendientes</h6>

                    <div class="table-responsive">
                        <table class="table table-bordered">

                            <thead class="table-light">
                                <tr>
                                    <th>Factura</th>
                                    <th>Fecha Doc.</th>
                                    <th>Días Antig.</th>
                                    <th class="col-md-3">Vendedor</th>
                                    <th>Tipo de venta</th>
                                    <th>Saldo</th>
                                    <th class="col-md-2">Set pago</th>
                                </tr>
                            </thead>

                            <tbody id="facturasContainer">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <small>Seleccione un cliente para cargar facturas</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-2">
                            <div style="width:220px">
                                <label class="text-muted mb-1">Total</label>
                                <input type="text" id="totalPago" class="form-control text-end font-weight-bold" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="small text-muted">Observaciones</label>
                        <textarea class="form-control" rows="2"></textarea>
                    </div>
                    <div class="text-end mt-3">

                        <button type="button" class="btn btn-secondary">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-success">
                            Guardar pago
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
    <!-- Modal configuración de pago -->
    <div class="modal fade" id="modalPagoFactura">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">

                <div class="modal-header">
                    <h6 class="modal-title">Aplicar pago</h6>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <label>Monto</label>
                    <input type="number" step="0.01" id="modalMonto" class="form-control">

                </div>

                <div class="modal-footer">

                    <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success btn-sm" id="guardarMonto">Aplicar</button>

                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="modalFactura">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h6 class="modal-title">Factura</h6>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body" id="facturaPreview">
                    Cargando...
                </div>

            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('#formPago').on('submit', function(e) {

            e.preventDefault();

            const cliente = $('#clienteSelect').val();
            const fecha = $('input[type="date"]').val();
            const tipoPago = $('#tipoPago').val();
            const descRecupero = $('#descripcionRecupero').val().trim();
            const cuenta = $('#cuentaTransferencia').val().trim();

            // ================= VALIDACIONES BASE =================

            if (!cliente) {
                Swal.fire('Cliente requerido', 'Seleccione un cliente.', 'warning');
                return;
            }

            if (!fecha) {
                Swal.fire('Fecha requerida', 'Seleccione fecha de pago.', 'warning');
                return;
            }

            if (!tipoPago) {
                Swal.fire('Tipo de pago', 'Seleccione tipo de pago.', 'warning');
                return;
            }

            if (tipoPago === 'recupero' && descRecupero === '') {
                Swal.fire('Recupero', 'Ingrese número o descripción de recupero.', 'warning');
                return;
            }

            if (tipoPago === 'transferencia' && cuenta === '') {
                Swal.fire('Transferencia', 'Ingrese cuenta bancaria destino.', 'warning');
                return;
            }

            // ================= FACTURAS =================

            let total = 0;
            let cantidad = 0;

            $('.aplicarMonto').each(function() {

                let v = parseFloat($(this).val()) || 0;

                if (v > 0) {
                    total += v;
                    cantidad++;
                }

            });

            if (cantidad === 0) {
                Swal.fire('Facturas', 'Debe aplicar al menos una factura.', 'warning');
                return;
            }

            // ================= CONFIRMACION =================

            let detallePago = '';

            if (tipoPago === 'recupero') {
                detallePago = `<p><strong>Recupero:</strong> ${descRecupero}</p>`;
            }

            if (tipoPago === 'transferencia') {
                detallePago = `<p><strong>Cuenta destino:</strong> ${cuenta}</p>`;
            }

            Swal.fire({
                icon: 'question',
                title: 'Confirmar pago',
                html: `
                    <div class="text-start">
                        <p><strong>Facturas:</strong> ${cantidad}</p>
                        <p><strong>Total:</strong> $${total.toFixed(2)}</p>
                        <p><strong>Tipo:</strong> ${tipoPago}</p>
                        ${detallePago}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Confirmar pago',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745'
            });

        });

        let inputActual = null;

        $(document).on('click', '.btnConfigPago', function() {

            inputActual = $(this).closest('.input-group').find('.aplicarMonto');

            const saldo = $(this).data('saldo');

            $('#modalMonto').attr('max', saldo).val(inputActual.val());

            $('#modalPagoFactura').modal('show');
        });

        $('#guardarMonto').on('click', function() {

            inputActual.val(parseFloat($('#modalMonto').val() || 0).toFixed(2)).trigger('input');

            $('#modalPagoFactura').modal('hide');
        });
        // ================= CLIENTE SELECT2 =================

        $('#clienteSelect').select2({
            placeholder: 'Buscar cliente...',
            ajax: {
                url: '<?= base_url("clientes/buscar") ?>',
                dataType: 'json',
                delay: 250,
                data: p => ({
                    q: p.term
                }),
                processResults: data => ({
                    results: data
                })
            }
        });

        // ================= CARGAR TIPO DE PAGO =================

        $('#tipoPago').on('change', function() {

            const tipo = $(this).val();

            // Reset required
            $('#descripcionRecupero, #cuentaTransferencia')
                .prop('required', false)
                .val('');

            // Ocultar con animación
            $('#boxRecupero, #boxTransferencia')
                .stop(true, true)
                .slideUp(150)
                .addClass('d-none');

            if (tipo === 'recupero') {

                $('#boxRecupero')
                    .removeClass('d-none')
                    .hide()
                    .slideDown(200);

                $('#descripcionRecupero')
                    .prop('required', true)
                    .focus();
            }

            if (tipo === 'transferencia') {

                $('#boxTransferencia')
                    .removeClass('d-none')
                    .hide()
                    .slideDown(200);

                $('#cuentaTransferencia')
                    .prop('required', true)
                    .focus();
                $('#boxRecupero')
                    .removeClass('d-none')
                    .hide()
                    .slideDown(200);

                $('#descripcionRecupero')
                    .prop('required', true)
                    .focus();
            }

        });
        // ================= Ajax para Cuentas =================
        $('#cuentaTransferencia').select2({
            language: 'es',
            placeholder: 'Buscar cuenta bancaria...',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url("accounts-search") ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: data => ({
                    results: data
                })
            }
        });

        // ================= CARGAR FACTURAS =================

        $('#clienteSelect').on('change', function() {

            const clienteId = $(this).val();

            if (!clienteId) return;

            $('#facturasContainer').html(`
                <tr class="loader-row">
                    <td colspan="7">
                        <div class="mini-loader">
                            <div class="mini-spinner"></div>
                            <small>Cargando facturas...</small>
                        </div>
                    </td>
                </tr>
                `);

            function diasDeAntiguedad(fecha) {

                const hoy = new Date();
                const f = new Date(fecha);

                const diff = hoy - f;

                return Math.floor(diff / (1000 * 60 * 60 * 24));
            }

            fetch('<?= base_url("payments/facturasPendientes") ?>/' + clienteId)
                .then(r => r.json())
                .then(data => {

                    if (!data.length) {
                        $('#facturasContainer').html('<tr><td colspan="4" class="text-center">Sin facturas pendientes</td></tr>');
                        return;
                    }

                    let html = '';

                    data.forEach(f => {

                        html += `
            <tr class="factura-row">
                <td>
                    ${f.numero_control.substr(-6)}
                    <button type="button"
                        class="btn btn-link p-0 ms-1 verFactura"
                        data-id="${f.id}">
                        <i class="fa-solid fa-eye text-muted"></i>
                    </button>
                </td>
                <td>${new Date(f.created_at).toLocaleDateString()}</td>
                <td class="text-center">${diasDeAntiguedad(f.fecha_emision)}</td>
                <td>${f.vendedor}</td>
                <td>${f.tipo_venta_nombre}</td>
                <td class="text-end">
                    $${parseFloat(f.saldo).toFixed(2)}
                    <div class="mt-1">
                        <span class="badge badge-secondary pagoBadge d-none"></span>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text"
                            class="form-control text-end aplicarMonto"
                            value="0.00"
                            readonly>

                        <div class="input-group-append">
                        <button type="button"
                                class="btn btn-outline-primary btnConfigPago"
                                data-id="${f.id}"
                                data-saldo="${f.saldo}">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            `;
                    });
                    $('#facturasContainer').html(html);
                });
        });

        // ================= SUMA AUTOMATICA =================

        function actualizarTotal() {
            let total = 0;

            $('.aplicarMonto').each(function() {
                total += parseFloat($(this).val()) || 0;
            });

            $('#totalPago').val(
                new Intl.NumberFormat('es-SV', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(total)
            );
            actualizarBadges();
        }

        $(document).on('input', '.aplicarMonto', actualizarTotal);
        $(document).on('click', '.verFactura', function() {

            const id = $(this).data('id');

            $('#facturaPreview').html('Cargando...');

            $('#modalFactura').modal('show');

            fetch('<?= base_url("facturas/preview") ?>/' + id)
                .then(r => r.text())
                .then(html => {
                    $('#facturaPreview').html(html);
                });

        });
        $(document).on('click', '.btnPreviewFactura', function() {

            const id = $(this).data('id');

            $('#modalFacturaBody').html('Cargando...');

            $('#modalFactura').modal('show');

            fetch('<?= base_url("facturas/preview") ?>/' + id)
                .then(r => r.text())
                .then(html => {
                    $('#modalFacturaBody').html(html);
                });

        });

        function actualizarBadges() {

            $('.factura-row').each(function() {

                const saldo = parseFloat($(this).find('.btnConfigPago').data('saldo'));
                const aplicado = parseFloat($(this).find('.aplicarMonto').val()) || 0;

                const badge = $(this).find('.pagoBadge');

                badge.removeClass('badge-success badge-warning d-none');

                if (aplicado === 0) {
                    badge.addClass('d-none');
                    return;
                }

                if (aplicado >= saldo) {
                    badge
                        .addClass('badge-success')
                        .text('Pago total');
                } else {
                    badge
                        .addClass('badge-warning')
                        .text('Pago parcial');
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>