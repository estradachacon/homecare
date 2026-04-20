<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .compact-label { font-size: 12px; color: #6c757d; margin-bottom: 2px; display: block; }
    .form-control-sm-dte { font-size: 13px; height: 34px; padding: 4px 8px; }
    #ncBody td { vertical-align: middle; padding: 4px 6px; }
    .total-box { background: #f8f9fa; border-radius: 8px; padding: 16px 20px; }
    .doc-original-badge { font-size: 12px; }
</style>

<div class="row">
    <div class="col-md-12">

        <!-- Referencia al documento original -->
        <div class="alert alert-info d-flex align-items-center gap-3 mb-3" style="font-size:13px;">
            <i class="fa-solid fa-link fa-lg"></i>
            <div>
                <strong>Nota de Crédito</strong> referenciando
                <span class="badge bg-primary doc-original-badge ms-1"><?= esc($original->numero_control) ?></span>
                &nbsp;|&nbsp;
                <?= $original->tipo_dte === '01' ? 'Factura' : 'Créd. Fiscal' ?>
                &nbsp;|&nbsp;
                <?= date('d/m/Y', strtotime($original->fecha_emision)) ?>
                &nbsp;|&nbsp;
                Cliente: <strong><?= esc($original->cliente_nombre ?? 'Consumidor Final') ?></strong>
                &nbsp;|&nbsp;
                Total original: <strong>$<?= number_format($original->total_pagar, 2) ?></strong>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fa-solid fa-file-invoice me-1 text-warning"></i>
                    Nueva Nota de Crédito (05)
                </h5>
                <a href="<?= base_url('emision-dte/' . $original->id) ?>" class="btn btn-sm btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Cancelar
                </a>
            </div>

            <div class="card-body">
                <form id="ncForm">
                    <input type="hidden" id="originalId" value="<?= $original->id ?>">

                    <!-- Fecha / Hora -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-2">
                            <label class="compact-label">Fecha emisión NC</label>
                            <input type="text" id="fechaDisplay" class="form-control form-control-sm-dte" readonly>
                            <input type="hidden" id="fechaIso">
                        </div>
                        <div class="col-md-2">
                            <label class="compact-label">Hora emisión NC</label>
                            <input type="text" id="horaDisplay" class="form-control form-control-sm-dte" readonly>
                            <input type="hidden" id="horaIso">
                        </div>
                        <div class="col-md-3">
                            <label class="compact-label">N° control (próximo)</label>
                            <input type="text" id="numeroControlPreview" class="form-control form-control-sm-dte text-muted" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="compact-label">Motivo de la nota de crédito</label>
                            <input type="text" id="motivoNc" class="form-control form-control-sm-dte"
                                placeholder="Ej: Devolución de mercadería, error en precio...">
                        </div>
                    </div>

                    <hr>

                    <!-- Tabla de ítems (pre-llenada desde el original) -->
                    <h6 class="mb-2">
                        Ítems a acreditar
                        <small class="text-muted fw-normal">(ajuste montos según lo que se está devolviendo/corrigiendo)</small>
                    </h6>

                    <div class="table-responsive mb-2">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light" style="font-size:12px;">
                                <tr>
                                    <th style="width:32px">#</th>
                                    <th>Descripción</th>
                                    <th style="width:70px">Cant.</th>
                                    <th style="width:90px">P/Unit (sin IVA)</th>
                                    <th style="width:85px" class="text-end">Gravado</th>
                                    <th style="width:75px" class="text-end">IVA 13%</th>
                                    <th style="width:85px" class="text-end">Total crédito</th>
                                    <th style="width:36px"></th>
                                </tr>
                            </thead>
                            <tbody id="ncBody">
                                <?php foreach ($detalles as $i => $d): ?>
                                <tr data-row="<?= $i ?>">
                                    <td class="text-center text-muted" style="font-size:11px;"><?= $i + 1 ?></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm desc-input"
                                            name="items[<?= $i ?>][descripcion]"
                                            value="<?= esc($d->descripcion) ?>">
                                        <input type="hidden" name="items[<?= $i ?>][tipo_item]" value="<?= $d->tipo_item ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-end qty-input"
                                            name="items[<?= $i ?>][cantidad]"
                                            value="<?= $d->cantidad ?>" min="0.0001" step="0.01"
                                            data-max="<?= $d->cantidad ?>">
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm text-end price-input"
                                            name="items[<?= $i ?>][precio_uni]"
                                            value="<?= $d->precio_unitario ?>" min="0" step="0.01">
                                    </td>
                                    <td class="text-end gravada-cell" style="font-size:12px;">
                                        <?= number_format($d->venta_gravada, 2) ?>
                                    </td>
                                    <td class="text-end iva-cell" style="font-size:12px;">
                                        <?= number_format($d->iva_item, 2) ?>
                                    </td>
                                    <td class="text-end total-cell fw-bold" style="font-size:12px;">
                                        <?= number_format($d->venta_gravada + $d->iva_item, 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Quitar línea">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="row justify-content-between align-items-end mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                El monto total de la NC no puede superar el saldo pendiente del documento original
                                (<strong>$<?= number_format($original->saldo, 2) ?></strong>).
                            </small>
                        </div>
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
                                <span class="fw-bold">Total a acreditar:</span>
                                <span id="lblTotalPagar" class="fw-bold fs-5 text-warning">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-warning" id="btnEmitirNc">
                            <i class="fa-solid fa-paper-plane me-1"></i> Emitir Nota de Crédito
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {

    // Fecha / Hora
    const now = new Date();
    $('#fechaDisplay').val(now.toLocaleDateString('es-SV'));
    $('#horaDisplay').val(now.toLocaleTimeString('es-SV'));
    $('#fechaIso').val(now.toISOString().split('T')[0]);
    $('#horaIso').val(now.toTimeString().slice(0, 8));

    // N° control
    $.getJSON('<?= base_url("emision-dte/proximo-numero/05") ?>', data => {
        $('#numeroControlPreview').val(data.numero || '—');
    });

    const saldoOriginal = <?= (float)$original->saldo ?>;

    // Calcular totales iniciales
    calcularTotales();

    // Recalcular al cambiar cantidad o precio
    $(document).on('input', '.qty-input, .price-input', function () {
        calcularFila($(this).closest('tr'));
    });

    function calcularFila($row) {
        const qty   = parseFloat($row.find('.qty-input').val())   || 0;
        const price = parseFloat($row.find('.price-input').val()) || 0;

        const gravada = Math.round(qty * price * 100) / 100;
        const iva     = Math.round(gravada * 0.13 * 100) / 100;
        const total   = Math.round((gravada + iva) * 100) / 100;

        $row.find('.gravada-cell').text(gravada.toFixed(2));
        $row.find('.iva-cell').text(iva.toFixed(2));
        $row.find('.total-cell').text(total.toFixed(2));

        calcularTotales();
    }

    function calcularTotales() {
        let totalGravada = 0, totalIva = 0;
        $('#ncBody tr').each(function () {
            totalGravada += parseFloat($(this).find('.gravada-cell').text()) || 0;
            totalIva     += parseFloat($(this).find('.iva-cell').text())     || 0;
        });
        totalGravada = Math.round(totalGravada * 100) / 100;
        totalIva     = Math.round(totalIva * 100) / 100;
        const total  = Math.round((totalGravada + totalIva) * 100) / 100;

        $('#lblTotalGravada').text('$' + totalGravada.toFixed(2));
        $('#lblTotalIva').text('$' + totalIva.toFixed(2));
        $('#lblTotalPagar').text('$' + total.toFixed(2));

        // Advertir si supera el saldo
        if (total > saldoOriginal + 0.01) {
            $('#lblTotalPagar').addClass('text-danger').removeClass('text-warning');
        } else {
            $('#lblTotalPagar').removeClass('text-danger').addClass('text-warning');
        }
    }

    // Eliminar fila
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        calcularTotales();
    });

    // Submit
    $('#ncForm').on('submit', function (e) {
        e.preventDefault();

        const total = parseFloat($('#lblTotalPagar').text().replace('$', '')) || 0;
        if (total <= 0) {
            Swal.fire('Sin monto', 'El total de la NC debe ser mayor a cero.', 'warning');
            return;
        }
        if (total > saldoOriginal + 0.01) {
            Swal.fire('Monto excede saldo', `El total ($${total.toFixed(2)}) supera el saldo pendiente del documento ($${saldoOriginal.toFixed(2)}).`, 'warning');
            return;
        }

        const items = [];
        let hayError = false;
        $('#ncBody tr').each(function () {
            const desc  = $(this).find('.desc-input').val().trim();
            const qty   = parseFloat($(this).find('.qty-input').val()) || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            if (!desc || qty <= 0 || price <= 0) { hayError = true; return; }
            items.push({
                tipo_item  : parseInt($(this).find('[name*="tipo_item"]').val()) || 1,
                descripcion: desc,
                cantidad   : qty,
                precio_uni : price,
                descuento  : 0,
            });
        });

        if (hayError || items.length === 0) {
            Swal.fire('Líneas incompletas', 'Complete descripción, cantidad y precio en todas las líneas.', 'warning');
            return;
        }

        const motivo = $('#motivoNc').val().trim();

        Swal.fire({
            title: 'Confirmar emisión de NC',
            html : `<div class="text-start" style="font-size:14px;">
                        <p><b>Referencia:</b> <?= esc($original->numero_control) ?></p>
                        ${motivo ? `<p><b>Motivo:</b> ${motivo}</p>` : ''}
                        <hr>
                        <p class="fs-5"><b>Total a acreditar: $${total.toFixed(2)}</b></p>
                    </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Emitir NC',
            cancelButtonText: 'Cancelar',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-warning m-2',
                cancelButton : 'btn btn-secondary m-2',
            },
        }).then(result => {
            if (!result.isConfirmed) return;

            $('#btnEmitirNc').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Enviando...');

            fetch('<?= base_url("emision-dte/nc/store") ?>', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body   : JSON.stringify({
                    original_id        : parseInt($('#originalId').val()),
                    cliente_id         : <?= (int)$original->receptor_id ?>,
                    condicion_operacion: 'contado',
                    fecha_emision      : $('#fechaIso').val(),
                    hora_emision       : $('#horaIso').val(),
                    motivo             : motivo,
                    items              : items,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon : data.estado_mh === 'procesado' ? 'success' : 'info',
                        title: 'Nota de Crédito emitida',
                        html : `<p>${data.numero}</p><p class="fs-5 fw-bold">$${parseFloat(data.total).toFixed(2)}</p>`,
                        timer: 2500,
                        showConfirmButton: true,
                        confirmButtonText: 'Ver detalle',
                    }).then(() => {
                        window.location.href = '<?= base_url("emision-dte") ?>/' + data.factura_id;
                    });
                } else {
                    Swal.fire('Error', data.message ?? 'Error desconocido.', 'error');
                    $('#btnEmitirNc').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Emitir Nota de Crédito');
                }
            })
            .catch(() => {
                Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
                $('#btnEmitirNc').prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-1"></i> Emitir Nota de Crédito');
            });
        });
    });

});
</script>

<?= $this->endSection() ?>
