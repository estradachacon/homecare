<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .info-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .info-value {
        font-size: 18px;
        font-weight: 600;
    }

    .tabla-movimientos {
        max-height: 500px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }

    .tabla-movimientos thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 5;
    }

    .tabla-movimientos table td,
    .tabla-movimientos table th {
        padding: 0.3rem 0.5rem;
        font-size: 13px;
        line-height: 1.2;
        vertical-align: middle;
    }

    .tabla-movimientos tbody tr:hover {
        background-color: #f1f3f5;
    }

    .tabla-movimientos table {
        margin-bottom: 0;
    }

    /* 🔥 FILAS ESPECIALES */
    tr.fila-apertura td {
        background-color: #e8f4fd !important;
        color: #0c5460;
        font-style: italic;
    }

    tr.fila-cierre td {
        background-color: #1a1a2e !important;
        color: #ffffff !important;
        font-style: italic;
    }
</style>

<?php
$stock         = $stock ?? 0;
$stockApertura = $stockApertura ?? 0;
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

                    <div class="fw-bold text-uppercase mt-1" style="letter-spacing: 1px;">
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

                            if ($tipo == 1) {
                                $tipoTexto = 'Bienes';
                            } elseif ($tipo == 2) {
                                $tipoTexto = 'Servicios';
                            } else {
                                $tipoTexto = 'N/D';
                            }
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
                                <span class="fw-bold ml-auto">
                                    $<?= number_format($producto->costo_promedio ?? 0, 4) ?>
                                </span>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="card-body">

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

                <!-- KARDEX -->
                <div class="table-responsive">

                    <?php $anioActual = date('Y'); ?>

                    <div class="d-flex justify-content-end mb-2">
                        <form method="GET" class="d-flex align-items-center">

                            <label class="me-2 mb-0 text-muted mr-2">Año:</label>

                            <select name="anio" class="form-select form-select-sm" onchange="this.form.submit()">

                                <option value="" <?= empty($anio) ? 'selected' : '' ?>>Todos</option>

                                <?php for ($y = $anioActual; $y >= $anioActual - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y == $anio ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
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

                                <?php if (!empty($movimientos)): ?>

                                    <?php
                                    // 🔥 ARRANCA DESDE EL SALDO ANTERIOR, NO DESDE 0
                                    $stock_acumulado = $stockApertura;
                                    $costo_actual    = 0;
                                    $siglas          = dte_siglas();
                                    ?>

                                    <?php
                                    function numeroCorto($numero) {
                                        return substr($numero, -6);
                                    }
                                    ?>

                                    <!-- 🔥 FILA APERTURA (solo si hay año filtrado y hay saldo previo) -->
                                    <?php if (!empty($anio)): ?>
                                    <tr class="fila-apertura">
                                        <td class="text-muted">—</td>
                                        <td>
                                            <span class="badge bg-info text-white">APERTURA</span>
                                        </td>
                                        <td colspan="2" class="fst-italic">
                                            Saldo anterior al <?= $anio ?>
                                        </td>
                                        <td class="text-end text-muted">—</td>
                                        <td class="text-end fw-bold <?= $stockApertura >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($stockApertura, 2) ?>
                                        </td>
                                        <td class="text-muted">31/12/<?= $anio - 1 ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <!-- 🔥 MOVIMIENTOS DEL AÑO -->
                                    <?php foreach ($movimientos as $m): ?>

                                        <?php
                                        $stock_acumulado += $m->cantidad;

                                        if ($m->cantidad > 0) {
                                            $costo_actual = $m->costo_unitario;
                                        }

                                        $stock_actual_mov = $stock_acumulado;
                                        $costo_mov        = $costo_actual;
                                        ?>

                                        <tr>
                                            <td><?= $m->id ?></td>

                                            <td>
                                                <?php
                                                if ($m->tipo_movimiento === 'compra') {
                                                    $tipoLabel = 'ENTRADA';
                                                } elseif ($m->tipo_movimiento === 'venta') {
                                                    $tipoLabel = 'SALIDA';
                                                } elseif ($m->tipo_movimiento === 'ajuste') {
                                                    $tipoLabel = $m->cantidad >= 0 ? 'ENTRADA' : 'SALIDA';
                                                } else {
                                                    $tipoLabel = strtoupper($m->tipo_movimiento);
                                                }
                                                ?>
                                                <span class="badge <?= $m->cantidad > 0 ? 'bg-success' : 'bg-danger' ?> text-white">
                                                    <?= $tipoLabel ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if ($m->referencia_tipo === 'factura' && !empty($m->numero_control)): ?>

                                                    <?php
                                                    $sigla  = $siglas[$m->tipo_dte] ?? $m->tipo_dte;
                                                    $numero = numeroCorto($m->numero_control);
                                                    ?>

                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <span class="fw-semibold text-primary"><?= $sigla ?> - <?= $numero ?></span>
                                                            <span class="text-muted">|| <?= esc($m->cliente_nombre ?? 'Cliente') ?></span>
                                                        </div>
                                                        <a href="<?= base_url('facturas/' . $m->referencia_id) ?>"
                                                            class="btn btn-sm btn-light border ms-2" title="Ver factura">👁</a>
                                                    </div>

                                                <?php elseif ($m->referencia_tipo === 'compra' && !empty($m->compra_numero_control)): ?>

                                                    <?php
                                                    $sigla  = $siglas[$m->compra_tipo_dte] ?? 'COMP';
                                                    $numero = numeroCorto($m->compra_numero_control);
                                                    ?>

                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <span class="fw-semibold text-success"><?= $sigla ?> - <?= $numero ?></span>
                                                            <span class="text-muted">|| <?= esc($m->proveedor_nombre ?? 'Proveedor') ?></span>
                                                        </div>
                                                        <a href="<?= base_url('purchases/' . $m->referencia_id) ?>"
                                                            class="btn btn-sm btn-light border ms-2" title="Ver compra">👁</a>
                                                    </div>

                                                <?php else: ?>
                                                    <?= esc($m->referencia_tipo) ?> #<?= $m->referencia_id ?>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-end <?= $m->cantidad > 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($m->cantidad, 2) ?>
                                            </td>

                                            <td class="text-end text-primary">
                                                $<?= number_format($costo_mov, 4) ?>
                                            </td>

                                            <td class="text-end fw-bold <?= $stock_actual_mov >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($stock_actual_mov, 2) ?>
                                            </td>

                                            <td>
                                                <?= !empty($m->fecha_documento)
                                                    ? date('d/m/Y', strtotime($m->fecha_documento))
                                                    : date('d/m/Y', strtotime($m->created_at)) ?>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                    <!-- 🔥 FILA CIERRE (solo si hay año filtrado) -->
                                    <?php if (!empty($anio)): ?>
                                    <tr class="fila-cierre">
                                        <td>—</td>
                                        <td>
                                            <span class="badge bg-light text-dark">CIERRE</span>
                                        </td>
                                        <td colspan="2" class="fst-italic">
                                            Saldo al cierre del <?= $anio ?>
                                        </td>
                                        <td class="text-end">—</td>
                                        <td class="text-end fw-bold <?= $stock_acumulado >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($stock_acumulado, 2) ?>
                                        </td>
                                        <td>31/12/<?= $anio ?></td>
                                    </tr>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Sin movimientos
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const contenedor = document.querySelector('.tabla-movimientos');
        if (contenedor) {
            contenedor.scrollTop = contenedor.scrollHeight;
        }
    });
</script>

<?= $this->endSection() ?>