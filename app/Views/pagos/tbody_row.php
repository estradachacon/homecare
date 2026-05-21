<?php if (!empty($pagos)): ?>
    <?php foreach ($pagos as $pago): ?>
        <tr class="pago-mobile-row" data-href="<?= base_url('payments/' . $pago->id) ?>">
            <td class="pago-id-cell" data-label="Pago">
                <span class="badge bg-light text-dark">#<?= esc($pago->id) ?></span>
            </td>

            <td class="pago-fecha-cell" data-label="Fecha">
                <span>
                    <?= date('d/m/Y', strtotime($pago->fecha_pago)) ?>
                    <small class="text-muted d-block">
                        <?= date('H:i', strtotime($pago->created_at)) ?>
                    </small>
                </span>
            </td>

            <td class="pago-cliente-cell" data-label="Cliente">
                <?= esc($pago->cliente_nombre ?? 'Sin cliente') ?>
            </td>

            <td class="pago-forma-cell" data-label="Forma">
                <?= esc(ucfirst($pago->forma_pago)) ?>
            </td>

            <td class="text-end pago-total-cell" data-label="Total">
                <div class="pago-total-content">

                <div class="fw-semibold fs-6">
                    $<?= number_format($pago->total, 2) ?>
                </div>

                <?php if ($pago->total_anulado > 0): ?>

                    <div class="small text-muted mt-1">
                        Anulado:
                        <span class="fw-semibold">
                            - $<?= number_format($pago->total_anulado, 2) ?>
                        </span>
                    </div>

                    <div class="small mt-1">
                        <span class="text-muted">Total efectivo:</span>
                        <span class="fw-bold">
                            $<?= number_format($pago->total_aplicado, 2) ?>
                        </span>
                    </div>

                <?php endif; ?>
                </div>

            </td>

            <td class="text-center pago-estado-cell" data-label="Estado">

                <div class="d-flex flex-column align-items-center gap-1 text-white">

                    <?php if ($pago->anulado): ?>

                        <span class="badge bg-danger w-100 mb-1">
                            <i class="fa-solid fa-ban me-1"></i> Anulado
                        </span>

                    <?php else: ?>

                        <span class="badge bg-success w-100 mb-1">
                            <i class="fa-solid fa-check me-1"></i> Aplicado
                        </span>

                        <?php if (!empty($pago->total_retencion) && $pago->total_retencion > 0): ?>
                            <span class="badge w-100 mb-1 text-white" style="background:#c0392b;" title="Retención: $<?= number_format($pago->total_retencion, 2) ?>">
                                <i class="fa-solid fa-percent me-1"></i> Con retención
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($pago->total_anulado) && $pago->total_anulado > 0): ?>
                            <span class="badge bg-warning text-dark w-100">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Con facturas anuladas
                            </span>
                        <?php endif; ?>

                    <?php endif; ?>

                </div>

            </td>

            <td class="text-center pago-menu-cell" data-label="Menu">
                <a href="<?= base_url('payments/' . $pago->id) ?>"
                    class="btn btn-sm btn-info">
                    <i class="fa-solid fa-eye"></i>
                </a>
            </td>

        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="8" class="text-center">
            No hay pagos registrados
        </td>
    </tr>
<?php endif; ?>
