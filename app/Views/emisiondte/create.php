<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    /* Compactar header */
    .form-control-sm-custom {
        height: 30px !important;
        padding: 2px 6px !important;
        font-size: 14px;
    }

    /* labels más pequeños */
    .compact-label {
        font-size: 13px;
        margin-bottom: 2px;
        color: #6c757d;
    }

    /* menos espacio entre filas */
    .row-compact {
        margin-bottom: 8px !important;
    }

    #productosTable input,
    #productosTable .select2-selection {
        border: none !important;
        border-bottom: 1px solid #ccc !important;
    }

    #productosTable input {
        height: 38px;
    }

    .add-line-link {
        color: #007bff;
        cursor: pointer;
    }

    #toggleClienteInfo {
        height: 30px;
        padding: 0 8px;
        /* quitamos padding vertical */
        line-height: 30px;
        /* 🔥 centra el texto verticalmente */
        font-size: 15px;
    }
</style>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex">
                <h5 class="mb-0">
                    <i class="fa fa-file-invoice"></i> Emisión DTE
                </h5>
            </div>

            <div class="card-body">

                <form id="dteForm">

                    <!-- HEADER -->
                    <div class="row row-compact">

                        <div class="col-md-3">
                            <label class="compact-label">Documento</label>
                            <select id="tipoDte" class="form-control form-control-sm-custom">
                                <option value="01">Factura</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="compact-label">Condición</label>
                            <select id="tipoVenta" class="form-control form-control-sm-custom">
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>

                        <!-- CAMPO DINÁMICO -->
                        <div class="col-md-2" id="diasCreditoWrap" style="display:none;">
                            <label class="compact-label">Plazo</label>
                            <select id="diasCredito" class="form-control form-control-sm-custom">
                                <option value="">Seleccione</option>
                                <option value="30">30 días</option>
                                <option value="45">45 días</option>
                                <option value="60">60 días</option>
                                <option value="90">90 días</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="compact-label">Fecha</label>
                            <input type="text" id="fecha" class="form-control form-control-sm-custom" readonly>
                        </div>

                        <div class="col-md-2">
                            <label class="compact-label">Hora</label>
                            <input type="text" id="hora" class="form-control form-control-sm-custom" readonly>
                        </div>

                    </div>

                    <div class="row row-compact">

                        <!-- CLIENTE -->
                        <div class="col-md-5">
                            <label class="compact-label">Cliente</label>
                            <select id="cliente_id" class="form-control form-control-sm-custom"></select>
                        </div>

                        <!-- BOTÓN -->
                        <div class="col-md-3">
                            <label></label>
                            <button type="button"
                                id="toggleClienteInfo"
                                class="btn btn-outline-secondary btn-block"
                                style="display:none; height:28px;">
                                Ver información
                            </button>
                        </div>

                    </div>

                    <!-- 🔥 INFO CLIENTE (OCULTA) -->
                    <div class="row">
                        <div class="col-md-12">
                            <div id="clienteInfo" style="display:none;">

                                <table class="table table-sm table-bordered mt-2" style="font-size:13px;">
                                    <tbody>

                                        <tr>
                                            <th style="width:15%;">Nombre</th>
                                            <td colspan="3"><b id="c_nombre">-</b></td>
                                        </tr>

                                        <tr>
                                            <th id="c_tipo_doc">Doc</th>
                                            <td id="c_num_doc"></td>

                                            <th>NRC</th>
                                            <td id="c_nrc"></td>
                                        </tr>

                                        <tr>
                                            <th>Teléfono</th>
                                            <td id="c_tel"></td>

                                            <th>Correo</th>
                                            <td id="c_correo"></td>
                                        </tr>

                                        <tr>
                                            <th>Dirección</th>
                                            <td colspan="3" id="c_direccion"></td>
                                        </tr>

                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>

                    <div class="row row-compact">

                        <div class="col-md-4">
                            <label class="compact-label">Tipo de modelo</label>
                            <input type="text"
                                class="form-control form-control-sm-custom"
                                value="Modelo de facturación previo"
                                readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="compact-label">Tipo de transmisión</label>
                            <input type="text"
                                class="form-control form-control-sm-custom"
                                value="Transmisión Normal"
                                readonly>
                        </div>

                    </div>

                    <!-- PRODUCTOS -->
                    <table class="table table-sm" id="productosTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Cant</th>
                                <th>Precio</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <a id="addRowBtn" class="add-line-link">
                        + Agregar producto
                    </a>

                    <!-- TOTAL -->
                    <div class="text-right mt-4">
                        <h4>$<span id="totalVenta">0.00</span></h4>
                    </div>

                    <div class="text-right mt-4">
                        <button class="btn btn-success">
                            Generar DTE
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        /* FECHA Y HORA FIJA */
        const now = new Date();

        // 🔹 FORMATO DTE (ISO)
        const fechaISO = now.toISOString().split('T')[0];
        const horaISO = now.toTimeString().slice(0, 8);

        // 🔹 FORMATO HUMANO
        const fechaUI = now.toLocaleDateString('es-SV'); // 08/04/2026
        const horaUI = now.toLocaleTimeString('es-SV'); // 14:32:10

        // pintar en inputs
        document.getElementById('fecha').value = fechaUI;
        document.getElementById('hora').value = horaUI;

        // guardar para DTE
        document.getElementById('fecha').dataset.iso = fechaISO;
        document.getElementById('hora').dataset.iso = horaISO;
        /* FIN FECHA Y HORA FIJA*/
        /* INICIO DE SELECTOR DE CLIENTES */
        $('#cliente_id').select2({
            language: 'es',
            placeholder: 'Buscar cliente...',
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url("clientes/buscarparaDTE") ?>',
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
        //parte 2 del evento cliente, aqui se dibuja el detalle
        $('#cliente_id').on('select2:select', function(e) {

            let c = e.params.data;

            // llenar datos
            document.getElementById('c_nombre').innerText = c.nombre || c.text || '-';
            document.getElementById('c_tipo_doc').innerText = c.tipo_documento || 'Doc';
            document.getElementById('c_num_doc').innerText = c.numero_documento || '';
            document.getElementById('c_nrc').innerText = c.nrc || '-';
            document.getElementById('c_direccion').innerText = c.direccion || '-';
            document.getElementById('c_tel').innerText = c.telefono || '-';
            document.getElementById('c_correo').innerText = c.correo || '-';

            // mostrar botón
            document.getElementById('toggleClienteInfo').style.display = 'inline-block';

            // ocultar tabla al cambiar cliente
            document.getElementById('clienteInfo').style.display = 'none';
        });
        //parte 3 del evento cliente, aqui se oculta el detalle
        const btnToggle = document.getElementById('toggleClienteInfo');
        const info = document.getElementById('clienteInfo');

        btnToggle.addEventListener('click', function() {

            if (info.style.display === 'none') {
                info.style.display = 'block';
                btnToggle.innerText = 'Ocultar información';
            } else {
                info.style.display = 'none';
                btnToggle.innerText = 'Ver información';
            }

        });
        /* FIN DE SELECTOR DE CLIENTES */
        /* INICIO DE SWITCH DE CONDICION DE PAGO */
        const tipoVenta = document.getElementById('tipoVenta');
        const diasWrap = document.getElementById('diasCreditoWrap');

        function toggleCredito() {

            if (tipoVenta.value === 'credito') {
                diasWrap.style.display = 'block';
            } else {
                diasWrap.style.display = 'none';
                document.getElementById('diasCredito').value = '';
            }
        }
        tipoVenta.addEventListener('change', toggleCredito);
        toggleCredito();
        /* FIN DE SWITCH DE CONDICION DE PAGO */
    });
</script>

<?= $this->endSection() ?>