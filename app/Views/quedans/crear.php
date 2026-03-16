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

    .badge-estado {
        font-size: 0.75rem;
        padding: 5px 12px;
        border-radius: 10px;
    }
</style>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex">
                <h4 class="header-title">Crear Quedan</h4>
            </div>

            <div class="card-body">

                <form id="formCrearQuedan" method="POST" action="<?= base_url('quedans/guardar') ?>">

                    <div class="row mb-3">

                        <div class="col-md-4">
                            <small class="text-muted">Cliente</small>
                            <select id="clienteSelect" name="cliente_id" class="form-control" required></select>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Número Quedan</small>
                            <input type="text" name="numero_quedan" class="form-control" required>
                        </div>

                        <div class="col-md-2">
                            <small class="text-muted">Fecha emisión</small>
                            <input
                                type="date"
                                name="fecha_emision"
                                class="form-control"
                                value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Fecha de pago</small>
                            <input type="date" name="fecha_pago" class="form-control" required>
                        </div>

                    </div>


                    <hr>

                    <h5 class="mb-3">Facturas pendientes</h5>

                    <div id="clienteResumen" class="mb-3" style="display:none;">

                        <div class="border rounded p-3 bg-light">

                            <div class="row align-items-end">

                                <div class="col-md-3">

                                    <small class="text-muted">
                                        Facturas pendientes
                                    </small>

                                    <div id="cantidadFacturas" class="fw-bold fs-5">
                                        0
                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted">
                                        Saldo total
                                    </small>

                                    <div id="saldoTotalCliente" class="fw-bold text-danger fs-5">
                                        $0.00
                                    </div>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted">
                                        Buscar correlativo
                                    </small>

                                    <input
                                        type="text"
                                        id="buscarCorrelativo"
                                        class="form-control"
                                        placeholder="Ej: 123456">

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted">
                                        Buscar monto
                                    </small>

                                    <input
                                        type="text"
                                        id="buscarMonto"
                                        class="form-control"
                                        placeholder="Ej: 150">

                                </div>

                            </div>

                        </div>

                    </div>

                    <table class="table table-striped table-bordered table-hover">

                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>Factura</th>
                                <th>Fecha</th>
                                <th class="text-center">Días transcurridos</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>

                        <tbody id="tablaFacturas">

                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Seleccione un cliente
                                </td>
                            </tr>

                        </tbody>

                    </table>


                    <div class="row align-items-center mt-3">
                        <div class="col-md-6 text-start small text-muted">
                            Facturas seleccionadas:
                            <strong id="cantidadSeleccionadas">0</strong>
                        </div>
                        <div class="col-md-6 text-right">

                            <h6 class="mb-1 text-muted">Total Quedan</h6>

                            <h4 class="text-primary mb-0">
                                $ <span id="totalQuedan">0.00</span>
                            </h4>

                            <input type="hidden"
                                name="total_aplicado"
                                id="totalAplicadoInput"
                                value="0">

                        </div>

                    </div>

                    <div class="mt-4 text-end">

                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-save"></i> Guardar Quedan
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        let total = 0;
        let facturasCliente = [];
        let facturasSeleccionadas = {};

        function recalcularTotal() {

            let total = 0;
            let count = 0;

            $('.factura-check:checked').each(function() {

                let monto = parseFloat($(this).data('saldo'));
                total += monto;
                count++;

            });

            $('#totalQuedan').text(total.toFixed(2));
            $('#totalAplicadoInput').val(total.toFixed(2));
            $('#cantidadSeleccionadas').text(count);

        }


        // ================= SELECT2 CLIENTES (tu mismo script) =================

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


        // ================= CUANDO CAMBIA CLIENTE =================

        $('#clienteSelect').on('change', function() {

            let clienteId = $(this).val();

            if (!clienteId) return;

            fetch('<?= base_url('quedans/facturas-cliente') ?>/' + clienteId)
                .then(r => r.json())
                .then(data => {

                    facturasCliente = data;

                    renderTabla(data);
                    let saldoTotal = 0;

                    data.forEach(f => {
                        saldoTotal += parseFloat(f.saldo);
                    });

                    $('#clienteResumen').show();
                    $('#cantidadFacturas').text(data.length);
                    $('#saldoTotalCliente').text('$' + saldoTotal.toFixed(2));

                });

        });

        // ================= CHECKBOX FACTURAS =================
        $(document).on('change', '.factura-check', function() {

            let id = $(this).val();

            if ($(this).is(':checked')) {
                facturasSeleccionadas[id] = true;
            } else {
                delete facturasSeleccionadas[id];
            }

            recalcularTotal();

        });

        $(document).on('change', '#checkAll', function() {

            $('.factura-check').each(function() {

                $(this).prop('checked', $('#checkAll').is(':checked'));

            });
            recalcularTotal();

        });

        $('.factura-check').on('change', function() {

            let row = $(this).closest('tr');

            if ($(this).is(':checked')) {
                row.find('input[type="hidden"]').prop('disabled', false);
            } else {
                row.find('input[type="hidden"]').prop('disabled', true);
            }

        });

        function renderTabla(data) {

            let html = '';
            let saldoTotal = 0;

            if (data.length === 0) {

                html = `
        <tr>
            <td colspan="7" class="text-center text-muted">
                No hay resultados
            </td>
        </tr>
        `;

            } else {

                data.sort((a, b) => {

                    let na = parseInt((a.numero_control || '').slice(-6));
                    let nb = parseInt((b.numero_control || '').slice(-6));

                    if (na !== nb) {
                        return na - nb;
                    }

                    return new Date(a.fecha_emision) - new Date(b.fecha_emision);

                });

                data.forEach(f => {

                    let checked = facturasSeleccionadas[f.id] ? 'checked' : '';
                    saldoTotal += parseFloat(f.saldo);

                    let fechaFactura = new Date(f.fecha_emision);
                    let hoy = new Date();

                    let dias = Math.floor((hoy - fechaFactura) / (1000 * 60 * 60 * 24));

                    let estado = f.saldo == 0 ?
                        '<span class="badge bg-success">Pagada</span>' :
                        '<span class="badge bg-warning text-dark">Pendiente</span>';

                    html += `
            <tr>

            <td class="text-center">

                <input type="checkbox"
                class="factura-check"
                name="facturas[${f.id}][id]"
                value="${f.id}"
                data-saldo="${f.saldo}"
                ${checked}>

                <input type="hidden"
                name="facturas[${f.id}][monto]"
                value="${f.saldo}">

            </td>

            <td>${f.numero_control}</td>

            <td class="text-center">
                ${f.fecha_emision}
            </td>

            <td class="text-center ${dias>30?'text-danger fw-bold':''}">
                ${dias}
            </td>

            <td class="text-end">
                $ ${parseFloat(f.total_pagar).toFixed(2)}
            </td>

            <td class="text-end">
                $ ${parseFloat(f.saldo).toFixed(2)}
            </td>

            <td class="text-center">
                ${estado}
            </td>

            </tr>
            `;

                });

            }

            $('#tablaFacturas').html(html);
            recalcularTotal();
        }

        $('#buscarCorrelativo').on('input', function() {

            let valor = $(this).val().toLowerCase().trim();

            if (valor === '') {
                renderTabla(facturasCliente);
                return;
            }

            let filtradas = facturasCliente.filter(f => {

                let numero = (f.numero_control || '').toLowerCase();

                return numero.includes(valor);

            });

            renderTabla(filtradas);

        });

        $('#buscarMonto').on('input', function() {

            let valor = $(this).val().trim();

            if (valor === '') {
                renderTabla(facturasCliente);
                return;
            }

            let filtradas = facturasCliente.filter(f =>
                f.total_pagar.toString().includes(valor)
            );

            renderTabla(filtradas);

        });
        $('#formCrearQuedan').on('submit', function(e) {

            e.preventDefault();

            let cliente = $('#clienteSelect option:selected').text();
            let numeroQuedan = $('input[name="numero_quedan"]').val();
            let fechaEmision = $('input[name="fecha_emision"]').val();
            let fechaPago = $('input[name="fecha_pago"]').val();

            let total = $('#totalQuedan').text();
            let cantidad = $('#cantidadSeleccionadas').text();

            if (cantidad == 0) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Sin facturas',
                    text: 'Debe seleccionar al menos una factura para crear el quedan.'
                });

                return;

            }

            let listaFacturas = [];

            $('.factura-check:checked').each(function() {

                let fila = $(this).closest('tr');

                let numero = fila.find('td:eq(1)').text().trim();
                let monto = $(this).data('saldo');

                listaFacturas.push(`
            <div style="display:flex;justify-content:space-between">
                <span>${numero}</span>
                <strong>$${parseFloat(monto).toFixed(2)}</strong>
            </div>
        `);

            });

            Swal.fire({

                title: 'Confirmar creación de Quedan',

                html: `
        <div style="text-align:left">

            <strong>Cliente:</strong><br>
            ${cliente}<br><br>

            <strong>Número de Quedan:</strong><br>
            ${numeroQuedan}<br><br>

            <strong>Fecha emisión:</strong> ${fechaEmision}<br>
            <strong>Fecha pago:</strong> ${fechaPago}<br><br>

            <strong>Facturas seleccionadas:</strong>
            <div style="max-height:150px;overflow:auto;border:1px solid #eee;padding:8px;margin-top:5px">
                ${listaFacturas.join('')}
            </div>

            <hr>

            <div style="display:flex;justify-content:space-between;font-size:18px">
                <span>Total aplicado</span>
                <strong>$${total}</strong>
            </div>

        </div>
        `,

                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Crear Quedan',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                width: 500

            }).then((result) => {

                if (result.isConfirmed) {

                    $('#formCrearQuedan')[0].submit();

                }

            });

        });
    });
</script>

<?= $this->endSection() ?>