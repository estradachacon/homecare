<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #ced4da; border-radius: .375rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: .75rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    .card-linea { border-left: 4px solid #0d6efd; }
    .qty-warning { color: #dc3545; font-size: 11px; font-weight: bold; }
    .qty-ok { color: #198754; font-size: 11px; }
    .bloque-devolucion, .bloque-facturas { display:none; }
    .select2-multiple .select2-selection--multiple { min-height: 38px; }
</style>

<div class="row">
    <div class="col-md-12">

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
$(document).ready(function () {

    // Cargar facturas del vendedor en los select2 múltiple
    const vendedorId = <?= (int)$consignacion->vendedor_id ?>;

    function cargarFacturasVendedor(selectEl) {
        $(selectEl).select2({
            language: 'es',
            placeholder: 'Buscar factura...',
            ajax: {
                url: '<?= base_url('consignaciones/facturas-vendedor') ?>/' + vendedorId,
                dataType: 'json',
                delay: 250,
                processResults: data => ({ results: data.results }),
                cache: true,
            },
        });
    }

    $('.select-facturas').each(function () {
        cargarFacturasVendedor(this);
    });

    // Validar distribución por línea
    function validarLinea(id) {
        const max   = parseFloat($(`[data-id="${id}"].input-facturada`).data('max'));
        const fact  = parseFloat($(`[name="lineas[${id}][cantidad_facturada]"]`).val())    || 0;
        const dev   = parseFloat($(`[name="lineas[${id}][cantidad_devuelta]"]`).val())      || 0;
        const stock = parseFloat($(`[name="lineas[${id}][cantidad_stock_vendedor]"]`).val()) || 0;
        const suma  = fact + dev + stock;

        const card  = $(`.card-linea[data-detalle-id="${id}"]`);
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

    // Escuchar cambios en inputs de cantidad
    $(document).on('input', '.input-facturada, .input-devuelta, .input-stock', function () {
        const id = $(this).data('id');
        validarLinea(id);
    });

    // Submit con validación
    $('#formCierre').on('submit', function (e) {
        e.preventDefault();

        let hayError = false;
        <?php foreach ($detalles as $d): ?>
        (function () {
            const id    = <?= $d->id ?>;
            const max   = <?= $d->cantidad ?>;
            const fact  = parseFloat($(`[name="lineas[${id}][cantidad_facturada]"]`).val())    || 0;
            const dev   = parseFloat($(`[name="lineas[${id}][cantidad_devuelta]"]`).val())      || 0;
            const stock = parseFloat($(`[name="lineas[${id}][cantidad_stock_vendedor]"]`).val()) || 0;
            const suma  = fact + dev + stock;
            if (Math.abs(suma - max) > 0.01) hayError = true;
        })();
        <?php endforeach; ?>

        if (hayError) {
            $('#alertaDistribucion').show();
            $('html, body').animate({ scrollTop: $('#alertaDistribucion').offset().top - 20 }, 300);
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

<?= $this->endSection() ?>
