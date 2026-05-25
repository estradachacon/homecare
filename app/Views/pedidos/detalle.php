<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
if (!function_exists('_nlUnidades')) {
    function _nlUnidades(int $n): string {
        $u = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
              'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis',
              'diecisiete', 'dieciocho', 'diecinueve', 'veinte', 'veintiún',
              'veintidós', 'veintitrés', 'veinticuatro', 'veinticinco', 'veintiséis',
              'veintisiete', 'veintiocho', 'veintinueve'];
        $d = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta',
              'sesenta', 'setenta', 'ochenta', 'noventa'];
        $c = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos',
              'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        if ($n < 30)   return $u[$n];
        if ($n < 100)  { $r = $n % 10; return $r === 0 ? $d[intdiv($n,10)] : $d[intdiv($n,10)] . ' y ' . $u[$r]; }
        if ($n === 100) return 'cien';
        if ($n < 1000) { $r = $n % 100; return $r === 0 ? $c[intdiv($n,100)] : $c[intdiv($n,100)] . ' ' . _nlUnidades($r); }
        if ($n < 2000) { $r = $n % 1000; return 'mil' . ($r === 0 ? '' : ' ' . _nlUnidades($r)); }
        if ($n < 1000000) { $m = intdiv($n,1000); $r = $n % 1000; return _nlUnidades($m) . ' mil' . ($r === 0 ? '' : ' ' . _nlUnidades($r)); }
        if ($n < 2000000) { $r = $n % 1000000; return 'un millón' . ($r === 0 ? '' : ' ' . _nlUnidades($r)); }
        $m = intdiv($n,1000000); $r = $n % 1000000;
        return _nlUnidades($m) . ' millones' . ($r === 0 ? '' : ' ' . _nlUnidades($r));
    }
    function numero_a_letras(float $monto): string {
        $entero   = (int) floor(abs($monto));
        $centavos = (int) round((abs($monto) - $entero) * 100);
        return ucfirst(_nlUnidades($entero)) . ' ' . str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100 dólares';
    }
}
?>

