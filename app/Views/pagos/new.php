<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    body {
        overflow-x: hidden !important;
    }

    /* Corrige el padding-right que Bootstrap deja a veces pegado al body */
    body.modal-open {
        padding-right: 0 !important;
    }

    .payment-new-page .card {
        border: 0;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
    }

    .payment-new-page label {
        font-size: .78rem;
        font-weight: 700;
        color: #5f6b7a;
        margin-bottom: .25rem;
    }

    .payment-new-page .form-control,
    .payment-new-page .custom-select {
        font-size: .86rem;
    }

    .payment-section-title {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #7b8794;
        border-bottom: 1px solid #eef1f5;
        padding-bottom: .45rem;
        margin-bottom: 1rem;
    }

    .payment-summary-panel {
        background: #f8fafc;
        border: 1px solid #edf1f5;
        border-radius: 8px;
        padding: 16px;
    }

    .payment-total-input {
        height: 44px;
        font-size: 1.15rem !important;
        background: #fff !important;
    }

    .payment-empty-state {
        padding: 32px 16px !important;
    }

    .payment-table th {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #657184;
        background: #f7f9fc;
        border-bottom: 1px solid #e6ebf1 !important;
    }

    .payment-table td {
        font-size: .84rem;
        vertical-align: middle !important;
    }

    .payment-actions {
        border-top: 1px solid #eef1f5;
        padding-top: 1rem;
    }

    /* Unificar altura Select2 con Bootstrap 4 */

    .select2-container .select2-selection--single {
        height: 38px !important;
        /* altura estándar Bootstrap */
        border: 1px solid #ced4da;
        border-radius: .25rem;
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
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #80bdff;
        box-shadow: 0 0 0 .2rem rgba(0, 123, 255, .25);
    }

    /*Loader en tabla*/
    .loader-row td {
        padding: 25px !important;
    }

    .mini-loader {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
    }

    .mini-spinner {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        border: 2px solid #dee2e6;
        border-top-color: #007bff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }
</style>
<div class="row payment-new-page">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between py-3">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-money-check-dollar mr-2 text-success"></i>Nuevo pago
                </h4>
                <a href="<?= base_url('payments') ?>" class="btn btn-light btn-sm border">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
            <div class="card-body p-4">
                <form id="formPago">
                    <div class="payment-section-title">Datos del pago</div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="clienteSelect">Cliente</label>
                                    <select id="clienteSelect" class="form-control"></select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="fechaPago">Fecha pago</label>
                                    <input type="date" id="fechaPago" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="tipoPago">Tipo de pago</label>
                                    <select class="custom-select" id="tipoPago">
                                        <option value="">Seleccione</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6 d-none" id="boxRecupero">
                                    <label for="descripcionRecupero">Número de recupero / referencia</label>
                                    <input type="text" class="form-control" id="descripcionRecupero" placeholder="Ej: REC-000123">
                                </div>

                                <div class="form-group col-md-6 d-none" id="boxTransferencia">
                                    <label for="cuentaTransferencia">Cuenta bancaria destino</label>
                                    <select class="form-control" id="cuentaTransferencia" style="width:100%"></select>
                                </div>
                            </div>

                            <div class="form-group mb-lg-0">
                                <label for="observacionesPago">Observaciones</label>
                                <textarea id="observacionesPago" class="form-control" rows="2" placeholder="Notas internas del pago"></textarea>
                            </div>
                        </div>

                        <div class="col-lg-4 mt-3 mt-lg-0">
                            <div class="payment-summary-panel h-100">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="mr-2 text-success">
                                        <i class="fa-solid fa-calculator fa-lg"></i>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">Resumen del pago</div>
                                        <small class="text-muted">Se calcula con los montos aplicados.</small>
                                    </div>
                                </div>

                                <label for="totalPago">Total aplicado</label>
                                <input type="text" id="totalPago" class="form-control text-right font-weight-bold payment-total-input" value="0.00" readonly>

                                <div class="d-flex justify-content-between mt-3 small">
                                    <span class="text-muted">Facturas aplicadas</span>
                                    <strong id="facturasAplicadasCount">0</strong>
                                </div>

                                <div class="small text-muted mt-3">
                                    Selecciona un cliente, carga sus facturas pendientes y aplica el monto correspondiente a cada documento.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-section-title mt-4">Facturas pendientes</div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover payment-table">

                            <thead>
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
                                    <td colspan="7" class="text-center text-muted payment-empty-state">
                                        <i class="fa-regular fa-file-lines fa-2x mb-2 d-block text-muted"></i>
                                        <small>Seleccione un cliente para cargar facturas pendientes</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="payment-actions d-flex justify-content-end mt-3">
                        <button type="button" id="btnGuardarPago" class="btn btn-success">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar pago
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>
    <!-- Modal configuración de pago -->
    <div class="modal fade" id="modalPagoFactura">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h6 class="modal-title mb-0" id="tituloModalPago"></h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <label for="modalMonto">Monto a aplicar</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" id="modalMonto" class="form-control text-right">
                    </div>

                    <label for="modalComentario" class="mt-3">Comentario</label>
                    <textarea id="modalComentario" class="form-control" rows="2"></textarea>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success btn-sm" id="guardarMonto">Aplicar</button>

                </div>

            </div>
        </div>
    </div>

    <!-- Modal preview de factura -->
    <div class="modal fade" id="modalFactura">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h6 class="modal-title">Factura</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
        $(document).on('click', '#btnGuardarPago', function(e) {

            e.preventDefault();
            const cliente = $('#clienteSelect').val();
            const fecha = $('#fechaPago').val();
            const tipoPago = $('#tipoPago').val();
            const descRecupero = ($('#descripcionRecupero').val() || '').trim();
            const cuenta = ($('#cuentaTransferencia').val() || '').trim();

            // ================= VALIDACIONES BASE =================

            if (cliente === null || cliente === "" || cliente === undefined) {
                Swal.fire({
                    icon: 'warning',
                    title: '¡Campo Obligatorio!',
                    text: 'Debes seleccionar un cliente para registrar el pago.',
                    confirmButtonText: 'Entendido',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-info'
                    }
                }).then(() => {
                    // Esto abre el select automáticamente después de cerrar la alerta
                    $('#clienteSelect').select2('open');
                });

                return; // Detiene la ejecución
            }

            if (!fecha) {
                Swal.fire('Fecha requerida', 'Seleccione fecha de pago.', 'warning');
                return;
            }

            if (!tipoPago) {
                Swal.fire('Tipo de pago', 'Seleccione tipo de pago.', 'warning');
                return;
            }

            if (tipoPago === 'efectivo' && descRecupero === '') {
                Swal.fire('Efectivo', 'Ingrese número o descripción de recupero.', 'warning');
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

            if (tipoPago === 'efectivo') {
                detallePago = `<p><strong>Recupero:</strong> ${descRecupero}</p>`;
            }

            if (tipoPago === 'transferencia') {
                detallePago = `<p><strong>Cuenta destino:</strong> ${cuenta}</p>`;
            }

            Swal.fire({
                icon: 'question',
                title: 'Confirmar pago',
                html: `
                    <div class="text-left">
                        <p><strong>Facturas:</strong> ${cantidad}</p>
                        <p><strong>Total:</strong> $${total.toFixed(2)}</p>
                        <p><strong>Tipo:</strong> ${tipoPago}</p>
                        ${detallePago}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Confirmar pago',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                customClass: {
                    confirmButton: 'btn btn-success m-2',
                    cancelButton: 'btn btn-secondary m-2'
                },

                preConfirm: () => {

                    let facturas = [];

                    $('.factura-row').each(function() {

                        const monto = parseFloat($(this).find('.aplicarMonto').val()) || 0;

                        if (monto > 0) {
                            facturas.push({
                                factura_id: $(this).find('.btnConfigPago').data('id'),
                                monto: monto,
                                comentario: $(this).find('.comentarioFactura').val() || '',
                                saldo: parseFloat($(this).find('.btnConfigPago').data('saldo')),
                                tipo: $(this).find('.btnConfigPago').data('tipo'),
                                numero: $(this).find('.btnConfigPago').data('numero')
                            });
                        }
                    });

                    const pago = {
                        cliente_id: cliente,
                        fecha_pago: fecha,
                        tipo_pago: tipoPago,
                        recupero: descRecupero,
                        cuenta_bancaria: tipoPago === 'transferencia' ? cuenta : 1,
                        observaciones: $('#observacionesPago').val() || '',
                        total: total,
                        facturas: facturas
                    };

                    return fetch('<?= base_url("payments/store") ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(pago)
                        })
                        .then(async response => {

                            const contentType = response.headers.get("content-type");

                            if (!contentType || !contentType.includes("application/json")) {
                                throw new Error("Respuesta inválida del servidor");
                            }

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Error en servidor');
                            }

                            return data;
                        })
                        .catch(error => {
                            console.error("ERROR BACKEND:", error);
                            Swal.showValidationMessage(`Error: ${error.message}`);
                        });
                }

            }).then(result => {

                if (result.isConfirmed && result.value) {
                    const d = result.value;
                    let html = '';

                    if (d.asiento_error) {
                        html += `<div class="text-warning small mt-1">⚠ Asiento no creado: ${d.asiento_error}</div>`;
                    } else if (d.asientos && d.asientos.length) {
                        html += '<table class="table table-sm table-bordered mt-2" style="font-size:0.82rem">';
                        html += '<thead><tr><th>Factura</th><th>Asiento</th><th class="text-right">Monto</th></tr></thead><tbody>';
                        d.asientos.forEach(a => {
                            if (a.error) {
                                html += `<tr><td>${a.factura}</td><td colspan="2" class="text-danger">${a.error}</td></tr>`;
                            } else {
                                html += `<tr><td>${a.factura}</td><td><strong>${a.asiento}</strong></td><td class="text-right">$ ${parseFloat(a.monto).toFixed(2)}</td></tr>`;
                            }
                        });
                        html += '</tbody></table>';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Pago registrado',
                        html: '<p class="mb-1">Pago registrado correctamente.</p>' + html,
                        width: 500,
                    }).then(() => {
                        window.location = '<?= base_url('payments/') ?>' + d.pago_id;
                    });
                }

            });

        });

        let inputActual = null;
        let comentarioActual = null;

        $(document).on('click', '.btnConfigPago', function() {

            const row = $(this).closest('tr');

            inputActual = row.find('.aplicarMonto');
            comentarioActual = row.find('.comentarioFactura');

            const saldo = $(this).data('saldo');
            const tipo = $(this).data('tipo');
            const numero = $(this).data('numero');

            $('#tituloModalPago').text(`${tipo} #${numero}`);

            $('#modalMonto').attr('max', saldo).val(inputActual.val());
            $('#modalComentario').val(comentarioActual.val());

            $('#modalPagoFactura').modal('show');
        });

        $('#guardarMonto').on('click', function() {
            const monto = parseFloat($('#modalMonto').val()) || 0;
            const saldo = parseFloat($('#modalMonto').attr('max')) || 0;

            if (monto < 0) {
                Swal.fire('Monto inválido', 'El monto no puede ser negativo.', 'warning');
                return;
            }

            if (saldo > 0 && monto > saldo) {
                Swal.fire('Monto inválido', 'El monto aplicado no puede superar el saldo de la factura.', 'warning');
                return;
            }

            inputActual
                .val(monto.toFixed(2))
                .trigger('input');

            comentarioActual.val($('#modalComentario').val());

            $('#modalPagoFactura').modal('hide');
        });

        // ================= CLIENTE SELECT2 =================

        $('#clienteSelect').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar cliente...',
            allowClear: true,
            ajax: {
                url: '<?= base_url("clientes/buscar") ?>',
                dataType: 'json',
                delay: 250,
                data: p => ({
                    q: p.term
                }),
                processResults: function(data) {

                    if (!Array.isArray(data)) {
                        return {
                            results: []
                        };
                    }

                    return {
                        results: data
                    };
                }
            }
        });

        // ================= Ajax para Cuentas =================
        function initCuentaSelect() {
            // Si ya está inicializado, lo destruimos para evitar duplicados
            if ($('#cuentaTransferencia').hasClass("select2-hidden-accessible")) {
                $('#cuentaTransferencia').select2('destroy');
            }

            $('#cuentaTransferencia').select2({
                theme: 'bootstrap4',
                language: 'es',
                placeholder: 'Buscar cuenta bancaria...',
                allowClear: true,
                minimumInputLength: 1,
                width: '100%',
                // ELIMINA dropdownParent si el select NO ESTÁ dentro de un modal
                // O asegúrate de que sea el body si falla
                dropdownParent: $('#boxTransferencia'),
                ajax: {
                    url: '<?= base_url("accounts-search") ?>',
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        q: params.term
                    }),
                    processResults: data => ({
                        results: Array.isArray(data) ? data : []
                    })
                }
            });
        }

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

            if (tipo === 'efectivo') {

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
                    .slideDown(200, function() {
                        initCuentaSelect();
                    });
                $('#boxRecupero')
                    .removeClass('d-none')
                    .hide()
                    .slideDown(200);

                $('#descripcionRecupero')
                    .prop('required', true)
                    .focus();

                $('#cuentaTransferencia').prop('required', true);
            }

        });

        $('#modalFactura').on('hidden.bs.modal', function() {
            $('#facturaPreview').html('');
        });

        // ================= CARGAR FACTURAS =================

        $('#clienteSelect').on('change', function() {
            $('#totalPago').val('0.00');
            $('#facturasAplicadasCount').text('0');
            const clienteId = $(this).val();

            if (!clienteId) {
                $('#facturasContainer').html(`
                    <tr>
                        <td colspan="7" class="text-center text-muted payment-empty-state">
                            <i class="fa-regular fa-file-lines fa-2x mb-2 d-block text-muted"></i>
                            <small>Seleccione un cliente para cargar facturas pendientes</small>
                        </td>
                    </tr>
                `);
                return;
            }

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
                        $('#facturasContainer').html(`
                            <tr>
                                <td colspan="7" class="text-center text-muted payment-empty-state">
                                    <i class="fa-regular fa-circle-check fa-2x mb-2 d-block text-muted"></i>
                                    <small>Este cliente no tiene facturas pendientes.</small>
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    let html = '';

                    data.forEach(f => {
                        let tipoTexto = 'Factura';

                        if (f.tipo_dte === '03') {
                            tipoTexto = 'Crédito fiscal';
                        }
                        if (f.tipo_dte === '01') {
                            tipoTexto = 'Factura Consumidor final';
                        }
                        html += `
                        <tr class="factura-row">
                            <td>
                                ${f.numero_control.substr(-6)}
                                <button type="button"
                                    class="btn btn-link p-0 ml-1 verFactura"
                                    data-id="${f.id}">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </button>
                            </td>
                            <td>${new Date(f.fecha_emision).toLocaleDateString()}</td>
                            <td class="text-center">${diasDeAntiguedad(f.fecha_emision)}</td>
                            <td>${f.vendedor}</td>
                            <td>${f.tipo_venta_nombre}</td>
                            <td class="text-right">
                                $${parseFloat(f.saldo).toFixed(2)}
                                <div class="mt-1">
                                    <span class="badge badge-secondary pagoBadge d-none"></span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                        class="form-control text-right aplicarMonto"
                                        value="0.00"
                                        readonly>
                                    <input type="hidden" class="comentarioFactura">
                                    <div class="input-group-append">
                                        <button type="button"
                                            class="btn btn-outline-primary btnConfigPago"
                                            data-id="${f.id}"
                                            data-saldo="${f.saldo}"
                                            data-tipo="${tipoTexto}"
                                            data-numero="${f.numero_control}">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        `;
                    });
                    $('#facturasContainer').html(html);
                })
                .catch(() => {
                    $('#facturasContainer').html(`
                        <tr>
                            <td colspan="7" class="text-center text-danger payment-empty-state">
                                <i class="fa-solid fa-triangle-exclamation fa-2x mb-2 d-block"></i>
                                <small>No se pudieron cargar las facturas pendientes.</small>
                            </td>
                        </tr>
                    `);
                });
        });

        // ================= SUMA AUTOMATICA =================

        function actualizarTotal() {
            let total = 0;
            let facturasAplicadas = 0;

            $('.aplicarMonto').each(function() {
                const monto = parseFloat($(this).val()) || 0;
                total += monto;
                if (monto > 0) {
                    facturasAplicadas++;
                }
            });

            $('#totalPago').val(
                new Intl.NumberFormat('es-SV', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(total)
            );
            $('#facturasAplicadasCount').text(facturasAplicadas);
            actualizarBadges();
        }

        $(document).on('input', '.aplicarMonto', actualizarTotal);

        $(document).on('click', '.verFactura', function() {

            const id = $(this).data('id');

            $('#facturaPreview').html('Cargando...');
            $('#modalFactura').modal('show');

            $.ajax({
                url: '<?= base_url("facturas/preview") ?>/' + id,
                type: 'GET',
                success: function(html) {
                    $('#facturaPreview').html(html);
                },
                error: function(xhr) {
                    console.error("Error preview:", xhr.responseText);
                    $('#facturaPreview').html('Error al cargar');
                }
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

        //Limpiar modal al cerrarlo para evitar datos pegados

        $('#modalFactura').on('hidden.bs.modal', function() {

            // limpiar contenido
            $('#facturaPreview').html('');

            // eliminar backdrop manual si quedó pegado
            $('.modal-backdrop').remove();

            // quitar clase del body si quedó pegada
            $('body').removeClass('modal-open');

            // restaurar overflow
            $('body').css('padding-right', '');
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
