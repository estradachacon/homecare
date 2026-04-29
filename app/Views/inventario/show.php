<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .info-label { font-size: 12px; color: #6c757d; text-transform: uppercase; font-weight: 600; }
    .info-value  { font-size: 18px; font-weight: 600; }

    .tabla-movimientos { max-height: 500px; overflow-y: auto; overflow-x: auto; border: 1px solid #dee2e6; border-radius: 6px; }
    .tabla-movimientos thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 5; }
    .tabla-movimientos table td,
    .tabla-movimientos table th { padding: 0.3rem 0.5rem; font-size: 13px; line-height: 1.2; vertical-align: middle; }
    .tabla-movimientos tbody tr:hover { background-color: #f1f3f5; }
    .tabla-movimientos table { margin-bottom: 0; }

    tr.fila-apertura td { background-color: #e8f4fd !important; color: #0c5460; font-style: italic; }
    tr.fila-cierre   td { background-color: #1a1a2e !important; color: #ffffff !important; font-style: italic; }

    .lote-card { border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 14px; font-size: 13px; background: #fff; }
    .lote-card .lote-num { font-weight: 700; color: #0d6efd; font-size: 14px; }
    .lote-card .lote-meta { color: #6c757d; font-size: 11px; }
.tabla-lotes-compacta {
    max-height: 420px;
    overflow: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.tabla-lotes-compacta thead th {
    position: sticky;
    top: 0;
    z-index: 3;
    font-size: 12px;
    padding: .4rem .5rem;
    white-space: nowrap;
}

.tabla-lotes-compacta td {
    font-size: 12.5px;
    padding: .35rem .5rem;
    vertical-align: middle;
}

.tabla-lotes-compacta .btn-xs {
    padding: .12rem .32rem;
    font-size: 11px;
}
</style>

<?php
$stock         = $stock ?? 0;
$stockApertura = $stockApertura ?? 0;
$lotes         = $lotes ?? [];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex justify-content-between">
                <div>
                    <h4 class="mb-0">
                        Producto
                        <span class="badge bg-info text-white ms-2">
                            <?= esc($producto->codigo ?? 'SIN CODIGO') ?>
                        </span>
                    </h4>
                    <div class="fw-bold text-uppercase mt-1" style="letter-spacing:1px;">
                        <?= esc($producto->descripcion) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">
                            <small class="text-muted d-block">Estado</small>
                            <?php if (($producto->activo ?? 1) == 1): ?>
                                <span class="badge text-white px-3 py-1" style="background:#15913a;">Activo</span>
                            <?php else: ?>
                                <span class="badge text-dark px-3 py-1" style="background:#e65220;">Inactivo</span>
                            <?php endif; ?>
                            <?php
                                $tipo = $producto->tipo ?? null;
                                $tipoTexto = match((int)$tipo) { 1 => 'Bienes', 2 => 'Servicios', default => 'N/D' };
                            ?>
                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">Tipo</small>
                                <span class="ml-auto fw-semibold"><?= esc($tipoTexto) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-end border rounded px-3 py-2 bg-light h-100">
                            <div class="d-flex align-items-center">
                                <small class="text-muted">Stock actual</small>
                                <span class="fw-bold fs-5 ml-auto <?= $stock > 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($stock, 2) ?>
                                </span>
                            </div>
                            <div class="mt-2 d-flex align-items-center">
                                <small class="text-muted">Costo promedio</small>
                                <span class="fw-bold ml-auto">$<?= number_format($producto->costo_promedio ?? 0, 4) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <!-- Info boxes -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Código</small>
                            <div class="fw-semibold"><?= esc($producto->codigo ?? 'N/D') ?></div>
                            <small class="text-muted mt-2 d-block">Descripción</small>
                            <div class="fw-semibold"><?= esc($producto->descripcion ?? 'N/D') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded h-100">
                            <small class="text-muted">Costo promedio actual</small>
                            <div class="fw-bold fs-5 text-primary">
                                $<?= number_format($producto->costo_promedio ?? 0, 4) ?>
                            </div>
                            <small class="text-muted mt-2 d-block">Última actualización</small>
                            <div class="fw-semibold">
                                <?= !empty($producto->updated_at) ? date('d/m/Y H:i', strtotime($producto->updated_at)) : 'N/D' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── TABS ───────────────────────────────────────────── -->
                <ul class="nav nav-tabs" id="productoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-kardex" data-toggle="tab" href="#pane-kardex" role="tab">
                            <i class="fa-solid fa-table-list mr-1"></i> Kardex
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-lotes" data-toggle="tab" href="#pane-lotes" role="tab">
                            <i class="fa-solid fa-boxes-stacked mr-1"></i> Lotes
                            <?php if (!empty($lotes)): ?>
                                <span class="badge badge-info ml-1"><?= count($lotes) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>

                <div class="tab-content border border-top-0 rounded-bottom p-3" id="productoTabsContent">

                    <!-- ── TAB KARDEX ─────────────────────────────────── -->
                    <div class="tab-pane fade show active" id="pane-kardex" role="tabpanel">

                        <?php $anioActual = date('Y'); ?>
                        <div class="d-flex justify-content-end mb-2">
                            <form method="GET" class="d-flex align-items-center">
                                <label class="me-2 mb-0 text-muted mr-2">Año:</label>
                                <select name="anio" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="" <?= empty($anio) ? 'selected' : '' ?>>Todos</option>
                                    <?php for ($y = $anioActual; $y >= $anioActual - 5; $y--): ?>
                                        <option value="<?= $y ?>" <?= $y == $anio ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </form>
                        </div>

                        <div class="tabla-movimientos">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tipo</th>
                                        <th>Referencia</th>
                                        <th class="text-end">Cantidad</th>
                                        <th class="text-end">Costo Prom.</th>
                                        <th class="text-end">Stock</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($movimientos)):
                                        $stock_acumulado = $stockApertura;
                                        $costo_actual    = 0;
                                        $siglas          = dte_siglas();

                                        function numeroCorto($numero) {
                                            return substr($numero, -6);
                                        }
                                    ?>
                                        <?php if (!empty($anio)): ?>
                                        <tr class="fila-apertura">
                                            <td class="text-muted">—</td>
                                            <td><span class="badge bg-info text-white">APERTURA</span></td>
                                            <td colspan="2" class="fst-italic">Saldo anterior al <?= $anio ?></td>
                                            <td class="text-end text-muted">—</td>
                                            <td class="text-end fw-bold <?= $stockApertura >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($stockApertura, 2) ?>
                                            </td>
                                            <td class="text-muted">31/12/<?= $anio - 1 ?></td>
                                        </tr>
                                        <?php endif; ?>

                                        <?php foreach ($movimientos as $m):
                                            $stock_acumulado += $m->cantidad;
                                            if ($m->cantidad > 0) $costo_actual = $m->costo_unitario;
                                            $stock_actual_mov = $stock_acumulado;
                                            $costo_mov        = $costo_actual;
                                        ?>
                                        <tr>
                                            <td><?= $m->id ?></td>
                                            <td>
                                                <?php
                                                    if ($m->tipo_movimiento === 'compra')       $tipoLabel = 'ENTRADA';
                                                    elseif ($m->tipo_movimiento === 'venta')    $tipoLabel = 'SALIDA';
                                                    elseif ($m->tipo_movimiento === 'ajuste')   $tipoLabel = $m->cantidad >= 0 ? 'ENTRADA' : 'SALIDA';
                                                    else                                         $tipoLabel = strtoupper($m->tipo_movimiento);
                                                ?>
                                                <span class="badge <?= $m->cantidad > 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                                                    <?= $tipoLabel ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($m->referencia_tipo === 'factura' && !empty($m->numero_control)): ?>
                                                    <?php $sigla = $siglas[$m->tipo_dte] ?? $m->tipo_dte; $numero = numeroCorto($m->numero_control); ?>
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <span class="fw-semibold text-primary"><?= $sigla ?> - <?= $numero ?></span>
                                                            <span class="text-muted">|| <?= esc($m->cliente_nombre ?? 'Cliente') ?></span>
                                                        </div>
                                                        <a href="<?= base_url('facturas/' . $m->referencia_id) ?>" class="btn btn-sm btn-light border ms-2" title="Ver factura">👁</a>
                                                    </div>
                                                <?php elseif ($m->referencia_tipo === 'compra' && !empty($m->compra_numero_control)): ?>
                                                    <?php $sigla = $siglas[$m->compra_tipo_dte] ?? 'COMP'; $numero = numeroCorto($m->compra_numero_control); ?>
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <span class="fw-semibold text-success"><?= $sigla ?> - <?= $numero ?></span>
                                                            <span class="text-muted">|| <?= esc($m->proveedor_nombre ?? 'Proveedor') ?></span>
                                                        </div>
                                                        <a href="<?= base_url('purchases/' . $m->referencia_id) ?>" class="btn btn-sm btn-light border ms-2" title="Ver compra">👁</a>
                                                    </div>
                                                <?php else: ?>
                                                    <?= esc($m->referencia_tipo) ?> #<?= $m->referencia_id ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end <?= $m->cantidad > 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($m->cantidad, 2) ?></td>
                                            <td class="text-end text-primary">$<?= number_format($costo_mov, 4) ?></td>
                                            <td class="text-end fw-bold <?= $stock_actual_mov >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($stock_actual_mov, 2) ?></td>
                                            <td>
                                                <?= !empty($m->fecha_documento)
                                                    ? date('d/m/Y', strtotime($m->fecha_documento))
                                                    : date('d/m/Y', strtotime($m->created_at)) ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>

                                        <?php if (!empty($anio)): ?>
                                        <tr class="fila-cierre">
                                            <td>—</td>
                                            <td><span class="badge bg-light text-dark">CIERRE</span></td>
                                            <td colspan="2" class="fst-italic">Saldo al cierre del <?= $anio ?></td>
                                            <td class="text-end">—</td>
                                            <td class="text-end fw-bold <?= $stock_acumulado >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($stock_acumulado, 2) ?></td>
                                            <td>31/12/<?= $anio ?></td>
                                        </tr>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted">Sin movimientos</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div><!-- /pane-kardex -->

                    <!-- ── TAB LOTES ──────────────────────────────────── -->
                    <div class="tab-pane fade" id="pane-lotes" role="tabpanel">

                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="mb-0">
                                <i class="fa-solid fa-boxes-stacked mr-1 text-primary"></i>
                                Lotes del producto
                            </h6>
                            <?php if (tienePermiso('gestionar_lotes_consignaciones')): ?>
                                <button class="btn btn-sm btn-primary" id="btnNuevoLoteProducto">
                                    <i class="fa-solid fa-plus mr-1"></i> Nuevo lote
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($lotes)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-boxes-stacked fa-2x mb-2 d-block opacity-50"></i>
                                Sin lotes registrados para este producto.
                            </div>
                        <?php else: ?>
<div class="table-responsive tabla-lotes-compacta">
    <table class="table table-sm table-hover mb-0" id="tablaLotesProducto">
        <thead class="thead-light">
            <tr>
                <th>Lote</th>
                <th style="width:150px">Vence</th>
                <th style="width:150px">Manufactura</th>
                <th>Descripción</th>
                <th class="text-center" style="width:80px">Estado</th>
                <?php if (tienePermiso('gestionar_lotes_consignaciones')): ?>
                    <th class="text-center" style="width:75px">Acc.</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($lotes as $l): ?>
                <tr>
                    <td>
                        <strong class="text-primary"><?= esc($l->numero_lote) ?></strong>
                    </td>

                    <td>
                        <span class="text-muted">
                            <?= !empty($l->fecha_vencimiento) ? esc($l->fecha_vencimiento) : '—' ?>
                        </span>
                    </td>

                    <td>
                        <span class="text-muted">
                            <?= !empty($l->manufactura) ? esc($l->manufactura) : '—' ?>
                        </span>
                    </td>

                    <td class="text-truncate" style="max-width:260px;">
                        <small title="<?= esc($l->descripcion ?? '') ?>">
                            <?= esc($l->descripcion ?: '—') ?>
                        </small>
                    </td>

                    <td class="text-center">
                        <?php if ($l->activo): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>

                    <?php if (tienePermiso('gestionar_lotes_consignaciones')): ?>
                        <td class="text-center text-nowrap">
                            <button class="btn btn-xs btn-outline-primary btn-editar-lote-prod"
                                data-id="<?= $l->id ?>"
                                data-numero="<?= esc($l->numero_lote) ?>"
                                data-vence="<?= esc($l->fecha_vencimiento ?? '') ?>"
                                data-manufactura="<?= esc($l->manufactura ?? '') ?>"
                                data-desc="<?= esc($l->descripcion ?? '') ?>"
                                title="Editar">
                                <i class="fa-solid fa-edit"></i>
                            </button>

                            <?php if ($l->activo): ?>
                                <button class="btn btn-xs btn-outline-danger btn-desactivar-lote-prod"
                                    data-id="<?= $l->id ?>" title="Desactivar">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            <?php else: ?>
                                <button class="btn btn-xs btn-outline-success btn-activar-lote-prod"
                                    data-id="<?= $l->id ?>" title="Reactivar">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
                        <?php endif; ?>

                    </div><!-- /pane-lotes -->

                </div><!-- /tab-content -->

            </div><!-- /card-body -->
        </div>
    </div>
</div>

<!-- Modal lote (crear / editar) -->
<div class="modal fade" id="modalLoteProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formLoteProducto">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLoteProdTitulo">Nuevo Lote</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="loteProdId" name="id" value="0">
                    <input type="hidden" name="producto_id" value="<?= (int)$producto->id ?>">

                    <div class="form-group">
                        <label class="form-label">Número de Lote <span class="text-danger">*</span></label>
                        <input type="text" name="numero_lote" id="loteProdNumero" class="form-control"
                            placeholder="Ej: L-2024-001" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Vencimiento</label>
                                <input type="text" name="fecha_vencimiento" id="loteProdVence" class="form-control"
                                    placeholder="Ej: Jun 2026, 12/2025…">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Manufactura</label>
                                <input type="text" name="manufactura" id="loteProdManufactura" class="form-control"
                                    placeholder="Ej: Ene 2025, 01/2025…">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="loteProdDesc" class="form-control"
                            placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarLoteProd">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Scroll kardex al fondo al cargar
    const contenedor = document.querySelector('.tabla-movimientos');
    if (contenedor) contenedor.scrollTop = contenedor.scrollHeight;

    // Restaurar tab activo desde hash
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector('[href="' + hash + '"]');
        if (tab) $(tab).tab('show');
    }
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        history.replaceState(null, null, $(e.target).attr('href'));
    });
});

<?php if (tienePermiso('gestionar_lotes_consignaciones')): ?>
$(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function limpiarModal() {
        $('#loteProdId').val(0);
        $('#loteProdNumero').val('');
        $('#loteProdVence').val('');
        $('#loteProdManufactura').val('');
        $('#loteProdDesc').val('');
    }

    $('#btnNuevoLoteProducto').on('click', function () {
        limpiarModal();
        $('#modalLoteProdTitulo').text('Nuevo Lote');
        $('#modalLoteProducto').modal('show');
    });

    $(document).on('click', '.btn-editar-lote-prod', function () {
        const b = $(this).data();
        $('#modalLoteProdTitulo').text('Editar Lote');
        $('#loteProdId').val(b.id);
        $('#loteProdNumero').val(b.numero);
        $('#loteProdVence').val(b.vence || '');
        $('#loteProdManufactura').val(b.manufactura || '');
        $('#loteProdDesc').val(b.desc || '');
        $('#modalLoteProducto').modal('show');
    });

    $('#formLoteProducto').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        const $btn = $('#btnGuardarLoteProd');
        $btn.prop('disabled', true).text('Guardando…');

        fetch('<?= base_url('consignaciones/lotes/guardar') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: fd,
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                $('#modalLoteProducto').modal('hide');
                // Reload staying on lotes tab
                window.location.hash = '#pane-lotes';
                location.reload();
            } else {
                Swal.fire('Error', d.message ?? 'No se pudo guardar.', 'error');
            }
        })
        .catch(() => Swal.fire('Error', 'No se pudo conectar.', 'error'))
        .finally(() => $btn.prop('disabled', false).text('Guardar'));
    });

    $(document).on('click', '.btn-desactivar-lote-prod', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Desactivar lote', text: '¿Desea desactivar este lote?',
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Sí, desactivar', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch('<?= base_url('consignaciones/lotes') ?>/' + id + '/eliminar', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            }).then(r => r.json()).then(d => {
                if (d.success) { window.location.hash = '#pane-lotes'; location.reload(); }
                else Swal.fire('Error', d.message, 'error');
            });
        });
    });

    $(document).on('click', '.btn-activar-lote-prod', function () {
        const id = $(this).data('id');
        const fd = new FormData();
        fd.append('id', id);
        fd.append('activo_toggle', 1);

        fetch('<?= base_url('consignaciones/lotes/guardar') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
            body: fd,
        }).then(r => r.json()).then(d => {
            if (d.success) { window.location.hash = '#pane-lotes'; location.reload(); }
            else Swal.fire('Error', d.message, 'error');
        });
    });
});
<?php endif; ?>
</script>

<?= $this->endSection() ?>