<style>
    .box-totales { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 16px; }
    .box-totales .row-total { font-size: 1.05rem; font-weight: 700; }
    .log-entry { border-left: 3px solid #dee2e6; padding-left: 10px; margin-bottom: 8px; }
    .badge-estado-pendiente  { background:#ffc107; color:#000; }
    .badge-estado-facturada  { background:#28a745; color:#fff; }
    .badge-estado-anulada    { background:#dc3545; color:#fff; }

    /* ── Área de impresión (oculta en pantalla) ──────────────────────────── */
    #printArea { display: none; }

    @media print {
        @page { size: letter portrait; margin: 12mm 15mm; }

        body * { visibility: hidden !important; }
        #printArea { display: block !important; visibility: visible !important;
                     position: fixed; top: 0; left: 0; width: 100%;
                     max-height: 100vh; overflow: visible; }
        #printArea * { visibility: visible !important; }

        /* ── Reset tipografía ── */
        #printArea { font-family: Arial, sans-serif; font-size: 12pt; color: #000; }

        /* ── Cabecera: logo + título ── */
        .pr-header { display: flex; align-items: center; justify-content: space-between;
                     border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 8px; }
        .pr-header img { height: 78px; width: auto; }
        .pr-header-right { text-align: right; }
        .pr-header-right h2 { font-size: 15pt; font-weight: 700; margin: 0 0 2px; }
        .pr-header-right p  { margin: 0; font-size: 11pt; }
        .pr-numero { font-size: 14pt; font-weight: 700; }

        /* ── Bloque info: cliente + pedido ── */
        .pr-info { display: flex; gap: 8px; margin-bottom: 8px; }
        .pr-info-col { flex: 1; border: 1px solid #ccc; border-radius: 3px; padding: 5px 8px; }
        .pr-info-col h6 { font-size: 10pt; font-weight: 700; text-transform: uppercase;
                          letter-spacing: .04em; color: #555; margin: 0 0 4px; border-bottom: 1px solid #ddd; padding-bottom: 2px; }
        .pr-info-col p  { margin: 0 0 3px; font-size: 11pt; }

        /* ── Tabla productos ── */
        .pr-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .pr-table th { background: #222; color: #fff; font-size: 10pt;
                       padding: 5px 7px; text-align: left; }
        .pr-table td { font-size: 11pt; padding: 4px 7px; border-bottom: 1px solid #e0e0e0; }
        .pr-table .txt-r { text-align: right; }
        .pr-table tfoot td { font-weight: 700; border-top: 1.5px solid #000; border-bottom: none; }
        .pr-table tfoot .lbl { text-align: right; color: #444; font-size: 10pt; }
        .pr-total-line { font-size: 13pt; }
        .pr-letras { font-size: 10.5pt; font-style: italic; color: #333; font-weight: normal; }

        /* ── Notas ── */
        .pr-notas { margin-top: 8px; border-top: 1px solid #ccc; padding-top: 5px; font-size: 11pt; }
        .pr-notas strong { font-size: 11pt; }

        /* ── Firma ── */
        .pr-firma { display: flex; justify-content: space-between; margin-top: 24px; }
        .pr-firma-col { text-align: center; width: 42%; }
        .pr-firma-col .linea { border-top: 1px solid #000; padding-top: 2px; font-size: 10pt; margin-top: 28px; }
    }
</style>

<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <h4 class="header-title mb-0 d-inline">
                        Nota de Pedido — <strong><?= esc($pedido->numero) ?></strong>
                    </h4>
                    &nbsp;
                    <?php
                    $badgeClass = [
                        'pendiente' => 'badge-estado-pendiente',
                        'facturada' => 'badge-estado-facturada',
                        'anulada'   => 'badge-estado-anulada',
                    ][$pedido->estado] ?? 'badge-secondary';
                    $badgeLabel = [
                        'pendiente' => 'Pendiente',
                        'facturada' => 'Facturada',
                        'anulada'   => 'Anulada',
                    ][$pedido->estado] ?? $pedido->estado;
                    ?>
                    <span class="badge <?= $badgeClass ?> px-2 py-1"><?= $badgeLabel ?></span>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($pedido->estado === 'pendiente' && tienePermiso('editar_pedidos')): ?>
                        <a href="<?= base_url('pedidos/' . $pedido->id . '/editar') ?>" class="btn btn-primary btn-sm mr-1">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                    <?php endif; ?>
                    <?php if ($pedido->estado !== 'anulada' && tienePermiso('anular_pedidos')): ?>
                        <button id="btnAnular" class="btn btn-danger btn-sm mr-1" data-id="<?= $pedido->id ?>" data-numero="<?= esc($pedido->numero) ?>">
                            <i class="fa-solid fa-ban"></i> Anular
                        </button>
                    <?php endif; ?>
                    <?php if ($pedido->estado !== 'anulada' && tienePermiso('editar_pedidos')): ?>
                        <button id="btnAsociarFactura" class="btn btn-success btn-sm mr-1">
                            <i class="fa-solid fa-link"></i>
                            <?= $pedido->estado === 'facturada' ? 'Cambiar Factura' : 'Asociar Factura' ?>
                        </button>
                    <?php endif; ?>
                    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm mr-1" title="Imprimir media página">
                        <i class="fa-solid fa-print"></i> Imprimir
                    </button>
                    <a href="<?= base_url('pedidos') ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <!-- Datos del cliente -->
                    <div class="col-md-5">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-2 px-3">
                                <h6 class="mb-2 text-muted"><i class="fa-solid fa-user mr-1"></i> Cliente</h6>
                                <p class="mb-1"><strong><?= esc($pedido->cliente_nombre) ?></strong></p>
                                <?php if ($pedido->cliente_tipo_doc && $pedido->cliente_num_doc): ?>
                                    <p class="mb-1 small text-muted"><?= esc($pedido->cliente_tipo_doc) ?>: <?= esc($pedido->cliente_num_doc) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_nrc): ?>
                                    <p class="mb-1 small text-muted">NRC: <?= esc($pedido->cliente_nrc) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_telefono): ?>
                                    <p class="mb-1 small text-muted"><i class="fa-solid fa-phone mr-1"></i><?= esc($pedido->cliente_telefono) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_correo): ?>
                                    <p class="mb-1 small text-muted"><i class="fa-solid fa-envelope mr-1"></i><?= esc($pedido->cliente_correo) ?></p>
                                <?php endif; ?>
                                <?php if ($pedido->cliente_direccion): ?>
                                    <p class="mb-0 small text-muted"><i class="fa-solid fa-location-dot mr-1"></i><?= esc($pedido->cliente_direccion) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Datos del pedido -->
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body py-2 px-3">
                                <h6 class="mb-2 text-muted"><i class="fa-solid fa-file-invoice mr-1"></i> Pedido</h6>
                                <p class="mb-1"><strong>Vendedor:</strong> <?= esc($pedido->vendedor_nombre) ?></p>
                                <p class="mb-1">
                                    <strong>Documento:</strong>
                                    <?php
                                    $docLabel = ['factura' => 'Factura', 'credito_fiscal' => 'Crédito Fiscal', 'nota_remision' => 'Nota de Remisión'];
                                    echo $docLabel[$pedido->tipo_documento] ?? esc($pedido->tipo_documento);
                                    ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Pago:</strong>
                                    <?php if ($pedido->tipo_pago === 'credito'): ?>
                                        Crédito &mdash; <?= $pedido->dias_credito ?> días
                                    <?php else: ?>
                                        Contado
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido->created_at)) ?></p>
                                <?php if ($pedido->factura_numero): ?>
                                    <p class="mb-0">
                                        <strong>Factura:</strong>
                                        <a href="<?= base_url('facturas/' . $pedido->factura_id) ?>">
                                            <?= esc($pedido->factura_numero) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="col-md-3">
                        <div class="box-totales">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal:</span>
                                <span>$<?= number_format($pedido->subtotal, 2) ?></span>
                            </div>
                            <?php if ($pedido->iva > 0): ?>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>IVA (13%):</span>
                                    <span>$<?= number_format($pedido->iva, 2) ?></span>
                                </div>
                            <?php endif; ?>
                            <hr class="my-1">
                            <div class="d-flex justify-content-between row-total">
                                <span>Total:</span>
                                <span class="text-primary">$<?= number_format($pedido->total, 2) ?></span>
                            </div>
                            <div class="mt-1" style="font-size:0.78rem;color:#555;font-style:italic;line-height:1.3;">
                                <?= numero_a_letras((float)$pedido->total) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($pedido->notas): ?>
                    <div class="alert alert-light border mb-3">
                        <strong><i class="fa-solid fa-note-sticky mr-1"></i>Notas:</strong> <?= nl2br(esc($pedido->notas)) ?>
                    </div>
                <?php endif; ?>

                <!-- Tabla de productos -->
                <h6 class="text-muted mb-2">Productos</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $i => $d): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($d->producto_codigo) ?></td>
                                    <td><?= esc($d->producto_nombre) ?></td>
                                    <td class="text-end"><?= number_format($d->cantidad, 2) ?></td>
                                    <td class="text-end">$<?= number_format($d->precio_unitario, 2) ?></td>
                                    <td class="text-end">$<?= number_format($d->subtotal, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end fw-bold">$<?= number_format($pedido->subtotal, 2) ?></td>
                            </tr>
                            <?php if ($pedido->iva > 0): ?>
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">IVA (13%):</td>
                                    <td class="text-end fw-bold">$<?= number_format($pedido->iva, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td colspan="5" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold">$<?= number_format($pedido->total, 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-muted small" style="font-style:italic;">
                                    <strong>Son:</strong> <?= numero_a_letras((float)$pedido->total) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Lotes de NE asociada -->
                <?php if (!empty($lotesNE)): ?>
                <h6 class="text-muted mt-4 mb-2">
                    <i class="fa-solid fa-tags mr-1"></i> Lotes de la Nota de Envío
                </h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm" style="font-size:.82rem;">
                        <thead class="thead-light">
                            <tr>
                                <th>NE</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lotesNE as $l): ?>
                            <tr>
                                <td><span class="badge badge-secondary"><?= esc($l->ne_numero) ?></span></td>
                                <td><code><?= esc($l->producto_codigo) ?></code></td>
                                <td><?= esc($l->producto_nombre) ?></td>
                                <td><strong><?= esc($l->numero_lote) ?></strong></td>
                                <td>
                                    <?php
                                    if ($l->fecha_vencimiento) {
                                        $diff = (strtotime($l->fecha_vencimiento) - time()) / 86400;
                                        $cls  = $diff < 30 ? 'text-danger' : ($diff < 90 ? 'text-warning' : '');
                                        echo '<span class="' . $cls . '">' . date('d/m/Y', strtotime($l->fecha_vencimiento)) . '</span>';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td class="text-end"><?= number_format((float)$l->cantidad, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Log de actividad -->
                <h6 class="text-muted mt-4 mb-2"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Actividad</h6>
                <?php if (empty($log)): ?>
                    <p class="text-muted small">Sin actividad registrada.</p>
                <?php else: ?>
                    <?php foreach ($log as $entry): ?>
                        <div class="log-entry">
                            <span class="fw-bold small"><?= esc($entry->accion) ?></span>
                            <?php if ($entry->detalle): ?>
                                <span class="text-muted small"> — <?= esc($entry->detalle) ?></span>
                            <?php endif; ?>
                            <br>
                            <span class="text-muted" style="font-size:0.78rem;">
                                <?= esc($entry->user_nombre) ?> — <?= date('d/m/Y H:i', strtotime($entry->created_at)) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════ ÁREA DE IMPRESIÓN ═══════════════════════════ -->
<div id="printArea">

    <!-- Cabecera: logo + número -->
    <div class="pr-header">
        <?php if (setting('logo')): ?>
            <img src="<?= base_url('upload/settings/' . setting('logo')) ?>" alt="Logo">
        <?php else: ?>
            <span style="font-size:13pt;font-weight:700;"><?= esc(setting('company_name') ?? '') ?></span>
        <?php endif; ?>
        <div class="pr-header-right">
            <h2>NOTA DE PEDIDO</h2>
            <p class="pr-numero"><?= esc($pedido->numero) ?></p>
            <p>Fecha: <?= date('d/m/Y', strtotime($pedido->created_at)) ?></p>
            <p>Estado:
                <?php
                $printEstado = ['pendiente' => 'Pendiente', 'facturada' => 'Facturada', 'anulada' => 'ANULADA'];
                echo $printEstado[$pedido->estado] ?? esc($pedido->estado);
                ?>
            </p>
        </div>
    </div>

    <!-- Info: cliente + pedido -->
    <div class="pr-info">
        <div class="pr-info-col">
            <h6>Cliente</h6>
            <p><strong><?= esc($pedido->cliente_nombre) ?></strong></p>
            <?php if ($pedido->cliente_tipo_doc && $pedido->cliente_num_doc): ?>
                <p><?= esc($pedido->cliente_tipo_doc) ?>: <?= esc($pedido->cliente_num_doc) ?></p>
            <?php endif; ?>
            <?php if ($pedido->cliente_nrc): ?>
                <p>NRC: <?= esc($pedido->cliente_nrc) ?></p>
            <?php endif; ?>
            <?php if ($pedido->cliente_telefono): ?>
                <p>Tel: <?= esc($pedido->cliente_telefono) ?></p>
            <?php endif; ?>
            <?php if ($pedido->cliente_direccion): ?>
                <p><?= esc($pedido->cliente_direccion) ?></p>
            <?php endif; ?>
        </div>
        <div class="pr-info-col">
            <h6>Detalle del pedido</h6>
            <p><strong>Vendedor:</strong> <?= esc($pedido->vendedor_nombre) ?></p>
            <p>
                <strong>Documento:</strong>
                <?php
                $docLabel = ['factura' => 'Factura', 'credito_fiscal' => 'Crédito Fiscal', 'nota_remision' => 'Nota de Remisión'];
                echo $docLabel[$pedido->tipo_documento] ?? esc($pedido->tipo_documento);
                ?>
            </p>
            <p>
                <strong>Pago:</strong>
                <?php if ($pedido->tipo_pago === 'credito'): ?>
                    Crédito — <?= $pedido->dias_credito ?> días
                <?php else: ?>
                    Contado
                <?php endif; ?>
            </p>
            <?php if ($pedido->factura_numero): ?>
                <p><strong>Factura:</strong> <?= esc($pedido->factura_numero) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla de productos -->
    <table class="pr-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th class="txt-r">Cant.</th>
                <th class="txt-r">P. Unit.</th>
                <th class="txt-r">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $i => $d): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($d->producto_codigo) ?></td>
                    <td><?= esc($d->producto_nombre) ?></td>
                    <td class="txt-r"><?= number_format($d->cantidad, 2) ?></td>
                    <td class="txt-r">$<?= number_format($d->precio_unitario, 2) ?></td>
                    <td class="txt-r">$<?= number_format($d->subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="lbl">Subtotal:</td>
                <td class="txt-r">$<?= number_format($pedido->subtotal, 2) ?></td>
            </tr>
            <?php if ($pedido->iva > 0): ?>
                <tr>
                    <td colspan="5" class="lbl">IVA (13%):</td>
                    <td class="txt-r">$<?= number_format($pedido->iva, 2) ?></td>
                </tr>
            <?php endif; ?>
            <tr class="pr-total-line">
                <td colspan="5" class="lbl">TOTAL:</td>
                <td class="txt-r">$<?= number_format($pedido->total, 2) ?></td>
            </tr>
            <tr>
                <td colspan="6" class="pr-letras" style="border-top:none;">
                    Son: <?= numero_a_letras((float)$pedido->total) ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <?php if ($pedido->notas): ?>
        <div class="pr-notas">
            <strong>Notas:</strong> <?= nl2br(esc($pedido->notas)) ?>
        </div>
    <?php endif; ?>

    <!-- Líneas de firma -->
    <div class="pr-firma">
        <div class="pr-firma-col">
            <div class="linea">Entregado por</div>
        </div>
        <div class="pr-firma-col">
            <div class="linea">Recibido por</div>
        </div>
    </div>

</div>
<!-- ══════════════════════════════════════════════════════════════════════════ -->

<!-- Modal Asociar Factura (mejorado) -->
<div class="modal fade" id="modalFactura" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header" style="background:#1a7f5a;color:#fff;">
                <div>
                    <h5 class="modal-title mb-0">
                        <i class="fa-solid fa-link mr-2"></i>
                        <?= $pedido->estado === 'facturada' ? 'Cambiar Factura' : 'Asociar Factura' ?>
                    </h5>
                    <small style="opacity:.85;">
                        Cliente: <strong><?= esc($pedido->cliente_nombre) ?></strong>
                    </small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:1;">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body pb-2">

                <!-- Buscador -->
                <div class="input-group input-group-sm mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                    </div>
                    <input type="text" id="buscarFactura" class="form-control"
                           placeholder="Filtrar por número de control…">
                    <div class="input-group-append">
                        <span class="input-group-text bg-white text-muted small" id="contadorFacturas"></span>
                    </div>
                </div>

                <!-- Estados del contenedor -->
                <div id="facturaListContainer" style="max-height:380px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px;">

                    <div id="facturaLoading" class="text-center py-5">
                        <i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i>
                        <p class="text-muted mt-2 mb-0 small">Cargando facturas disponibles…</p>
                    </div>

                    <table class="table table-sm table-hover mb-0 d-none" id="tablaFacturas">
                        <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Correlativo</th>
                                <th>Tipo</th>
                                <th>Número de control</th>
                                <th>Fecha</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyFacturas"></tbody>
                    </table>

                    <div id="facturaVacio" class="text-center py-5 d-none">
                        <i class="fa-solid fa-inbox fa-2x text-muted"></i>
                        <p class="text-muted mt-2 mb-0 small">No hay facturas disponibles sin asociar para este cliente.</p>
                    </div>

                </div>

                <!-- Alerta de error AJAX -->
                <div id="facturaAjaxError" class="alert alert-danger alert-sm mt-2 mb-0 d-none" style="font-size:.84rem;"></div>

                <!-- Factura seleccionada -->
                <div id="facturaSeleccionada" class="d-none mt-3 p-2 rounded"
                     style="background:#e8f5e9;border:1px solid #a5d6a7;font-size:.86rem;">
                    <i class="fa-solid fa-circle-check text-success mr-1"></i>
                    <strong>Seleccionada:</strong>
                    <span id="facturaSelText" class="ml-1"></span>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnConfirmarFactura" disabled>
                    <i class="fa-solid fa-link"></i> Confirmar asociación
                </button>
            </div>

        </div>
    </div>
</div>

<script>
$(function () {

    // ── Anular ─────────────────────────────────────────────────────────────
    $('#btnAnular').on('click', function () {
        const id     = $(this).data('id');
        const numero = $(this).data('numero');
        Swal.fire({
            title: '¿Anular nota?',
            html: `¿Está seguro de anular <strong>${numero}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch(`<?= base_url('pedidos') ?>/${id}/anular`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Anulada', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    });

    // ── Asociar factura ────────────────────────────────────────────────────
    let facturaIdSel  = null;
    let listaFacturas = [];

    function renderFacturas(lista) {
        const tbody = $('#tbodyFacturas').empty();
        $('#contadorFacturas').text(lista.length + ' encontradas');

        if (lista.length === 0) {
            $('#tablaFacturas').addClass('d-none');
            $('#facturaVacio').removeClass('d-none');
            return;
        }
        $('#facturaVacio').addClass('d-none');
        lista.forEach(f => {
            const sigla = f.sigla
                ? `<span class="badge badge-info mr-1">${f.sigla}</span>`
                : '';
            tbody.append(`
                <tr class="fila-factura" data-id="${f.id}" style="cursor:pointer;">
                    <td class="text-center text-muted">
                        <i class="fa-regular fa-circle fila-check"></i>
                    </td>
                    <td><span class="badge badge-dark">${f.correlativo}</span></td>
                    <td>${sigla}</td>
                    <td class="small text-monospace">${f.numero}</td>
                    <td class="small">${f.fecha}</td>
                    <td class="text-right font-weight-bold">$${f.total}</td>
                </tr>
            `);
        });
        $('#tablaFacturas').removeClass('d-none');
    }

    function resetModal() {
        facturaIdSel = null;
        listaFacturas = [];
        $('#buscarFactura').val('');
        $('#contadorFacturas').text('');
        $('#tablaFacturas').addClass('d-none');
        $('#tbodyFacturas').empty();
        $('#facturaVacio').addClass('d-none');
        $('#facturaAjaxError').addClass('d-none').text('');
        $('#facturaSeleccionada').addClass('d-none');
        $('#facturaSelText').text('');
        $('#btnConfirmarFactura').prop('disabled', true);
        $('#facturaLoading').removeClass('d-none');
    }

    $('#btnAsociarFactura').on('click', function () {
        resetModal();
        $('#modalFactura').modal('show');

        fetch(`<?= base_url('pedidos/facturas-cliente/' . $pedido->cliente_id) ?>?pedido_id=<?= $pedido->id ?>`)
            .then(r => r.json())
            .then(data => {
                listaFacturas = data.results || [];
                $('#facturaLoading').addClass('d-none');
                renderFacturas(listaFacturas);
            })
            .catch(() => {
                $('#facturaLoading').addClass('d-none');
                $('#facturaAjaxError').removeClass('d-none').text('Error al cargar las facturas. Intente de nuevo.');
            });
    });

    // Seleccionar fila
    $(document).on('click', '.fila-factura', function () {
        const id = parseInt($(this).data('id'));
        const f  = listaFacturas.find(x => parseInt(x.id) === id);
        if (!f) return;

        $('.fila-factura').removeClass('table-success')
            .find('.fila-check').removeClass('fa-check-circle text-success').addClass('fa-circle');
        $(this).addClass('table-success')
            .find('.fila-check').removeClass('fa-circle').addClass('fa-check-circle text-success');

        facturaIdSel = id;
        $('#facturaSelText').text(f.correlativo + '  ·  ' + f.numero + '  ·  $' + f.total);
        $('#facturaSeleccionada').removeClass('d-none');
        $('#btnConfirmarFactura').prop('disabled', false);
    });

    // Filtro de búsqueda en tiempo real
    $('#buscarFactura').on('input', function () {
        const q = $(this).val().toLowerCase().trim();
        const filtradas = q
            ? listaFacturas.filter(f => f.numero.toLowerCase().includes(q))
            : listaFacturas;

        // Deselect si el ítem actual ya no está en la lista filtrada
        if (facturaIdSel && !filtradas.find(f => parseInt(f.id) === facturaIdSel)) {
            facturaIdSel = null;
            $('#facturaSeleccionada').addClass('d-none');
            $('#btnConfirmarFactura').prop('disabled', true);
        }
        renderFacturas(filtradas);

        // Re-highlight si sigue seleccionado
        if (facturaIdSel) {
            $(`.fila-factura[data-id="${facturaIdSel}"]`)
                .addClass('table-success')
                .find('.fila-check').removeClass('fa-circle').addClass('fa-check-circle text-success');
        }
    });

    // Confirmar asociación
    $('#btnConfirmarFactura').on('click', function () {
        if (!facturaIdSel) return;
        const btn = $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Asociando…');

        fetch(`<?= base_url('pedidos/' . $pedido->id . '/asociar-factura') ?>`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ factura_id: facturaIdSel }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $('#modalFactura').modal('hide');
                Swal.fire({
                    icon: 'success', title: 'Listo',
                    text: data.message, timer: 1800, showConfirmButton: false,
                }).then(() => location.reload());
            } else {
                $('#facturaAjaxError').removeClass('d-none').text(data.message);
                btn.prop('disabled', false).html('<i class="fa-solid fa-link"></i> Confirmar asociación');
            }
        })
        .catch(() => {
            $('#facturaAjaxError').removeClass('d-none').text('Error de conexión al asociar.');
            btn.prop('disabled', false).html('<i class="fa-solid fa-link"></i> Confirmar asociación');
        });
    });

});
</script>

<?= $this->endSection() ?>
