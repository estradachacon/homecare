<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .select2-container .select2-selection--single {
        height: 34px !important;
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: .75rem;
        font-size: 13px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }

    .compact-label {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 2px;
        display: block;
    }

    .form-control-sm-dte {
        font-size: 13px;
        height: 34px;
        padding: 4px 8px;
    }

    #productosTable {
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    #productosTable thead th {
        background: #f1f3f5;
        border-top: 0;
        border-bottom: 1px solid #d7dce1;
        color: #495057;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #productosTable th,
    #productosTable td {
        border-color: #e9ecef;
    }

    #productosBody tr:nth-child(even) {
        background: #fbfcfd;
    }

    #productosBody tr:hover {
        background: #f8fafc;
    }

    #productosBody td {
        vertical-align: top;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    #productosBody input,
    #productosBody textarea {
        margin-top: 0;
        border-color: #d8dee4;
        font-size: 14px;
    }

    #productosBody .desc-input {
        resize: vertical;
        min-height: 34px;
    }

    #productosBody .num-cell {
        color: #6c757d;
        font-size: 14px;
        line-height: 1;
    }

    #productosBody .remove-row {
        width: 22px;
        height: 22px;
        padding: 0;
        border: 0;
        color: #adb5bd;
        background: transparent;
        line-height: 22px;
    }

    #productosBody .remove-row:hover {
        color: #dc3545;
        background: #fff5f5;
    }

    #productosBody .money-cell {
        color: #343a40;
        font-size: 14px;
        padding-top: 14px;
        font-variant-numeric: tabular-nums;
    }

    .total-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px 20px;
    }

    .badge-estado-mh {
        font-size: 13px;
        padding: 6px 12px;
    }

    .cliente-popup-in {
        animation: clientePopupIn .22s ease-out both;
    }

    .cliente-popup-out {
        animation: clientePopupOut .18s ease-in both;
    }

    @keyframes clientePopupIn {
        from {
            opacity: 0;
            transform: translateY(-12px) scale(.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes clientePopupOut {
        from {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        to {
            opacity: 0;
            transform: translateY(-8px) scale(.98);
        }
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0"><i class="fa-solid fa-file-invoice me-1"></i> Emisión DTE</h5>
                <a href="<?= base_url('emision-dte') ?>" class="btn btn-sm btn-secondary">
                    <i class="fa-solid fa-list me-1"></i> Ver emitidos
                </a>
            </div>

            <div class="card-body">
                <form id="dteForm">

                    <!-- ═══ ENCABEZADO ═══ -->
                    <div class="row g-2 mb-3">

                        <div class="col-md-3">
                            <label class="compact-label">Tipo de documento</label>
                            <select id="tipoDte" class="form-control form-control-sm-dte">
                                <option value="01">Factura (01)</option>
                                <option value="03">Crédito Fiscal (03)</option>
                                <option value="04">Nota de Remisión (04)</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="compact-label">Condición de pago</label>
                            <select id="condicionPago" class="form-control form-control-sm-dte">
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>

                        <div class="col-md-2" id="plazoCreditoWrap" style="display:none;">
                            <label class="compact-label">Plazo</label>
                            <select id="plazoCredito" class="form-control form-control-sm-dte">
                                <option value="">Seleccione</option>
                                <option value="30">30 días</option>
                                <option value="45">45 días</option>
                                <option value="60">60 días</option>
                                <option value="90">90 días</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="compact-label">Fecha emisión</label>
                            <input type="text" id="fechaDisplay" class="form-control form-control-sm-dte" readonly>
                            <input type="hidden" id="fechaIso">
                        </div>

                        <div class="col-md-2">
                            <label class="compact-label">Hora emisión</label>
                            <input type="text" id="horaDisplay" class="form-control form-control-sm-dte" readonly>
                            <input type="hidden" id="horaIso">
                        </div>

                    </div>

                    <!-- ═══ CLIENTE ═══ -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <label class="compact-label">Cliente</label>
                            <select id="clienteId" class="form-control form-control-sm-dte" style="width:100%;"></select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="btnToggleCliente" class="btn btn-sm btn-outline-secondary w-100" style="display:none; height:34px;">
                                Ver datos
                            </button>
                        </div>
                    </div>

                    <!-- Info cliente colapsable -->
                    <div id="clienteInfo" class="mb-3" style="display:none;">
                        <table class="table table-sm table-bordered" style="font-size:12px; width:auto; max-width:600px;">
                            <tbody>
                                <tr>
                                    <th style="width:100px">Nombre</th>
                                    <td colspan="3"><b id="c_nombre">-</b></td>
                                </tr>
                                <tr>
                                    <th id="c_tipo_doc_lbl">Documento</th>
                                    <td id="c_num_doc">-</td>
                                    <th>NRC</th>
                                    <td id="c_nrc">-</td>
                                </tr>
                                <tr>
                                    <th>Teléfono</th>
                                    <td id="c_tel">-</td>
                                    <th>Correo</th>
                                    <td id="c_correo">-</td>
                                </tr>
                                <tr>
                                    <th>Dirección</th>
                                    <td colspan="3" id="c_direccion">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="compact-label">Agregar producto</label>
                            <select id="productoQuick" class="form-control form-control-sm-dte" style="width:100%;"></select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="compact-label">Agregar servicio</label>
                            <select id="servicioQuick" class="form-control form-control-sm-dte" style="width:100%;"></select>
                        </div>
                    </div>
                    <!-- ═══ PRODUCTOS ═══ -->
                    <div class="table-responsive mb-2">
                        <table class="table table-sm mb-0" id="productosTable">
                            <thead>
                                <tr>
                                    <th style="width:48px">#</th>
                                    <th style="min-width:260px">Descripción</th>
                                    <th style="width:100px">Cant.</th>
                                    <th id="thPrecioUnitario" style="width:90px">P/Unit (sin IVA)</th>
                                    <th style="width:75px">Descuento</th>
                                    <th style="width:85px" class="text-end">Gravado</th>
                                    <th id="thIva" style="width:75px" class="text-end">IVA 13%</th>
                                    <th style="width:85px" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="productosBody"></tbody>
                        </table>
                    </div>

                    <!-- ═══ TOTALES ═══ -->
                    <div class="row justify-content-end mb-3">
                        <div class="col-md-4 total-box">
                            <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                                <span class="text-muted">Subtotal gravado:</span>
                                <span id="lblTotalGravada">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                                <span class="text-muted">IVA (13%):</span>
                                <span id="lblTotalIva">$0.00</span>
                            </div>
                            <hr class="my-1">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total a pagar:</span>
                                <span id="lblTotalPagar" class="fw-bold fs-5 text-success">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <textarea
                            class="form-control form-control-sm desc-input auto-expand"
                            name="items[__IDX__][descripcion]"
                            rows="2"
                            placeholder="Notas:"></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success" id="btnEmitir">
                            <i class="fa-solid fa-paper-plane me-1"></i> Emitir y guardar
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template de fila de producto -->
<template id="rowTemplate">
    <tr data-row="__IDX__">

        <td class="text-center num-cell">
            <div class="num-item mb-1"></div>
            <button type="button" class="remove-row" title="Quitar linea">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <input type="hidden" class="tipo-item" name="items[__IDX__][tipo_item]" value="1">
            <input type="hidden" class="producto-id">
            <input type="hidden" class="producto-codigo">
        </td>

        <!-- Descripción con textarea -->
        <td>
            <textarea
                class="form-control form-control-sm desc-input auto-expand"
                name="items[__IDX__][descripcion]"
                rows="1"
                placeholder="Descripción"></textarea>
        </td>

        <td>
            <input type="number"
                class="form-control form-control-sm text-end qty-input"
                name="items[__IDX__][cantidad]"
                value="1"
                min="1"
                step="1">
        </td>

        <td>
            <input type="number"
                class="form-control form-control-sm text-end price-input"
                name="items[__IDX__][precio_uni]"
                value="0.00"
                min="0"
                step="0.01">
        </td>

        <td>
            <input type="number"
                class="form-control form-control-sm text-end descu-input"
                name="items[__IDX__][descuento]"
                value="0.00"
                min="0"
                step="0.01">
        </td>

        <td class="text-end gravada-cell money-cell">0.00</td>
        <td class="text-end iva-cell money-cell">0.00</td>
        <td class="text-end total-cell money-cell fw-bold">0.00</td>

    </tr>
</template>

<script>
    $(function() {

        // ──────────────────────────────────────────────
        //  FECHA Y HORA
        // ──────────────────────────────────────────────
        const fechaIso = '<?= esc($fecha_sv ?? date('Y-m-d')) ?>';
        const horaIso = '<?= esc($hora_sv ?? date('H:i:s')) ?>';
        const now = new Date(`${fechaIso}T${horaIso}`);

        $('#fechaDisplay').val(now.toLocaleDateString('es-SV'));
        $('#horaDisplay').val(now.toLocaleTimeString('es-SV'));
        $('#fechaIso').val(fechaIso);
        $('#horaIso').val(horaIso);

        let clienteSeleccionado = null;
        const actividadesMap = <?= json_encode(config('ActividadesEconomicas')->actividades) ?>;

        function clienteTieneNrc() {
            return !!String(clienteSeleccionado?.nrc || '').trim();
        }

        function clienteTieneGiro() {
            return !!String(clienteSeleccionado?.cod_actividad || '').trim() &&
                !!String(clienteSeleccionado?.desc_actividad || '').trim();
        }

        function alertarClienteSinNrc() {
            Swal.fire(
                'Cliente no apto para CCF',
                'Para emitir Credito Fiscal el cliente debe tener NRC registrado. Actualice la ficha del cliente o emita Factura Consumidor Final.',
                'warning'
            );
        }

        function actualizarEstadoBotonEmitir(avisar = false) {
            const requiereNrc = $('#tipoDte').val() === '03' && $('#clienteId').val();
            const bloquear = requiereNrc && !clienteTieneNrc();

            $('#btnEmitir')
                .prop('disabled', bloquear)
                .attr('title', bloquear ? 'El cliente seleccionado no tiene NRC para emitir CCF.' : '');

            if (bloquear && avisar) {
                alertarClienteSinNrc();
            }
        }

        function buildOpcionesActividades() {
            return Object.entries(actividadesMap)
                .map(([codigo, descripcion]) => {
                    const texto = `${codigo} - ${descripcion}`;
                    return `<option value="${escapeHtml(codigo)}">${escapeHtml(texto)}</option>`;
                })
                .join('');
        }

        function solicitarGiroCliente() {
            if (!clienteSeleccionado || !clienteTieneNrc() || clienteTieneGiro()) {
                return;
            }

            Swal.fire({
                title: 'Completar giro',
                html: `
                    <div class="text-start" style="font-size:14px;">
                        <p class="mb-3">El cliente tiene NRC, pero no tiene giro registrado. Puede incluirlo ahora sin detener la emision.</p>
                        <label class="compact-label">Giro</label>
                        <select id="clienteGiroSelect" class="form-control" style="width:100%;">
                            <option value="">Seleccione...</option>
                            ${buildOpcionesActividades()}
                        </select>
                    </div>
                `,
                icon: 'info',
                width: '46rem',
                showCancelButton: true,
                confirmButtonText: 'Guardar giro',
                cancelButtonText: 'Omitir por ahora',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary m-2',
                    cancelButton: 'btn btn-secondary m-2',
                },
                showClass: {
                    popup: 'cliente-popup-in'
                },
                hideClass: {
                    popup: 'cliente-popup-out'
                },
                didOpen: () => {
                    $('#clienteGiroSelect').select2({
                        placeholder: 'Buscar por codigo o nombre...',
                        width: '100%',
                        dropdownParent: $('.swal2-popup'),
                    });
                },
                preConfirm: () => {
                    const codActividad = $('#clienteGiroSelect').val();

                    if (!codActividad) {
                        Swal.showValidationMessage('Seleccione un giro para guardar.');
                        return false;
                    }

                    return fetch(`<?= base_url('clientes/actualizar-giro') ?>/${clienteSeleccionado.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new URLSearchParams({
                                cod_actividad: codActividad,
                            }),
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'No se pudo guardar el giro.');
                            }

                            return data;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message || 'No se pudo guardar el giro.');
                            return false;
                        });
                },
            }).then(result => {
                if (!result.isConfirmed || !result.value?.success) {
                    return;
                }

                clienteSeleccionado.cod_actividad = result.value.cod_actividad;
                clienteSeleccionado.desc_actividad = result.value.desc_actividad;

                Swal.fire('Giro actualizado', 'El giro del cliente fue guardado correctamente.', 'success');
            });
        }

        $('#tipoDte').on('change', function() {
            if ($(this).val() === '03' && $('#clienteId').val() && !clienteTieneNrc()) {
                alertarClienteSinNrc();
            }

            actualizarTituloPrecio();
            actualizarEstadoBotonEmitir();
            aplicarModoDTE(); // 🔥 ESTA ES LA CLAVE
        });
        actualizarTituloPrecio();
        aplicarModoDTE();
        actualizarEstadoBotonEmitir();

        // ──────────────────────────────────────────────
        //  PLAZO CRÉDITO
        // ──────────────────────────────────────────────
        $('#condicionPago').on('change', function() {
            if ($(this).val() === 'credito') {
                $('#plazoCreditoWrap').show();
            } else {
                $('#plazoCreditoWrap').hide();
                $('#plazoCredito').val('');
            }
        });

        // ──────────────────────────────────────────────
        //  CLIENTE SELECT2
        // ──────────────────────────────────────────────
        $('#clienteId').select2({
            language: 'es',
            placeholder: 'Buscar cliente...',
            minimumInputLength: 1,
            width: '100%',
            dropdownParent: $('body'),
            ajax: {
                url: '<?= base_url("clientes/buscarparaDTE") ?>',
                dataType: 'json',
                delay: 250,
                data: p => ({
                    q: p.term
                }),
                processResults: data => ({
                    results: Array.isArray(data) ? data : []
                }),
            },
        });
        $('#servicioQuick').on('select2:select', function(e) {
            const servicio = e.params.data;

            addRowFromServicio(servicio);

            $('#servicioQuick').val(null).trigger('change');
        });
        $('#productoQuick').select2({
            language: 'es',
            placeholder: 'Buscar producto y agregar...',
            minimumInputLength: 1,
            width: '100%',
            dropdownParent: $('body'),
            ajax: {
                url: '<?= base_url("productos/searchAjax") ?>',
                dataType: 'json',
                delay: 250,
                data: p => ({
                    q: p.term
                }),
                processResults: data => ({
                    results: (data.results || []).filter(r => r.tipo == '1')
                }),
            },
        });

function addRowFromServicio(servicio) {
    rowIdx++;

    const tpl = document.getElementById('rowTemplate').innerHTML.replace(/__IDX__/g, rowIdx);
    const $row = $(tpl);

    $('#productosBody').append($row);

    $row.find('.tipo-item').val('2');
    $row.find('.producto-id').val(servicio.id || '');
    $row.find('.producto-codigo').val(servicio.codigo || '');
    $row.find('.desc-input').val(servicio.text);

    if (servicio.precio) {
        $row.find('.price-input').val(servicio.precio);
    }

    if ($('#tipoDte').val() === '01') {
        $row.find('.iva-cell').hide();
    }

    actualizarNumeros();

    // 🔥 IGUAL QUE addRowFromProducto
    calcularFila($row);

    requestAnimationFrame(() => {
        $row.find('.auto-expand').each(function() {
            autoExpand(this);
        });
    });

    setTimeout(() => {
        $row.find('.qty-input').focus().select();
    }, 100);
}
        $('#clienteId').on('select2:select', function(e) {
            const c = e.params.data;
            clienteSeleccionado = c;
            $('#c_nombre').text(c.nombre || c.text || '-');
            $('#c_tipo_doc_lbl').text(c.tipo_documento || 'Doc');
            $('#c_num_doc').text(c.numero_documento || '-');
            $('#c_nrc').text(c.nrc || '-');
            $('#c_direccion').text(c.direccion || '-');
            $('#c_tel').text(c.telefono || '-');
            $('#c_correo').text(c.correo || '-');
            $('#btnToggleCliente').show();
            $('#clienteInfo').hide();
            $('#btnToggleCliente').text('Ver datos');

            actualizarEstadoBotonEmitir(true);
            solicitarGiroCliente();
        });

        $('#btnToggleCliente').on('click', function() {
            if (!clienteSeleccionado) {
                Swal.fire('Cliente requerido', 'Seleccione un cliente para ver sus datos.', 'warning');
                return;
            }

            const c = clienteSeleccionado;
            const nombre = escapeHtml(c.nombre || c.text || '-');
            const tipoDoc = escapeHtml(c.tipo_documento || 'Documento');
            const documento = escapeHtml(c.numero_documento || '-');
            const nrc = escapeHtml(c.nrc || '-');
            const codActividad = escapeHtml(c.cod_actividad || '-');
            const descActividad = escapeHtml(c.desc_actividad || '-');
            const telefono = escapeHtml(c.telefono || '-');
            const correo = escapeHtml(c.correo || '-');
            const direccion = escapeHtml(c.direccion || '-');

            Swal.fire({
                title: 'Datos del cliente',
                html: `
                    <div class="text-start" style="font-size:14px;">
                        <div class="mb-3">
                            <span class="text-muted small d-block">Nombre</span>
                            <div class="fw-bold fs-6">${nombre}</div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <span class="text-muted small d-block">${tipoDoc}</span>
                                <div class="border rounded px-2 py-2 bg-light">${documento}</div>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">NRC</span>
                                <div class="border rounded px-2 py-2 bg-light">${nrc}</div>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <span class="text-muted small d-block">Cod. actividad</span>
                                <div class="border rounded px-2 py-2 bg-light">${codActividad}</div>
                            </div>
                            <div class="col-8">
                                <span class="text-muted small d-block">Giro</span>
                                <div class="border rounded px-2 py-2 bg-light">${descActividad}</div>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <span class="text-muted small d-block">Telefono</span>
                                <div class="border rounded px-2 py-2 bg-light">${telefono}</div>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Correo</span>
                                <div class="border rounded px-2 py-2 bg-light">${correo}</div>
                            </div>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Direccion</span>
                            <div class="border rounded px-2 py-2 bg-light">${direccion}</div>
                        </div>
                    </div>
                `,
                icon: 'info',
                width: '42rem',
                showClass: {
                    popup: 'cliente-popup-in'
                },
                hideClass: {
                    popup: 'cliente-popup-out'
                },
                confirmButtonText: 'Cerrar',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-secondary'
                }
            });
        });

        $('#servicioQuick').select2({
            language: 'es',
            placeholder: 'Buscar servicio...',
            minimumInputLength: 1,
            width: '100%',
            dropdownParent: $('body'),
            ajax: {
                url: '<?= base_url("productos/searchAjax") ?>',
                dataType: 'json',
                delay: 250,
                data: p => ({
                    q: p.term
                }),
                processResults: data => ({
                    results: (data.results || []).filter(r => r.tipo == '2')
                }),
            },
        });
requestAnimationFrame(() => {
    $row.find('.desc-input').each(function () {
        autoExpand(this);
    });
});
        $('#productoQuick').on('select2:select', function(e) {
            const producto = e.params.data;

            addRowFromProducto(producto);

            // limpiar selector para siguiente uso
            $('#productoQuick').val(null).trigger('change');
        });

        function actualizarTituloPrecio() {
            const tipo = $('#tipoDte').val();

            let texto = 'P/Unit (sin IVA)';
            let mostrarIva = true;

            if (tipo === '01') {
                texto = 'Precio (con IVA)';
                mostrarIva = false;
            }

            $('#thPrecioUnitario').text(texto);

            // Mostrar/ocultar columna IVA
            if (mostrarIva) {
                $('#thIva').show();
                $('.iva-cell').show();
            } else {
                $('#thIva').hide();
                $('.iva-cell').hide();
            }

            calcularTotales(); // recalcular al cambiar tipo
        }

        function aplicarModoDTE() {
            const tipo = $('#tipoDte').val();
            const esFactura = tipo === '01';

            if (esFactura) {
                $('#thIva').hide();
                $('.iva-cell').hide();
            } else {
                $('#thIva').show();
                $('.iva-cell').show();
            }

            // 🔥 recalcular TODAS las filas
            $('#productosBody tr').each(function() {
                calcularFila($(this));
            });
        }

        function addRowFromProducto(producto) {
            rowIdx++;

            const tpl = document.getElementById('rowTemplate').innerHTML.replace(/__IDX__/g, rowIdx);
            const $row = $(tpl);

            $('#productosBody').append($row);

            if ($('#tipoDte').val() === '01') {
                $row.find('.iva-cell').hide();
            }

            actualizarNumeros();

            // Setear producto
            $row.find('.producto-id').val(producto.id);
            $row.find('.producto-codigo').val(producto.codigo || '');

            // Descripción automática
            const desc = producto.text.replace(/\s*\(.*\)$/, '').trim();
            $row.find('.desc-input').val(desc);

            // 👇 PRIMERO calcula
            calcularFila($row);

            // 👇 LUEGO expandís (cuando ya todo está pintado)
            requestAnimationFrame(() => {
                $row.find('.auto-expand').each(function() {
                    autoExpand(this);
                });
            });

            // Focus UX
            setTimeout(() => {
                $row.find('.qty-input').focus().select();
            }, 100);
        }

        // ──────────────────────────────────────────────
        //  FILAS DE PRODUCTOS
        // ──────────────────────────────────────────────
        let rowIdx = 0;

        function actualizarNumeros() {
            $('#productosBody tr').each(function(i) {
                $(this).find('.num-item').text(i + 1);
            });
        }

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            actualizarNumeros();
            calcularTotales();
        });

        $(document).on('input change', '.qty-input, .price-input, .descu-input', function() {
            calcularFila($(this).closest('tr'));
        });

        function calcularFila($row) {
            const tipo = $('#tipoDte').val();

            const qty = parseFloat($row.find('.qty-input').val()) || 0;
            const price = parseFloat($row.find('.price-input').val()) || 0;
            const descu = parseFloat($row.find('.descu-input').val()) || 0;

            let gravada = 0,
                iva = 0,
                total = 0;

            if (tipo === '01') {
                // FACTURA: precio ya incluye IVA
                total = Math.max(0, Math.round((qty * price - descu) * 100) / 100);

                gravada = total; // aquí puedes decidir si separar o no
                iva = 0;

            } else {
                // CCF / NR: precio sin IVA
                gravada = Math.max(0, Math.round((qty * price - descu) * 100) / 100);
                iva = Math.round(gravada * 0.13 * 100) / 100;
                total = Math.round((gravada + iva) * 100) / 100;
            }

            $row.find('.gravada-cell').text(gravada.toFixed(2));
            $row.find('.iva-cell').text(iva.toFixed(2));
            $row.find('.total-cell').text(total.toFixed(2));

            calcularTotales();
        }

        function calcularTotales() {
            const tipo = $('#tipoDte').val();

            let totalGravada = 0,
                totalIva = 0;

            $('#productosBody tr').each(function() {
                totalGravada += parseFloat($(this).find('.gravada-cell').text()) || 0;
                totalIva += parseFloat($(this).find('.iva-cell').text()) || 0;
            });

            totalGravada = Math.round(totalGravada * 100) / 100;
            totalIva = Math.round(totalIva * 100) / 100;

            let totalPagar = 0;

            if (tipo === '01') {
                // Factura: ya incluye IVA
                totalPagar = totalGravada;
                totalIva = 0;
            } else {
                totalPagar = totalGravada + totalIva;
            }

            $('#lblTotalGravada').text('$' + totalGravada.toFixed(2));
            $('#lblTotalIva').text('$' + totalIva.toFixed(2));
            $('#lblTotalPagar').text('$' + totalPagar.toFixed(2));
        }

        function autoExpand(el) {
            el.style.height = 'auto';

            const minHeight = 38 * 2; // ~2 rows (ajústalo si quieres)
            const newHeight = Math.max(el.scrollHeight, minHeight);

            el.style.height = newHeight + 'px';
        }

        $(document).on('input', '.auto-expand', function() {
            autoExpand(this);
        });

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        // ──────────────────────────────────────────────
        //  SUBMIT
        // ──────────────────────────────────────────────
        $('#dteForm').on('submit', function(e) {
            e.preventDefault();

            const tipoDte = $('#tipoDte').val();
            const clienteId = $('#clienteId').val();
            const condicion = $('#condicionPago').val();
            const plazo = $('#plazoCredito').val();

            if (!clienteId) {
                Swal.fire('Cliente requerido', 'Seleccione un cliente para emitir el DTE.', 'warning');
                return;
            }

            if (tipoDte === '03' && !clienteTieneNrc()) {
                alertarClienteSinNrc();
                return;
            }

            if (condicion === 'credito' && !plazo) {
                Swal.fire('Plazo requerido', 'Seleccione el plazo de crédito.', 'warning');
                return;
            }

            // Recolectar items
            const items = [];
            let hayError = false;

            $('#productosBody tr').each(function(i) {
                const desc = $(this).find('.desc-input').val().trim().replace(/\r\n|\r|\n/g, '\r\n');
                const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;

                if (!desc) {
                    hayError = true;
                    return;
                }
                if (qty <= 0) {
                    hayError = true;
                    return;
                }
                if (price <= 0) {
                    hayError = true;
                    return;
                }

                items.push({
                    producto_id: $(this).find('.producto-id').val() || null,
                    codigo: $(this).find('.producto-codigo').val() || null,
                    tipo_item: parseInt($(this).find('.tipo-item').val()),
                    descripcion: desc,
                    cantidad: qty,
                    precio_uni: price,
                    descuento: parseFloat($(this).find('.descu-input').val()) || 0,
                });
            });

            if (hayError) {
                Swal.fire('Líneas incompletas', 'Complete descripción, cantidad y precio en todas las líneas.', 'warning');
                return;
            }

            if (items.length === 0) {
                Swal.fire('Sin productos', 'Agregue al menos un producto al DTE.', 'warning');
                return;
            }

            const totalPagar = parseFloat($('#lblTotalPagar').text().replace('$', '')) || 0;
            const tipoDteLabels = { '01': 'Factura Consumidor Final', '03': 'Crédito Fiscal', '04': 'Nota de Remisión' };
            const tipoDteLabel = tipoDteLabels[tipoDte] || tipoDte;



            Swal.fire({
                title: `Emitir y guardar - ${tipoDteLabel}`,
                html: `
                <div class="text-start" style="font-size:14px;">
                    <p><b>Documento:</b> ${tipoDteLabel}</p>
                    <p><b>Líneas:</b> ${items.length}</p>
                    <hr>
                    <p class="fs-5"><b>Total: $${totalPagar.toFixed(2)}</b></p>
                </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Emitir y guardar',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-success m-2',
                    cancelButton: 'btn btn-secondary m-2',
                },
            }).then(result => {
                if (!result.isConfirmed) return;

                $('#btnEmitir').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Emitiendo...');

                const payload = {
                    tipo_dte: tipoDte,
                    cliente_id: clienteId,
                    condicion_operacion: condicion,
                    plazo_credito: plazo || null,
                    fecha_emision: $('#fechaIso').val(),
                    hora_emision: $('#horaIso').val(),
                    observaciones: $('#observaciones').val(),
                    items: items,
                };

                fetch('<?= base_url("emision-dte/store") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const asientoMsg = data.asiento_creado
                                ? '<p class="mb-1"><b>Asiento contable:</b> generado</p>'
                                : (data.asiento_omitido ? `<p class="mb-1 text-warning"><b>Asiento omitido:</b> ${escapeHtml(data.asiento_omitido)}</p>` : '');

                            Swal.fire({
                                icon: 'success',
                                title: 'DTE guardado',
                                html: `
                                    <div class="text-start" style="font-size:14px;">
                                        <p class="mb-1"><b>Numero de control:</b> ${escapeHtml(data.numero || 'N/D')}</p>
                                        <p class="mb-1"><b>Total:</b> $${Number(data.total || 0).toFixed(2)}</p>
                                        <p class="mb-1"><b>Estado MH:</b> ${escapeHtml(data.estado_mh || 'N/D')}</p>
                                        ${asientoMsg}
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Ver factura',
                                cancelButtonText: 'Cerrar',
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: 'btn btn-success m-2',
                                    cancelButton: 'btn btn-secondary m-2',
                                },
                            }).then((res) => {
                                if (res.isConfirmed && data.factura_id) {
                                    window.location.href = `<?= base_url('facturas') ?>/${data.factura_id}`;
                                }
                            });
                        } else {
                            Swal.fire('Error al emitir', data.message ?? 'Error desconocido.', 'error');
                            $('#btnEmitir').html('<i class="fa-solid fa-paper-plane me-1"></i> Emitir y guardar');
                            actualizarEstadoBotonEmitir();
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
                    })
                    .finally(() => {
                        $('#btnEmitir').html('<i class="fa-solid fa-paper-plane me-1"></i> Emitir y guardar');
                        actualizarEstadoBotonEmitir();
                    });
            });
        });

    });
</script>

<?= $this->endSection() ?>
