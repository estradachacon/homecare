<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .select2-container .select2-selection--single {
        height: 40px !important;
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
</style>
<div class="row">
    <div class="col-md-12">

        <div class="card">
            <div class="card-header">
                <h4>Nuevo pago a proveedor</h4>
            </div>

            <div class="card-body">
                <form id="formPagoCompra">

                    <div class="row">

                        <!-- PROVEEDOR -->
                        <div class="col-md-4 mb-3">
                            <label class="text-muted">Proveedor</label>
                            <select id="proveedorSelect" class="form-control"></select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="text-muted">Fecha pago</label>
                            <input type="date" id="fechaPago" class="form-control">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="text-muted">Tipo de pago</label>
                            <select class="form-control" id="tipoPago">
                                <option value="">Seleccione</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-none" id="boxReferencia">
                            <label class="text-muted">Referencia</label>
                            <input type="text" id="referencia" class="form-control">
                        </div>

                        <div class="col-md-6 d-none" id="boxCuenta">
                            <label class="text-muted">Cuenta bancaria destino</label>
                            <select class="form-control" id="cuentaTransferencia" style="width:100%"></select>
                        </div>

                    </div>

                    <hr>

                    <h6>Compras pendientes</h6>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th class="col-md-2">Fecha</th>
                                <th class="col-md-2">Saldo</th>
                                <th class="col-md-5">Aplicar</th>
                            </tr>
                        </thead>
                        <tbody id="comprasContainer">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Seleccione proveedor
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-end">
                        <label>Total</label>
                        <input type="text" id="totalPago" class="form-control text-end" readonly>
                    </div>

                    <div class="mt-3">
                        <textarea id="observaciones" class="form-control" placeholder="Observaciones"></textarea>
                    </div>

                    <div class="text-end mt-3">
                        <button class="btn btn-success" id="btnGuardarPago">
                            Guardar pago
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="modalCompra">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h6 class="modal-title">Compra</h6>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="compraPreview">
                Cargando...
            </div>

        </div>
    </div>
</div>
<script>
    $(function() {
        $(document).on('click', '.verCompra', function() {

            const id = $(this).data('id');

            $('#compraPreview').html('Cargando...');
            $('#modalCompra').modal('show');

            $.ajax({
                url: '<?= base_url("compras/preview") ?>/' + id,
                type: 'GET',
                success: function(html) {
                    $('#compraPreview').html(html);
                },
                error: function() {
                    $('#compraPreview').html('Error al cargar compra');
                }
            });

        });
        // ================= PROVEEDOR SELECT2 =================
        $('#proveedorSelect').select2({
            placeholder: 'Buscar proveedor...',
            minimumInputLength: 2,
            ajax: {
                url: '<?= base_url("proveedores/searchAjax") ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term,
                    select2: 1
                }),
                processResults: data => data
            }
        });

        // ================= CARGAR COMPRAS =================
        $('#proveedorSelect').on('change', function() {

            let proveedorId = $(this).val();

            if (!proveedorId) return;

            $('#comprasContainer').html(`
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Cargando compras...
                    </td>
                </tr>
            `);

            fetch('<?= base_url("compraspagos/comprasPendientes") ?>/' + proveedorId)
                .then(r => r.json())
                .then(data => {

                    if (!data.length) {
                        $('#comprasContainer').html(`
                                <tr>
                                    <td colspan="4" class="text-center">
                                        Sin compras pendientes
                                    </td>
                                </tr>
                            `);
                        return;
                    }

                    let html = '';

                    data.forEach(c => {

                        html += `
                        <tr class="compra-row">
                            <td>
                                ${c.numero}
                                <button type="button"
                                    class="btn btn-link p-0 ms-1 verCompra"
                                    data-id="${c.id}">
                                    <i class="fa-solid fa-eye text-muted"></i>
                                </button>
                            </td>
                            <td>${c.fecha}</td>
                            <td class="text-end">$${parseFloat(c.saldo).toFixed(2)}</td>
                            <td>
                                <input type="number"
                                    class="form-control aplicarMonto text-end"
                                    data-id="${c.id}"
                                    data-saldo="${c.saldo}"
                                    value="0.00"
                                    step="0.01">

                                <div class="mt-1">
                                    <span class="badge badge-secondary pagoBadge d-none"></span>
                                </div>
                            </td>
                        </tr>
                        `;
                    });

                    $('#comprasContainer').html(html);
                    actualizarTotal();

                })
                .catch(err => {
                    console.error(err);
                    $('#comprasContainer').html(`
                        <tr>
                            <td colspan="4" class="text-center text-danger">
                                Error al cargar compras
                            </td>
                        </tr>
                    `);
                });

        });
        // ================= MOSTRAR / OCULTAR CAMPOS SEGÚN TIPO PAGO =================
        $('#tipoPago').on('change', function() {

            const tipo = $(this).val();

            // Reset
            $('#referencia, #cuentaTransferencia')
                .prop('required', false)
                .val('');

            // Ocultar ambos
            $('#boxReferencia, #boxCuenta')
                .stop(true, true)
                .slideUp(150)
                .addClass('d-none');

            if (tipo === 'efectivo') {

                // Solo referencia
                $('#boxReferencia')
                    .removeClass('d-none')
                    .hide()
                    .slideDown(200);

                $('#referencia')
                    .prop('required', true)
                    .focus();
            }

            if (tipo === 'transferencia') {

                // Mostrar ambos
                $('#boxReferencia')
                    .removeClass('d-none')
                    .hide()
                    .slideDown(200);

                $('#boxCuenta')
                    .removeClass('d-none')
                    .hide()
                    .slideDown(200, function() {
                        initCuentaSelect(); // inicializa select2 aquí
                    });

                $('#referencia').prop('required', true);
                $('#cuentaTransferencia').prop('required', true);
            }

        });

        // ================= TOTAL =================
        $('#modalCompra').on('hidden.bs.modal', function() {
            $('#compraPreview').html('');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        });

        function initCuentaSelect() {

            if ($('#cuentaTransferencia').hasClass("select2-hidden-accessible")) {
                $('#cuentaTransferencia').select2('destroy');
            }

            $('#cuentaTransferencia').select2({
                language: 'es',
                minimumInputLength: 1,
                placeholder: 'Buscar cuenta...',
                width: '100%',
                dropdownParent: $('#boxCuenta'),
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

        function actualizarTotal() {

            let total = 0;

            $('.aplicarMonto').each(function() {
                total += parseFloat($(this).val()) || 0;
            });

            $('#totalPago').val(total.toFixed(2));

            actualizarBadges();

        }

        function actualizarBadges() {

            $('.compra-row').each(function() {

                const saldo = parseFloat($(this).find('.aplicarMonto').data('saldo'));
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
        $(document).on('input', '.aplicarMonto', function() {

            let saldo = parseFloat($(this).data('saldo')) || 0;
            let valor = parseFloat($(this).val()) || 0;

            if (valor > saldo) {
                $(this).val(saldo.toFixed(2));
                valor = saldo;
            }

            actualizarTotal();
        });

        $('#totalPago').val(
            new Intl.NumberFormat('es-SV', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(total)
        );
        
        $('#btnGuardarPago').on('click', function(e) {

            e.preventDefault();

            const proveedor = $('#proveedorSelect').val();
            const fecha = $('#fechaPago').val();
            const tipoPago = $('#tipoPago').val();
            const referencia = $('#referencia').val();
            const cuenta = $('#cuentaTransferencia').val();

            let compras = [];
            let total = 0;

            $('.compra-row').each(function() {

                let monto = parseFloat($(this).find('.aplicarMonto').val()) || 0;

                if (monto > 0) {

                    compras.push({
                        compra_id: $(this).find('.aplicarMonto').data('id'),
                        monto: monto
                    });

                    total += monto;
                }
            });

            if (tipoPago === 'efectivo' && !referencia) {
                Swal.fire('Efectivo', 'Ingrese referencia', 'warning');
                return;
            }

            if (tipoPago === 'transferencia' && !cuenta) {
                Swal.fire('Transferencia', 'Seleccione cuenta bancaria', 'warning');
                return;
            }

            let detalle = `
                <p><strong>Compras:</strong> ${compras.length}</p>
                <p><strong>Total:</strong> $${total.toFixed(2)}</p>
                <p><strong>Tipo:</strong> ${tipoPago}</p>
            `;

            if (tipoPago === 'transferencia') {
                detalle += `<p><strong>Cuenta:</strong> ${cuenta}</p>`;
            }

            if (tipoPago === 'efectivo') {
                detalle += `<p><strong>Referencia:</strong> ${referencia}</p>`;
            }

            Swal.fire({
                title: 'Confirmar pago',
                html: `<div class="text-start">${detalle}</div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-success m-2',
                    cancelButton: 'btn btn-secondary m-2'
                }
            }).then(result => {

                if (!result.isConfirmed) return;

                const payload = {
                    proveedor_id: proveedor,
                    fecha_pago: fecha,
                    forma_pago: tipoPago,
                    referencia: referencia,
                    numero_cuenta_bancaria: tipoPago === 'transferencia' ? cuenta : null,
                    observaciones: $('#observaciones').val(),
                    total: total,
                    compras: compras
                };

                fetch('<?= base_url("compraspagos/store") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pago registrado'
                        }).then(() => location.reload());
                    });

            });

        });
    });
</script>

<?= $this->endSection() ?>