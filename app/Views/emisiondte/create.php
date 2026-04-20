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

    #productosBody td {
        vertical-align: top;
    }

    #productosBody input,
    #productosBody select,
    #productosBody textarea {
        margin-top: 2px;
    }

    #productosBody td {
        padding-top: 6px;
        padding-bottom: 6px;
    }

    #productosBody .select2-container {
        min-width: 180px;
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

                        <div class="col-md-3">
                            <label class="compact-label">N° de control (próximo)</label>
                            <input type="text" id="numeroControlPreview" class="form-control form-control-sm-dte text-muted" readonly>
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
                        <table class="table table-sm table-bordered" id="productosTable">
                            <thead class="table-light" style="font-size:12px;">
                                <tr>
                                    <th style="width:32px">#</th>
                                    <th style="width:100px">Tipo</th>
                                    <th style="min-width:150px">Descripción</th>
                                    <th style="width:100px">Cant.</th>
                                    <th id="thPrecioUnitario" style="width:90px">P/Unit (sin IVA)</th>
                                    <th style="width:75px">Descuento</th>
                                    <th style="width:85px" class="text-end">Gravado</th>
                                    <th id="thIva" style="width:75px" class="text-end">IVA 13%</th>
                                    <th style="width:85px" class="text-end">Total</th>
                                    <th style="width:36px"></th>
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
                            placeholder="Descripción"></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success" id="btnEmitir">
                            <i class="fa-solid fa-paper-plane me-1"></i> Emitir DTE
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

        <td class="num-item text-center text-muted" style="font-size:11px;"></td>

        <td>
            <select class="form-control form-control-sm tipo-item" name="items[__IDX__][tipo_item]">
                <option value="1">Bien</option>
                <option value="2">Servicio</option>
                <option value="3">Ambos</option>
            </select>
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

        <td class="text-end gravada-cell" style="font-size:12px;">0.00</td>
        <td class="text-end iva-cell" style="font-size:12px;">0.00</td>
        <td class="text-end total-cell fw-bold" style="font-size:12px;">0.00</td>

        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                <i class="fa-solid fa-times"></i>
            </button>
        </td>

    </tr>
</template>

<script>
    $(function() {

        // ──────────────────────────────────────────────
        //  FECHA Y HORA
        // ──────────────────────────────────────────────
        const now = new Date();
        const fechaIso = now.toISOString().split('T')[0];
        const horaIso = now.toTimeString().slice(0, 8);

        $('#fechaDisplay').val(now.toLocaleDateString('es-SV'));
        $('#horaDisplay').val(now.toLocaleTimeString('es-SV'));
        $('#fechaIso').val(fechaIso);
        $('#horaIso').val(horaIso);

        // ──────────────────────────────────────────────
        //  N° CONTROL PREVIEW
        // ──────────────────────────────────────────────
        function cargarNumeroControl() {
            const tipo = $('#tipoDte').val();
            $.getJSON('<?= base_url("emision-dte/proximo-numero") ?>/' + tipo, data => {
                $('#numeroControlPreview').val(data.numero || '—');
            }).fail(() => $('#numeroControlPreview').val('—'));
        }
        cargarNumeroControl();
        $('#tipoDte').on('change', function() {
            cargarNumeroControl();
            actualizarTituloPrecio();
            aplicarModoDTE(); // 🔥 ESTA ES LA CLAVE
        });
        actualizarTituloPrecio();
        aplicarModoDTE();
        $('#tipoDte').on('change', cargarNumeroControl);

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
        });

        $('#btnToggleCliente').on('click', function() {
            if ($('#clienteInfo').is(':visible')) {
                $('#clienteInfo').hide();
                $(this).text('Ver datos');
            } else {
                $('#clienteInfo').show();
                $(this).text('Ocultar datos');
            }
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
            $row.find('.producto-label').text(producto.text);
            $row.find('.producto-id').val(producto.id);

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

            if (condicion === 'credito' && !plazo) {
                Swal.fire('Plazo requerido', 'Seleccione el plazo de crédito.', 'warning');
                return;
            }

            // Recolectar items
            const items = [];
            let hayError = false;

            $('#productosBody tr').each(function(i) {
                const desc = $(this).find('.desc-input').val().trim();
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
                    producto_id: $(this).find('.producto-select').val() || null,
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
            const tipoDteLabel = tipoDte === '01' ? 'Factura Consumidor Final' :
                tipoDte === '03' ? 'Comprobante de Crédito Fiscal' :
                'Nota de Remisión';

            Swal.fire({
                title: 'Confirmar emisión',
                html: `
                <div class="text-start" style="font-size:14px;">
                    <p><b>Documento:</b> ${tipoDteLabel}</p>
                    <p><b>N° control:</b> ${$('#numeroControlPreview').val()}</p>
                    <p><b>Productos:</b> ${items.length}</p>
                    <hr>
                    <p class="fs-5"><b>Total: $${totalPagar.toFixed(2)}</b></p>
                </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Emitir DTE',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-success m-2',
                    cancelButton: 'btn btn-secondary m-2',
                },
            }).then(result => {
                if (!result.isConfirmed) return;

                $('#btnEmitir').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Enviando...');

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
                            const icoClass = data.estado_mh === 'procesado' ? 'success' : 'warning';
                            const titulo = data.estado_mh === 'procesado' ? 'DTE Emitido' : 'DTE enviado (pendiente MH)';
                            Swal.fire({
                                icon: icoClass,
                                title: titulo,
                                html: `<p>${data.numero}</p>` +
                                    (data.sello ? `<p class="text-muted small">Sello: ${data.sello.substring(0, 30)}...</p>` : '') +
                                    `<p class="fs-5 fw-bold">$${parseFloat(data.total).toFixed(2)}</p>`,
                                timer: 3000,
                                showConfirmButton: true,
                                confirmButtonText: 'Ver detalle',
                            }).then(() => {
                                window.location.href = '<?= base_url("emision-dte") ?>/' + data.factura_id;
                            });
                        } else {
                            Swal.fire('Error al emitir', data.message ?? 'Error desconocido.', 'error');
                            $('#btnEmitir').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Emitir DTE');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
                        $('#btnEmitir').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Emitir DTE');
                    });
            });
        });

    });
</script>

<?= $this->endSection() ?>