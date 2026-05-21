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

    .card-linea {
        border-left: 4px solid #0d6efd;
    }

    .qty-warning {
        color: #dc3545;
        font-size: 11px;
        font-weight: bold;
    }

    .qty-ok {
        color: #198754;
        font-size: 11px;
    }

    .bloque-devolucion,
    .bloque-facturas {
        display: none;
    }

    .select2-multiple .select2-selection--multiple {
        min-height: 38px;
    }
</style>

<div class="row">
    <div class="col-md-12">

        <?php
        $apEst = $consignacion->aprobacion_estado ?? 'pendiente';
        ?>

        <?php if ($apEst !== 'aprobada'): ?>
            <div class="alert alert-warning d-flex align-items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation fa-lg mt-1"></i>
                <div>
                    <strong>Aprobación pendiente</strong><br>
                    <?php if ($apEst === 'rechazada'): ?>
                        Esta nota fue <strong>rechazada</strong>. Motivo: <?= esc($consignacion->rechazo_motivo ?? '—') ?><br>
                        Debe ser aprobada por el validador físico antes de poder cerrarse.
                    <?php else: ?>
                        Esta nota aún no ha sido aprobada por el validador físico.
                        El cierre está bloqueado hasta que sea aprobada.
                    <?php endif; ?>
                    <br>
                    <a href="<?= base_url('consignaciones/' . $consignacion->id) ?>" class="btn btn-sm btn-secondary mt-2">
                        <i class="fa-solid fa-arrow-left me-1"></i> Volver al detalle
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="alert alert-info">
                <i class="fa-solid fa-info-circle"></i>
                Para cada producto indique cómo se distribuye la cantidad consignada:
                <strong>facturada</strong>, <strong>devuelta</strong> o en
                <strong>stock del vendedor</strong>. La suma debe ser igual a la cantidad original.
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="header-title mb-0">
                        Cerrar Nota <strong><?= esc($consignacion->numero) ?></strong>
                        &nbsp;–&nbsp; <?= esc($consignacion->vendedor_nombre) ?>
                    </h4>
                    <a href="<?= base_url('consignaciones/' . $consignacion->id) ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i> Cancelar
                    </a>
                </div>

                <div class="card-body">
                    <form id="formCierre" method="POST"
                        action="<?= base_url('consignaciones/' . $consignacion->id . '/procesar-cierre') ?>"
                        enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Una tarjeta por producto -->
                        <?php foreach ($detalles as $d): ?>
                            <div class="card card-linea mb-4" data-detalle-id="<?= $d->id ?>">
                                <div class="card-body">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-6">
                                            <h6 class="mb-0">
                                                <span class="badge bg-light text-dark border me-1"><?= esc($d->producto_codigo) ?></span>
                                                <?= esc($d->producto_nombre) ?>
                                            </h6>
                                            <small class="text-muted">
                                                Cantidad original: <strong><?= number_format($d->cantidad, 2) ?></strong>
                                                &nbsp;|&nbsp; Precio: <strong>$<?= number_format($d->precio_unitario, 2) ?></strong>
                                            </small>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <span class="estado-linea qty-warning">Pendiente de distribuir</span>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Cantidad facturada -->
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Cantidad facturada</label>
                                            <input type="number"
                                                name="lineas[<?= $d->id ?>][cantidad_facturada]"
                                                class="form-control input-facturada"
                                                min="0" step="0.01" value="0"
                                                data-max="<?= $d->cantidad ?>"
                                                data-id="<?= $d->id ?>">
                                        </div>
                                        <!-- Cantidad devuelta -->
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Cantidad devuelta</label>
                                            <input type="number"
                                                name="lineas[<?= $d->id ?>][cantidad_devuelta]"
                                                class="form-control input-devuelta"
                                                min="0" step="0.01" value="0"
                                                data-max="<?= $d->cantidad ?>"
                                                data-id="<?= $d->id ?>">
                                        </div>
                                        <!-- Stock vendedor -->
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">En stock del vendedor</label>
                                            <input type="number"
                                                name="lineas[<?= $d->id ?>][cantidad_stock_vendedor]"
                                                class="form-control input-stock"
                                                min="0" step="0.01" value="0"
                                                data-max="<?= $d->cantidad ?>"
                                                data-id="<?= $d->id ?>">
                                        </div>
                                        <?php
                                        $lotesLinea = $lotesPorDetalle[$d->id] ?? [];
                                        $totalLotes = count($lotesLinea);
                                        ?>

                                        <?php if (!empty($lotesLinea)): ?>
                                            <div class="col-md-12 bloque-lotes-traslado mt-3"
                                                id="lotes_traslado_<?= $d->id ?>"
                                                data-total-lotes="<?= $totalLotes ?>"
                                                style="display:none;">

                                                <div class="p-3 border rounded bg-light">
                                                    <p class="mb-2 small fw-bold text-warning">
                                                        <i class="fa-solid fa-boxes-stacked"></i>
                                                        Lotes que pasarán a la nueva nota
                                                    </p>

                                                    <div class="alert alert-info py-2 mb-2 small">
                                                        Si el traslado es completo, los lotes pasan automáticamente.
                                                        Si es parcial y hay varios lotes, indique de cuáles lotes sale la cantidad.
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-1">
                                                            <thead>
                                                                <tr>
                                                                    <th>Lote</th>
                                                                    <th class="text-end">Cantidad original</th>
                                                                    <th class="text-end">Cantidad a trasladar</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($lotesLinea as $lote): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <strong><?= esc($lote->numero_lote ?? 'Lote') ?></strong>
                                                                            <?php if (!empty($lote->fecha_vencimiento)): ?>
                                                                                <br><small class="text-muted">Vence: <?= esc($lote->fecha_vencimiento) ?></small>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <?= number_format($lote->cantidad, 2) ?>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number"
                                                                                name="lineas[<?= $d->id ?>][lotes_stock][<?= $lote->lote_id ?>]"
                                                                                class="form-control form-control-sm text-end input-lote-stock"
                                                                                min="0"
                                                                                step="0.01"
                                                                                value="0"
                                                                                data-detalle="<?= $d->id ?>"
                                                                                data-max-lote="<?= $lote->cantidad ?>">
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <small class="estado-lotes-traslado qty-warning" id="estado_lotes_<?= $d->id ?>">
                                                        Pendiente de validar lotes.
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-md-12 bloque-lotes-facturados mt-3"
                                                id="lotes_facturados_<?= $d->id ?>"
                                                data-total-lotes="<?= $totalLotes ?>"
                                                style="display:none;">

                                                <div class="p-3 border rounded bg-light">
                                                    <p class="mb-2 small fw-bold text-primary">
                                                        <i class="fa-solid fa-boxes-stacked"></i>
                                                        Lotes que se facturarÃ¡n
                                                    </p>

                                                    <div class="alert alert-info py-2 mb-2 small">
                                                        Si la facturaciÃ³n es completa, los lotes se asignan automÃ¡ticamente.
                                                        Si es parcial y hay varios lotes, indique de cuÃ¡les lotes sale la cantidad facturada.
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-1">
                                                            <thead>
                                                                <tr>
                                                                    <th>Lote</th>
                                                                    <th class="text-end">Cantidad original</th>
                                                                    <th class="text-end">Cantidad a facturar</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($lotesLinea as $lote): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <strong><?= esc($lote->numero_lote ?? 'Lote') ?></strong>
                                                                            <?php if (!empty($lote->fecha_vencimiento)): ?>
                                                                                <br><small class="text-muted">Vence: <?= esc($lote->fecha_vencimiento) ?></small>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <?= number_format($lote->cantidad, 2) ?>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number"
                                                                                name="lineas[<?= $d->id ?>][lotes_facturados][<?= $lote->lote_id ?>]"
                                                                                class="form-control form-control-sm text-end input-lote-facturado"
                                                                                min="0"
                                                                                step="0.01"
                                                                                value="0"
                                                                                data-detalle="<?= $d->id ?>"
                                                                                data-max-lote="<?= $lote->cantidad ?>">
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <small class="estado-lotes-facturados qty-warning" id="estado_lotes_facturados_<?= $d->id ?>">
                                                        Pendiente de validar lotes facturados.
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Bloque devolución (visible si devuelta > 0) -->
                                    <div class="bloque-devolucion mt-3 p-3 border rounded bg-light" id="devolucion_<?= $d->id ?>">
                                        <p class="mb-2 small fw-bold text-danger"><i class="fa-solid fa-undo"></i> Datos de devolución</p>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="small text-muted">Documento con el que se recibió</label>
                                                <input type="text" name="lineas[<?= $d->id ?>][doc_devolucion]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Nº documento o referencia">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small text-muted">Foto (opcional)</label>
                                                <input type="file" name="foto_<?= $d->id ?>"
                                                    class="form-control form-control-sm"
                                                    accept="image/*">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small text-muted">Comentario</label>
                                                <input type="text" name="lineas[<?= $d->id ?>][comentario_devolucion]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Motivo o nota adicional">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bloque facturas (visible si facturada > 0) -->
                                    <div class="bloque-facturas mt-3 p-3 border rounded bg-light" id="facturas_<?= $d->id ?>">
                                        <p class="mb-2 small fw-bold text-primary"><i class="fa-solid fa-file-invoice"></i> Facturas asociadas</p>

                                        <?php $sugerencias = array_values($sugerenciasPorProducto[$d->producto_id] ?? []); ?>
                                        <?php if (!empty($sugerencias)): ?>
                                            <div class="alert alert-success py-2 mb-2" style="font-size:.82rem;">
                                                <i class="fa-solid fa-lightbulb mr-1"></i>
                                                <strong>Sugerencia basada en NP asociada<?= count($sugerencias) > 1 ? 's' : '' ?>:</strong>
                                                <div class="mt-1 d-flex flex-wrap gap-1">
                                                    <?php foreach ($sugerencias as $s): ?>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-success btn-aplicar-factura"
                                                                data-detalle="<?= $d->id ?>"
                                                                data-factura-id="<?= $s['factura_id'] ?>"
                                                                data-factura-numero="<?= esc($s['factura_texto']) ?>"
                                                                data-np-cantidad="<?= $s['np_cantidad'] ?>"
                                                                data-detalle-max="<?= $d->cantidad ?>"
                                                                title="Aplicar esta factura al selector">
                                                            <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>
                                                            <?= esc($s['np_numero']) ?> → <strong><?= esc($s['factura_texto']) ?></strong>
                                                            <span class="text-muted ml-1">(<?= number_format($s['np_cantidad'], 0) ?> u.)</span>
                                                        </button>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <label class="small text-muted">Seleccione una o más facturas del vendedor</label>
                                        <select name="lineas[<?= $d->id ?>][facturas][]"
                                            class="form-control select-facturas"
                                            multiple
                                            data-vendedor="<?= $consignacion->vendedor_id ?>">
                                        </select>
                                        <small class="text-muted">Puede buscar por número de control</small>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Observaciones generales del cierre -->
                        <div class="mb-3">
                            <label class="form-label">Observaciones del cierre</label>
                            <textarea name="observaciones_cierre" class="form-control" rows="2"
                                placeholder="Notas generales sobre el cierre de esta consignación..."></textarea>
                        </div>

                        <div class="alert alert-warning" id="alertaDistribucion" style="display:none;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Hay productos con cantidades no distribuidas correctamente. Revise antes de continuar.
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success btn-lg" id="btnCerrar">
                                <i class="fa-solid fa-lock"></i> Cerrar Nota de Envío
                            </button>
                        </div>

                    </form>
                </div>
            </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // Cargar facturas del vendedor en los select2 múltiple
        const vendedorId = <?= (int)$consignacion->vendedor_id ?>;

        function cargarFacturasVendedor(selectEl) {
            $(selectEl).select2({
                language: 'es',
                placeholder: 'Buscar factura...',
                minimumInputLength: 0,
                ajax: {
                    url: '<?= base_url('consignaciones/facturas-vendedor') ?>/' + vendedorId,
                    dataType: 'json',
                    delay: 300,
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({
                        results: data.results
                    }),
                    cache: false,
                },
            });
        }

        $('.select-facturas').each(function() {
            cargarFacturasVendedor(this);
        });

        // Aplicar sugerencia de factura desde NP asociada
        document.querySelectorAll('.btn-aplicar-factura').forEach(btn => {
            btn.addEventListener('click', function() {
                const detalleId     = this.dataset.detalle;
                const facturaId     = this.dataset.facturaId;
                const facturaNro    = this.dataset.facturaNumero;
                const npCantidad    = parseFloat(this.dataset.npCantidad) || 0;
                const detalleMax    = parseFloat(this.dataset.detalleMax) || 0;

                // Pre-seleccionar factura en el Select2
                const selectEl = document.querySelector(`select[name="lineas[${detalleId}][facturas][]"]`);
                const opt = new Option(facturaNro, facturaId, true, true);
                $(selectEl).append(opt).trigger('change');

                // Pre-llenar cantidad facturada si aún está en 0
                const inputFact = document.querySelector(`input[name="lineas[${detalleId}][cantidad_facturada]"]`);
                if (inputFact && (parseFloat(inputFact.value) || 0) === 0) {
                    inputFact.value = Math.min(npCantidad, detalleMax);
                    inputFact.dispatchEvent(new Event('input', { bubbles: true }));
                }

                // Feedback visual en el botón
                this.classList.replace('btn-outline-success', 'btn-success');
                this.innerHTML = '<i class="fa-solid fa-check mr-1"></i>' + this.innerHTML.replace(/<i[^>]*><\/i>/, '');
            });
        });

        // Validar distribución por línea
        function validarLinea(id) {
            const max = parseFloat($(`[data-id="${id}"].input-facturada`).data('max'));
            const fact = parseFloat($(`[name="lineas[${id}][cantidad_facturada]"]`).val()) || 0;
            const dev = parseFloat($(`[name="lineas[${id}][cantidad_devuelta]"]`).val()) || 0;
            const stock = parseFloat($(`[name="lineas[${id}][cantidad_stock_vendedor]"]`).val()) || 0;
            const suma = fact + dev + stock;
            const bloqueLotes = $(`#lotes_traslado_${id}`);
            const totalLotes = parseInt(bloqueLotes.data('total-lotes') || 0);
            const bloqueLotesFacturados = $(`#lotes_facturados_${id}`);
            const totalLotesFacturados = parseInt(bloqueLotesFacturados.data('total-lotes') || 0);

            if (stock > 0 && totalLotes > 1 && stock < max) {
                bloqueLotes.show();
            } else {
                bloqueLotes.hide();

                bloqueLotes.find('.input-lote-stock').each(function() {
                    $(this).val(0);
                });
            }

            validarLotesTraslado(id);

            if (fact > 0 && totalLotesFacturados > 1 && fact < max) {
                bloqueLotesFacturados.show();
            } else {
                bloqueLotesFacturados.hide();

                bloqueLotesFacturados.find('.input-lote-facturado').each(function() {
                    $(this).val(0);
                });
            }

            validarLotesFacturados(id);

            const card = $(`.card-linea[data-detalle-id="${id}"]`);
            const label = card.find('.estado-linea');

            const diff = Math.abs(suma - max);

            if (diff < 0.001) {
                label.removeClass('qty-warning').addClass('qty-ok')
                    .text('✓ Distribuido correctamente (' + max.toFixed(2) + ')');
            } else {
                label.removeClass('qty-ok').addClass('qty-warning')
                    .text('⚠ Suma: ' + suma.toFixed(2) + ' / Original: ' + max.toFixed(2));
            }

            // Mostrar/ocultar bloques
            if (dev > 0) {
                $(`#devolucion_${id}`).show();
            } else {
                $(`#devolucion_${id}`).hide();
            }

            if (fact > 0) {
                $(`#facturas_${id}`).show();
            } else {
                $(`#facturas_${id}`).hide();
            }
        }

        function validarLotesTraslado(id) {
            const max = parseFloat($(`[data-id="${id}"].input-facturada`).data('max'));
            const stock = parseFloat($(`[name="lineas[${id}][cantidad_stock_vendedor]"]`).val()) || 0;

            const bloqueLotes = $(`#lotes_traslado_${id}`);
            const totalLotes = parseInt(bloqueLotes.data('total-lotes') || 0);
            const estado = $(`#estado_lotes_${id}`);

            if (!bloqueLotes.length || stock <= 0 || totalLotes <= 1 || stock >= max) {
                return true;
            }

            let sumaLotes = 0;
            let loteExcedido = false;

            bloqueLotes.find('.input-lote-stock').each(function() {
                const val = parseFloat($(this).val()) || 0;
                const maxLote = parseFloat($(this).data('max-lote')) || 0;

                if (val > maxLote) {
                    loteExcedido = true;
                }

                sumaLotes += val;
            });

            if (loteExcedido) {
                estado.removeClass('qty-ok').addClass('qty-warning')
                    .text('⚠ Un lote supera la cantidad original.');
                return false;
            }

            if (Math.abs(sumaLotes - stock) < 0.001) {
                estado.removeClass('qty-warning').addClass('qty-ok')
                    .text('✓ Lotes distribuidos correctamente (' + stock.toFixed(2) + ')');
                return true;
            }

            estado.removeClass('qty-ok').addClass('qty-warning')
                .text('⚠ Lotes: ' + sumaLotes.toFixed(2) + ' / Stock vendedor: ' + stock.toFixed(2));

            return false;
        }

        function validarLotesFacturados(id) {
            const max = parseFloat($(`[data-id="${id}"].input-facturada`).data('max'));
            const fact = parseFloat($(`[name="lineas[${id}][cantidad_facturada]"]`).val()) || 0;

            const bloqueLotes = $(`#lotes_facturados_${id}`);
            const totalLotes = parseInt(bloqueLotes.data('total-lotes') || 0);
            const estado = $(`#estado_lotes_facturados_${id}`);

            if (!bloqueLotes.length || fact <= 0 || totalLotes <= 1 || fact >= max) {
                return true;
            }

            let sumaLotes = 0;
            let loteExcedido = false;

            bloqueLotes.find('.input-lote-facturado').each(function() {
                const val = parseFloat($(this).val()) || 0;
                const maxLote = parseFloat($(this).data('max-lote')) || 0;

                if (val > maxLote) {
                    loteExcedido = true;
                }

                sumaLotes += val;
            });

            if (loteExcedido) {
                estado.removeClass('qty-ok').addClass('qty-warning')
                    .text('âš  Un lote supera la cantidad original.');
                return false;
            }

            if (Math.abs(sumaLotes - fact) < 0.001) {
                estado.removeClass('qty-warning').addClass('qty-ok')
                    .text('âœ“ Lotes facturados correctamente (' + fact.toFixed(2) + ')');
                return true;
            }

            estado.removeClass('qty-ok').addClass('qty-warning')
                .text('âš  Lotes: ' + sumaLotes.toFixed(2) + ' / Facturado: ' + fact.toFixed(2));

            return false;
        }

        // Escuchar cambios en inputs de cantidad
        $(document).on('input', '.input-facturada, .input-devuelta, .input-stock', function() {
            const id = $(this).data('id');
            validarLinea(id);
        });

        $(document).on('input', '.input-lote-stock, .input-lote-facturado', function() {
            const id = $(this).data('detalle');
            validarLotesTraslado(id);
            validarLotesFacturados(id);
        });

        // Submit con validación
        $('#formCierre').on('submit', function(e) {
            e.preventDefault();

            let hayError = false;
            <?php foreach ($detalles as $d): ?>
                    (function() {
                        const id = <?= $d->id ?>;
                        const max = <?= $d->cantidad ?>;
                        const fact = parseFloat($(`[name="lineas[${id}][cantidad_facturada]"]`).val()) || 0;
                        const dev = parseFloat($(`[name="lineas[${id}][cantidad_devuelta]"]`).val()) || 0;
                        const stock = parseFloat($(`[name="lineas[${id}][cantidad_stock_vendedor]"]`).val()) || 0;
                        const suma = fact + dev + stock;
                        if (Math.abs(suma - max) > 0.01) hayError = true;
                        if (!validarLotesTraslado(id)) hayError = true;
                        if (!validarLotesFacturados(id)) hayError = true;
                    })();
            <?php endforeach; ?>

            if (hayError) {
                $('#alertaDistribucion').show();
                $('html, body').animate({
                    scrollTop: $('#alertaDistribucion').offset().top - 20
                }, 300);
                return;
            }

            $('#alertaDistribucion').hide();

            const hayStock = <?php
                                $stocks = [];
                                foreach ($detalles as $d) $stocks[] = "parseFloat($('[name=\"lineas[{$d->id}][cantidad_stock_vendedor]\"]').val()) || 0";
                                echo '(' . implode(' + ', $stocks) . ') > 0';
                                ?>;

            let html = '<p>¿Confirma el cierre de la nota <strong><?= esc($consignacion->numero) ?></strong>?</p>';
            if (hayStock) {
                html += '<div class="alert alert-info mt-2" style="text-align:left;font-size:13px;">Se generará automáticamente una <strong>nueva nota de envío</strong> con el producto que queda en stock del vendedor.</div>';
            }

            Swal.fire({
                title: 'Confirmar cierre',
                html: html,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
            }).then(result => {
                if (result.isConfirmed) {
                    $('#formCierre')[0].submit();
                }
            });
        });
    });
</script>

<?php endif; // aprobacion_estado === 'aprobada' 
?>

<?= $this->endSection() ?>
