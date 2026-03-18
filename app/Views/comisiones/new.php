<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style id="tabla-pro">
    #tabla_documentos table {
        font-size: 12px;
        /* texto más pequeño */
    }

    #tabla_documentos th,
    #tabla_documentos td {
        padding: 4px 6px;
        /* menos espacio interno */
        vertical-align: middle;
    }

    #tabla_documentos td {
        line-height: 1.2;
    }

    /* 🔥 inputs y selects más compactos */
    #tabla_documentos select {
        padding: 2px 4px;
        font-size: 12px;
        height: auto;
    }

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

    .origen-comision {
        font-size: 11px;
        padding: 6px 4px;
        white-space: nowrap;
    }

    .popover-comision {
        position: absolute;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 8px;
        min-width: 160px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .popover-comision button {
        width: 100%;
        margin-bottom: 4px;
        font-size: 12px;
    }

    .popover-comision button:last-child {
        margin-bottom: 0;
    }

    .popover-comision {
        animation: fadeIn 0.15s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
<div class="card shadow-sm">
    <div class="card-header">
        <h4>Generar Comisión</h4>
    </div>

    <div class="card-body">

        <div class="row">

            <!-- VENDEDOR -->
            <div class="col-md-4">
                <label>Vendedor</label>
                <select id="seller_id" class="form-control seller-select"></select>
            </div>

            <!-- FECHAS -->
            <div class="col-md-3">
                <label>Desde</label>
                <input type="date" id="fecha_inicio" class="form-control">
            </div>

            <div class="col-md-3">
                <label>Hasta</label>
                <input type="date" id="fecha_fin" class="form-control">
            </div>

            <!-- BOTÓN -->
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-success w-100" onclick="generarComision()">
                    Generar
                </button>
            </div>

        </div>

    </div>
</div>
<div class="mt-4" id="tabla_documentos" style="display:none;">
    <div class="card">
        <div class="card-header">
            <h5>Documentos encontrados</h5>
        </div>
        <div class="card-body">

            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th style="white-space: nowrap;">Fecha</th>
                        <th>Tipo</th>
                        <th>Doc</th>
                        <th style="width: 240px;">Cliente</th>
                        <th style="white-space: nowrap;">Código</th>
                        <th>Descripción</th>
                        <th>Cant.</th>
                        <th>Costo</th>
                        <th>Precio</th>
                        <th>Venta</th>
                        <th style="width: 225px;">%</th>
                        <th>Comisión</th>
                    </tr>
                </thead>
                <tbody id="tbody_docs">
                    <tr>
                        <td colspan="4" class="text-center">Sin datos</td>
                    </tr>
                </tbody>
            </table>


            <table class="table table-sm table-bordered mb-3">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">%</th>
                    </tr>
                </thead>
                <tbody id="resumen_tipos">
                    <tr>
                        <td colspan="3" class="text-center">Sin datos</td>
                    </tr>
                </tbody>
            </table>

            <h6>Estadisticas</h6>

            <table class="table table-sm table-bordered mb-3">
                <thead>
                    <tr>
                        <th>Tipo venta</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">%</th>
                    </tr>
                </thead>
                <tbody id="resumen_tipo_venta">
                    <tr>
                        <td colspan="3" class="text-center">Sin datos</td>
                    </tr>
                </tbody>
            </table>

            <div class="row">
                <div class="col-md-6">
                    <p><b>Total ventas:</b> $<span id="totalVentas">0.00</span></p>
                </div>
                <div class="col-md-6 text-end">
                    <p><b>Total comisión:</b> $<span id="totalComision">0.000000</span></p>
                    <p><b>% Comisión:</b> <span id="porcentajeComision">0.00</span>%</p>
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn-success" onclick="confirmarGeneracion()">
                    Confirmar Comisión
                </button>
            </div>

        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        let vendedorAnterior = null;

        $('#seller_id').select2({
            placeholder: 'Buscar vendedor...',
            minimumInputLength: 2,
            width: '100%',
            ajax: {
                url: "<?= base_url('sellers/searchAjax') ?>",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        select2: 1
                    };
                },
                processResults: function(data) {
                    return data;
                }
            }
        });
        let hoy = new Date();

        let inicio = primerDiaMes(hoy);
        let fin = ultimoDiaMes(hoy);

        $('#fecha_inicio').val(formatoInputDate(inicio));
        $('#fecha_fin').val(formatoInputDate(fin));
        $('#fecha_inicio').on('change', function() {

            let valor = $(this).val();
            if (!valor) return;

            let partes = valor.split('-');

            let fechaInicio = new Date(
                partes[0],
                partes[1] - 1,
                partes[2]
            );

            let ultimo = ultimoDiaMes(fechaInicio);

            $('#fecha_fin').val(formatoInputDate(ultimo));
        });
        $('#seller_id').on('change', function() {

            let nuevo = $(this).val();

            // si no había nada antes, solo guarda y sigue
            if (!vendedorAnterior) {
                vendedorAnterior = nuevo;
                return;
            }

            // si no hay tabla cargada, solo cambia sin problema
            let hayDatos = document.querySelectorAll('#tbody_docs tr').length > 0 &&
                document.getElementById('tabla_documentos').style.display !== 'none';

            if (!hayDatos) {
                vendedorAnterior = nuevo;
                return;
            }

            // ALERTA
            Swal.fire({
                title: 'Cambiar vendedor',
                text: 'Se perderán los datos cargados. ¿Deseas continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {

                if (result.isConfirmed) {

                    // limpiar todo
                    limpiarTabla();

                    vendedorAnterior = nuevo;

                } else {

                    // revertir selección
                    $('#seller_id').val(vendedorAnterior).trigger('change.select2');

                }

            });

        });
    });
    document.addEventListener('click', function(e) {

        // CERRAR SI HACE CLICK AFUERA
        if (!e.target.closest('.popover-comision') && !e.target.classList.contains('badge-comision-btn')) {
            document.querySelectorAll('.popover-comision').forEach(p => p.remove());
        }

        // ABRIR POPOVER
        let btn = e.target.closest('.badge-comision-btn');

        if (btn) {
            let row = btn.closest('tr');

            // eliminar otros abiertos
            document.querySelectorAll('.popover-comision').forEach(p => p.remove());

            let opciones = JSON.parse(row.dataset.opciones || '[]');

            let pop = document.createElement('div');
            pop.className = 'popover-comision';

            opciones.forEach(op => {
                let b = document.createElement('button');

                b.className = 'btn btn-sm btn-outline-' + getColor(op.tipo);
                b.innerText = op.tipo.toUpperCase() + ' - ' + op.porcentaje + '%';

                b.onclick = function() {

                    let input = row.querySelector('.porcentaje');
                    input.value = op.porcentaje;

                    btn.innerHTML = `
                    ${op.tipo.toUpperCase()}<br>
                    <small>${op.porcentaje}%</small>
                `;

                    btn.className = 'badge badge-' + getColor(op.tipo) + ' w-100 badge-comision-btn';
                    btn.dataset.tipo = op.tipo;
                    btn.dataset.base = op.porcentaje;

                    let venta = parseFloat(
                        row.querySelector('.venta').innerText.replace(/[^\d.-]/g, '')
                    ) || 0;

                    let comision = venta * (op.porcentaje / 100);
                    row.querySelector('.comision').innerText = comision.toFixed(6);

                    calcularTotales();

                    pop.remove();
                };

                pop.appendChild(b);
            });

            document.body.appendChild(pop);

            // POSICIÓN (debajo del botón)
            let rect = btn.getBoundingClientRect();

            pop.style.top = (rect.bottom + window.scrollY) + 'px';
            pop.style.left = (rect.left + window.scrollX) + 'px';
        }
    });
    document.addEventListener('input', function(e) {

        if (e.target.classList.contains('porcentaje')) {

            let input = e.target;
            let row = input.closest('tr');


            let venta = parseFloat(
                row.querySelector('.venta').innerText.replace(/[^\d.-]/g, '')
            ) || 0;

            let porcentaje = parseFloat(input.value) || 0;
            let comision = venta * (porcentaje / 100);

            row.querySelector('.comision').innerText = comision.toFixed(6);

            // BADGE DINÁMICO
            let btn = row.querySelector('.badge-comision-btn');
            let opciones = JSON.parse(row.dataset.opciones || '[]');

            // ordenar de menor a mayor
            opciones.sort((a, b) => a.porcentaje - b.porcentaje);

            let menorIgual = null;
            let mayor = null;

            opciones.forEach(op => {
                if (porcentaje >= op.porcentaje) {
                    menorIgual = op;
                }
                if (porcentaje < op.porcentaje && !mayor) {
                    mayor = op;
                }
            });

            // LÓGICA FINAL
            if (menorIgual && porcentaje === menorIgual.porcentaje) {

                btn.innerHTML = `
                    ${menorIgual.tipo.toUpperCase()}<br>
                    <small>${porcentaje}%</small>
                `;
                btn.className = 'badge badge-' + getColor(menorIgual.tipo) + ' w-100 badge-comision-btn';

                btn.dataset.tipo = menorIgual.tipo;
                btn.dataset.base = menorIgual.porcentaje;

            } else if (menorIgual) {

                btn.innerHTML = `
                    ↑ ${menorIgual.tipo.toUpperCase()}<br>
                    <small>${porcentaje}%</small>
                `;
                btn.className = 'badge badge-info w-100 badge-comision-btn';

            } else if (mayor) {

                btn.innerHTML = `
                    ↓ ${mayor.tipo.toUpperCase()}<br>
                    <small>${porcentaje}%</small>
                `;
                btn.className = 'badge badge-danger w-100 badge-comision-btn';
            }

            calcularTotales();
        }

    });

    function limpiarTabla() {

        // limpiar tabla
        document.getElementById('tbody_docs').innerHTML = `
        <tr>
            <td colspan="4" class="text-center">Sin datos</td>
        </tr>
    `;

        // ocultar bloque
        document.getElementById('tabla_documentos').style.display = 'none';

        // limpiar totales
        document.getElementById('totalVentas').innerText = '0.00';
        document.getElementById('totalComision').innerText = '0.000000';
        document.getElementById('porcentajeComision').innerText = '0.00';

        // limpiar resumen
        document.getElementById('resumen_tipos').innerHTML = `
        <tr><td colspan="3" class="text-center">Sin datos</td></tr>
    `;

        document.getElementById('resumen_tipo_venta').innerHTML = `
        <tr><td colspan="3" class="text-center">Sin datos</td></tr>
    `;
    }

    function calcularTotales() {

        let totalVentas = 0;
        let totalComision = 0;

        let resumen = {};
        let resumenTipoVenta = {};

        // cálculo automático inicial
        document.querySelectorAll('.porcentaje').forEach(input => {
            let row = input.closest('tr');

            let venta = parseFloat(
                row.querySelector('.venta').innerText.replace(/[^\d.-]/g, '')
            ) || 0;

            let porcentaje = parseFloat(input.value) || 0;

            let comision = venta * (porcentaje / 100);

            row.querySelector('.comision').innerText = comision.toFixed(6);
        });

        document.querySelectorAll('#tbody_docs tr').forEach(row => {

            let venta = parseFloat(
                row.querySelector('.venta').innerText.replace(/[^\d.-]/g, '')
            ) || 0;

            let comision = parseFloat(
                row.querySelector('.comision').innerText
            ) || 0;

            let tipo = row.children[2].innerText;
            let tipoVenta = row.dataset.tipoVenta || 'N/A';

            totalVentas += venta;
            totalComision += comision;

            // por tipo documento
            if (!resumen[tipo]) resumen[tipo] = 0;
            resumen[tipo] += venta;

            // por tipo venta
            if (!resumenTipoVenta[tipoVenta]) resumenTipoVenta[tipoVenta] = 0;
            resumenTipoVenta[tipoVenta] += venta;

        });

        // TABLA 1: tipo documento
        let html = "";

        for (let tipo in resumen) {

            let totalTipo = resumen[tipo];

            let porcentaje = totalVentas !== 0 ?
                (totalTipo / totalVentas) * 100 :
                0;

            html += `
            <tr>
                <td>${tipo}</td>
                <td class="text-end">$ ${formatoNumero(totalTipo, 2)}
                <td class="text-end">${porcentaje.toFixed(2)}%</td>
            </tr>
        `;
        }

        document.getElementById('resumen_tipos').innerHTML = html;

        // TABLA 2: tipo venta
        let htmlTipoVenta = "";

        for (let tipo in resumenTipoVenta) {

            let total = resumenTipoVenta[tipo];

            let porcentaje = totalVentas !== 0 ?
                (total / totalVentas) * 100 :
                0;

            htmlTipoVenta += `
            <tr>
                <td>${tipo}</td>
                <td class="text-end">$ ${formatoNumero(total, 2)}
                <td class="text-end">${porcentaje.toFixed(2)}%</td>
            </tr>
        `;
        }

        document.getElementById('resumen_tipo_venta').innerHTML = htmlTipoVenta;

        // TOTALES
        document.getElementById('totalVentas').innerText = formatoNumero(totalVentas, 2);
        document.getElementById('totalComision').innerText = formatoNumero(totalComision, 6);

        let porcentajeComision = totalVentas !== 0 ?
            (totalComision / totalVentas) * 100 :
            0;

        document.getElementById('porcentajeComision').innerText =
            porcentajeComision.toFixed(2);
    }

    function formatoFechaSV(fecha) {
        let d = new Date(fecha);

        return d.toLocaleDateString('es-SV', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function ultimos6(doc) {
        return doc ? doc.slice(-6) : '';
    }

    function formatoNumero(num, decimales = 6) {

        return parseFloat(num || 0).toLocaleString('en-US', {
            minimumFractionDigits: decimales,
            maximumFractionDigits: decimales
        });

    }

    function entero(num) {
        return parseInt(num || 0);
    }

    function primerDiaMes(fecha) {
        let d = new Date(fecha);
        return new Date(d.getFullYear(), d.getMonth(), 1);
    }

    function ultimoDiaMes(fecha) {
        let d = new Date(fecha);
        return new Date(d.getFullYear(), d.getMonth() + 1, 0);
    }

    function formatoInputDate(date) {
        return date.toISOString().split('T')[0];
    }

    function getColor(tipo) {
        switch (tipo) {
            case 'producto':
                return 'success';
            case 'vendedor':
                return 'primary';
            case 'general':
                return 'secondary';
            default:
                return 'dark';
        }
    }

    function generarComision() {

        let seller = $('#seller_id').val();
        let sellerText = $('#seller_id').select2('data')[0]?.text;
        let inicio = $('#fecha_inicio').val();
        let fin = $('#fecha_fin').val();

        if (!seller) {
            Swal.fire('Error', 'Debe seleccionar un vendedor', 'warning');
            return;
        }

        if (!inicio || !fin) {
            Swal.fire('Error', 'Debe seleccionar rango de fechas', 'warning');
            return;
        }

        // CAMBIO AQUÍ
        Swal.fire({
            title: '¿Cargar documentos?',
            html: `
            <b>Vendedor:</b> ${sellerText}<br>
            <b>Desde:</b> ${inicio}<br>
            <b>Hasta:</b> ${fin}
        `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, cargar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (!result.isConfirmed) return;

            // loader
            Swal.fire({
                title: 'Cargando documentos...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch("<?= base_url('comisiones/getDocumentos') ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        seller_id: seller,
                        fecha_inicio: inicio,
                        fecha_fin: fin
                    })
                })
                .then(res => res.text())
                .then(text => {

                    console.log("RESPUESTA CRUDA:", text);

                    let data;
                    let porcentajeDefault;
                    let porcentajeVendedor;

                    try {
                        let response = JSON.parse(text);

                        data = response.documentos;
                        porcentajeDefault = parseFloat(response.porcentaje_default) || 0;
                        porcentajeVendedor = response.porcentaje_vendedor !== null ?
                            parseFloat(response.porcentaje_vendedor) :
                            null;

                    } catch (e) {
                        console.error("NO ES JSON:", text);
                        Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
                        return;
                    }

                    // AQUÍ YA USAS data
                    Swal.close();

                    let html = "";

                    data.forEach((row, i) => {

                        console.log("TIPO VENTA:", row.tipo_venta);

                        let venta = parseFloat(row.venta_gravada || 0);

                        // PRIORIDAD: producto > vendedor > general
                        let opciones = [];

                        // GENERAL
                        if (porcentajeDefault !== null) {
                            opciones.push({
                                tipo: 'general',
                                porcentaje: porcentajeDefault
                            });
                        }

                        // VENDEDOR
                        if (porcentajeVendedor !== null) {
                            opciones.push({
                                tipo: 'vendedor',
                                porcentaje: porcentajeVendedor
                            });
                        }

                        // PRODUCTO (cuando lo tengas desde backend)
                        if (row.producto_porcentaje) {
                            opciones.push({
                                tipo: 'producto',
                                porcentaje: row.producto_porcentaje
                            });
                        }

                        // ORDENAR POR PRIORIDAD
                        const prioridad = {
                            producto: 1,
                            vendedor: 2,
                            general: 3
                        };

                        opciones.sort((a, b) => prioridad[a.tipo] - prioridad[b.tipo]);

                        let esNotaCredito = row.tipo === 'NCE';
                        let seleccion = opciones[0];

                        let porcentajeInicial = seleccion.porcentaje;
                        let tipoInicial = seleccion.tipo;
                        if (esNotaCredito) {
                            venta = venta * -1;
                        }

                        let opcionesHTML = '';

                        opciones.forEach(op => {
                            opcionesHTML += `
                                <a class="dropdown-item opcion-comision"
                                href="#"
                                data-porcentaje="${op.porcentaje}"
                                data-tipo="${op.tipo}">
                                    ${op.tipo.toUpperCase()} (${op.porcentaje}%)
                                </a>
                            `;
                        });

                        let clase = esNotaCredito ? 'table-danger' : '';

                        html += `
                            <tr class="${clase}" 
                                data-tipo-venta="${row.tipo_venta ?? 'N/A'}"
                                data-opciones='${JSON.stringify(opciones)}'>
                                
                                <td>${i + 1}</td>

                                <td>${formatoFechaSV(row.fecha_emision)}</td>

                                <td>${row.tipo}</td>

                                <td>${ultimos6(row.numero_control)}</td>

                                <td>${row.cliente ?? ''}</td>

                                <td style="white-space: nowrap;">
                                    ${row.codigo}
                                </td>

                                <td>${row.descripcion}</td>

                                <td class="text-center">
                                    ${entero(row.cantidad)}
                                </td>

                            <td class="text-end text-muted">
                                Pendiente
                            </td>

                                <td class="text-end">
                                    $ ${formatoNumero(row.precio_unitario, 6)}
                                </td>

                                <td class="text-end venta">
                                    $ ${formatoNumero(venta, 6)}
                                </td>

                            <td>
                                <div class="row g-2">

                                    <div class="col-6">
                                        <input 
                                            type="number" 
                                            class="form-control porcentaje" 
                                            step="0.01" 
                                            min="0"
                                            value="${porcentajeInicial}"
                                            data-default="${porcentajeDefault}"
                                        >
                                    </div>

                                    <div class="col-6">
                            <button class="badge badge-${getColor(tipoInicial)} w-100 badge-comision-btn"
                                    data-tipo="${tipoInicial}"
                                    data-base="${porcentajeInicial}"
                                    style="font-size:11px; cursor:pointer;">
                                ${tipoInicial.toUpperCase()}<br>
                                <small>${porcentajeInicial}%</small>
                            </button>
                                    </div>

                                </div>
                            </td>

                                <td class="comision text-end">0.000000</td>
                            </tr>
                            `;
                    });

                    document.getElementById('tbody_docs').innerHTML = html;
                    document.getElementById('tabla_documentos').style.display = 'block';
                    calcularTotales();

                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'No se pudo cargar', 'error');
                });
        });
    }
</script>

<?= $this->endSection() ?>