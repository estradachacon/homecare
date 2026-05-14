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

    .payment-mobile-date {
        display: none;
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

    @media (max-width: 767.98px) {
        .payment-new-page .card-body {
            padding: 1rem !important;
        }

        .payment-new-page .card-header {
            gap: .75rem;
            align-items: flex-start;
        }

        .payment-new-page .header-title {
            font-size: 1.1rem;
            line-height: 1.25;
        }

        .payment-summary-panel {
            padding: 12px;
        }

        .payment-table-wrap {
            overflow: visible;
            width: 100%;
        }

        .payment-table {
            border-collapse: separate;
            border-spacing: 0 .7rem;
            display: block;
            width: 100%;
        }

        .payment-table thead {
            display: none;
        }

        .payment-table tbody {
            display: block;
            width: 100%;
        }

        .payment-table tbody tr.factura-row {
            display: block;
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem;
            background: #fff;
            box-shadow: 0 .125rem .45rem rgba(15, 23, 42, .06);
        }

        .payment-table tbody tr.factura-row td {
            display: block;
            width: 100%;
            border: 0;
            padding: .18rem 0;
        }

        .payment-table .payment-invoice-cell {
            display: flex !important;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
        }

        .payment-table .payment-invoice-main {
            min-width: 0;
        }

        .payment-table .payment-invoice-number {
            font-weight: 700;
            color: #1f2d3d;
        }

        .payment-mobile-date {
            display: block;
            margin-top: .15rem;
            line-height: 1.2;
        }

        .payment-table .payment-date-cell,
        .payment-table .payment-age-cell {
            display: none !important;
        }

        .payment-table .payment-seller-cell,
        .payment-table .payment-type-cell,
        .payment-table .payment-balance-cell {
            display: flex !important;
            justify-content: space-between;
            gap: .75rem;
            font-size: .88rem;
        }

        .payment-table .payment-seller-cell::before,
        .payment-table .payment-type-cell::before,
        .payment-table .payment-balance-cell::before {
            content: attr(data-label);
            flex: 0 0 auto;
            color: #6c757d;
            font-size: .76rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .payment-table .payment-seller-cell span,
        .payment-table .payment-type-cell span {
            min-width: 0;
            text-align: right;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .payment-table .payment-balance-cell {
            align-items: flex-start;
            margin-top: .25rem;
        }

        .payment-table .payment-balance-content {
            text-align: right;
        }

        .payment-table .payment-apply-cell {
            margin-top: .55rem;
            padding-top: .65rem !important;
            border-top: 1px solid #eef1f5;
        }

        .payment-table .payment-apply-cell::before {
            content: attr(data-label);
            display: block;
            color: #6c757d;
            font-size: .76rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: .25rem;
        }

        .payment-table .payment-apply-cell .input-group {
            width: 100%;
        }

        .payment-table .payment-empty-state {
            padding: 28px 12px !important;
        }

        .payment-actions {
            justify-content: stretch !important;
        }

        .payment-actions .btn {
            width: 100%;
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
                            <!-- Banner recuperos activos -->
                            <div id="alertaRecuperos" class="d-none mb-3"></div>

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
                                <div class="d-flex mb-3">
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

                    <div class="table-responsive payment-table-wrap">
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

    <!-- Modal recuperos activos -->
    <div class="modal fade" id="modalRecuperos" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header py-2" style="background:#1e3a5f;">
                    <h6 class="modal-title text-white mb-0">
                        <i class="fa-solid fa-wallet mr-2"></i>Recuperos activos del cliente
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="cuerpoModalRecuperos">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="recargarRecuperos()">
                        <i class="fa-solid fa-rotate-right mr-1"></i>Re-consultar
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAplicarRecupero">
                        <i class="fa-solid fa-link mr-1"></i>Vincular selección
                    </button>
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
    // ── Variables y funciones de recuperos en scope global (son llamadas desde onclick dinámico) ──
    let recuperoSeleccionado = null;
    let clienteIdActual      = null;
    let recuperosData        = [];

    function mostrarBannerRecuperos() {
        const fc = { efectivo:'Efectivo', cheque:'Cheque', transferencia:'Transferencia', deposito:'Depósito' };
        if (recuperoSeleccionado) {
            $('#alertaRecuperos').removeClass('d-none').html(`
                <div class="alert alert-success py-2 mb-0 d-flex justify-content-between">
                    <span class="small">
                        <i class="fa-solid fa-circle-check mr-1"></i>
                        Recupero vinculado: <strong>${recuperoSeleccionado.numero_recupero}</strong>
                        <span class="badge badge-secondary mx-1">${fc[recuperoSeleccionado.forma_cobro] ?? recuperoSeleccionado.forma_cobro}</span>
                        $${parseFloat(recuperoSeleccionado.total).toFixed(2)}
                    </span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-warning mr-1" onclick="abrirModalRecuperos()">Cambiar</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quitarRecupero()" title="Quitar recupero">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>`);
        } else if (recuperosData.length) {
            $('#alertaRecuperos').removeClass('d-none').html(`
                <div class="alert alert-warning py-2 mb-0 d-flex justify-content-between">
                    <span class="small">
                        <i class="fa-solid fa-wallet mr-1"></i>
                        Este cliente tiene <strong>${recuperosData.length}</strong> recupero(s) activo(s) sin aplicar
                    </span>
                    <button type="button" class="btn btn-sm btn-warning" onclick="abrirModalRecuperos()">
                        <i class="fa-solid fa-list mr-1"></i>Ver recuperos
                    </button>
                </div>`);
        } else {
            $('#alertaRecuperos').addClass('d-none').html('');
        }
    }

    function abrirModalRecuperos() {
        const fc = { efectivo:'Efectivo', cheque:'Cheque', transferencia:'Transferencia', deposito:'Depósito bancario' };
        let html = '<p class="small text-muted mb-3">Selecciona el recupero que respalda este pago. Es opcional: el pago funciona sin recupero vinculado.</p>';

        if (!recuperosData.length) {
            html += '<div class="text-center text-muted py-3"><i class="fa-solid fa-circle-check fa-2x mb-2 d-block text-success"></i>No hay recuperos activos para este cliente.</div>';
        } else {
            html += '<div class="list-group">';
            recuperosData.forEach(r => {
                const checked  = recuperoSeleccionado?.id == r.id ? 'checked' : '';
                const active   = recuperoSeleccionado?.id == r.id ? 'active'  : '';
                const fechaFmt = r.fecha ? r.fecha.substring(0, 10).split('-').reverse().join('/') : '—';
                html += `
                    <label class="list-group-item list-group-item-action py-2 ${active}" for="rec_${r.id}" style="cursor:pointer;">
                        <div class="d-flex align-items-start">
                            <input type="radio" name="recuperoRadio" id="rec_${r.id}"
                                   value="${r.id}" class="mr-2 mt-1" ${checked}>
                            <div>
                                <strong>${r.numero_recupero}</strong>
                                <span class="badge badge-secondary ml-1">${fc[r.forma_cobro] ?? r.forma_cobro}</span>
                                <div class="small text-muted mt-1">
                                    ${fechaFmt} &mdash; Total: <strong class="text-dark">$${parseFloat(r.total).toFixed(2)}</strong>
                                    ${r.referencia ? ' &mdash; Ref: ' + r.referencia : ''}
                                </div>
                            </div>
                        </div>
                    </label>`;
            });
            html += '</div>';
        }
        $('#cuerpoModalRecuperos').html(html);
        $('#modalRecuperos').modal('show');
    }

    function recargarRecuperos() {
        if (!clienteIdActual) return;
        $('#cuerpoModalRecuperos').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>');
        fetch('<?= base_url('recuperos/activos-cliente/') ?>' + clienteIdActual)
            .then(r => r.json())
            .then(data => { recuperosData = data; abrirModalRecuperos(); });
    }

    function quitarRecupero() {
        recuperoSeleccionado = null;
        // Desbloquear filas que fueron bloqueadas por el recupero
        $('[data-bloqueada-por-recupero="1"]').each(function() {
            const row = $(this);
            row.find('.aplicarMonto').prop('readonly', false).css({ background: '', cursor: '' });
            row.find('.btnConfigPago')
               .prop('disabled', false)
               .removeClass('btn-secondary')
               .addClass('btn-outline-primary')
               .attr('title', '')
               .html('<i class="fa-solid fa-pen"></i>');
            row.removeAttr('data-bloqueada-por-recupero');
        });
        // Limpiar montos y comentarios que puso el recupero
        $('.aplicarMonto').val('0.00').trigger('input');
        $('.comentarioFactura').val('');
        mostrarBannerRecuperos();
    }

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

            if (recuperoSeleccionado) {
                detallePago += `<p class="text-success mb-0"><i class="fa-solid fa-link mr-1"></i><strong>Recupero vinculado:</strong> ${recuperoSeleccionado.numero_recupero} ($${parseFloat(recuperoSeleccionado.total).toFixed(2)})</p>`;
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
                        cliente_id:   cliente,
                        fecha_pago:   fecha,
                        tipo_pago:    tipoPago,
                        recupero:     descRecupero,
                        recupero_id:  recuperoSeleccionado ? recuperoSeleccionado.id : null,
                        cuenta_bancaria: tipoPago === 'transferencia' ? cuenta : 1,
                        observaciones: $('#observacionesPago').val() || '',
                        total:    total,
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

        // ================= RECUPEROS =================

        function cargarRecuperos(clienteId) {
            fetch('<?= base_url('recuperos/activos-cliente/') ?>' + clienteId)
                .then(r => r.json())
                .then(data => {
                    recuperosData = data;
                    if (!data.length) {
                        $('#alertaRecuperos').addClass('d-none').html('');
                        return;
                    }
                    mostrarBannerRecuperos();
                })
                .catch(() => { /* silencioso — recuperos son opcionales */ });
        }

        $('#btnAplicarRecupero').on('click', function() {
            const checked = $('input[name="recuperoRadio"]:checked');
            if (!checked.length) {
                Swal.fire('Sin selección', 'Selecciona un recupero o cancela.', 'warning');
                return;
            }
            const id = parseInt(checked.val());
            recuperoSeleccionado = recuperosData.find(r => r.id == id) ?? null;
            $('#modalRecuperos').modal('hide');
            mostrarBannerRecuperos();

            // ── Precargar montos desde el recupero ───────────────────
            if (recuperoSeleccionado?.detalles?.length) {
                // Limpiar todos los montos primero
                $('.aplicarMonto').val('0.00').trigger('input');

                let aplicadas = 0;
                recuperoSeleccionado.detalles.forEach(d => {
                    const btn = $(`.btnConfigPago[data-id="${d.factura_id}"]`);
                    if (!btn.length) return; // factura no está en la tabla (ya pagada u otro motivo)

                    const row   = btn.closest('tr');
                    const input = row.find('.aplicarMonto');
                    const saldo = parseFloat(btn.data('saldo')) || 0;
                    // No aplicar más del saldo pendiente actual
                    const monto = Math.min(parseFloat(d.monto_aplicado) || 0, saldo);
                    if (monto > 0) {
                        input.val(monto.toFixed(2)).trigger('input');
                        row.find('.comentarioFactura').val('Recupero ' + recuperoSeleccionado.numero_recupero);
                        // Bloquear edición — el monto viene del recupero
                        input.prop('readonly', true).css({ background: '#e9ecef', cursor: 'not-allowed' });
                        btn.prop('disabled', true)
                           .removeClass('btn-outline-primary')
                           .addClass('btn-secondary')
                           .attr('title', 'Monto fijado por recupero ' + recuperoSeleccionado.numero_recupero)
                           .html('<i class="fa-solid fa-lock"></i>');
                        row.attr('data-bloqueada-por-recupero', '1');
                        aplicadas++;
                    }
                });

                if (aplicadas === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin facturas coincidentes',
                        text:  'Las facturas del recupero ya no tienen saldo pendiente o no están en la lista actual.',
                        confirmButtonText: 'Entendido'
                    });
                }
            }

            // Auto-rellenar campo de texto de recupero si está visible
            if (recuperoSeleccionado && $('#descripcionRecupero').is(':visible')) {
                $('#descripcionRecupero').val(recuperoSeleccionado.numero_recupero);
            }
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

            // Reset recupero al cambiar de cliente
            recuperoSeleccionado = null;
            recuperosData        = [];
            clienteIdActual      = clienteId || null;
            $('#alertaRecuperos').addClass('d-none').html('');

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

            // Cargar recuperos activos en paralelo
            cargarRecuperos(clienteId);

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
                        const fechaTexto = new Date(f.fecha_emision).toLocaleDateString();
                        const diasAntiguedad = diasDeAntiguedad(f.fecha_emision);

                        if (f.tipo_dte === '03') {
                            tipoTexto = 'Crédito fiscal';
                        }
                        if (f.tipo_dte === '01') {
                            tipoTexto = 'Factura Consumidor final';
                        }
                        html += `
                        <tr class="factura-row">
                            <td class="payment-invoice-cell" data-label="Factura">
                                <div class="payment-invoice-main">
                                    <span class="payment-invoice-number">${f.numero_control.substr(-6)}</span>
                                    <small class="text-muted payment-mobile-date">${fechaTexto} · ${diasAntiguedad} dias</small>
                                </div>
                                <button type="button"
                                    class="btn btn-link p-0 ml-1 verFactura"
                                    data-id="${f.id}">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </button>
                            </td>
                            <td class="payment-date-cell" data-label="Fecha">${fechaTexto}</td>
                            <td class="text-center payment-age-cell" data-label="Dias">${diasAntiguedad}</td>
                            <td class="payment-seller-cell" data-label="Vendedor"><span>${f.vendedor ?? ''}</span></td>
                            <td class="payment-type-cell" data-label="Tipo venta"><span>${f.tipo_venta_nombre ?? ''}</span></td>
                            <td class="text-right payment-balance-cell" data-label="Saldo">
                                <div class="payment-balance-content">
                                    $${parseFloat(f.saldo).toFixed(2)}
                                    <div class="mt-1">
                                        <span class="badge badge-secondary pagoBadge d-none"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="payment-apply-cell" data-label="Set pago">
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
