<?php if (!empty($pagos)): ?>
    <?php foreach ($pagos as $pago): ?>
        <tr>
            <td>
                <?= esc($pago->id) ?>
            </td>

            <td>
                <?= date('d/m/Y', strtotime($pago->fecha_pago)) ?>
                <br>
                <small class="text-muted">
                    <?= date('H:i', strtotime($pago->created_at)) ?>
                </small>
            </td>

            <td>
                <?= esc($pago->cliente_nombre ?? 'Sin cliente') ?>
            </td>

            <td>
                <?= esc(ucfirst($pago->forma_pago)) ?>
            </td>

            <td class="text-end">

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

            </td>

            <td class="text-center">

                <div class="d-flex flex-column align-items-center gap-1 text-white">

                    <?php if ($pago->anulado): ?>

                        <span class="badge bg-danger w-100 mb-1">
                            <i class="fa-solid fa-ban me-1"></i> Anulado
                        </span>

                    <?php else: ?>

                        <span class="badge bg-success w-100 mb-1">
                            <i class="fa-solid fa-check me-1"></i> Aplicado
                        </span>

                        <?php if (!empty($pago->total_anulado) && $pago->total_anulado > 0): ?>

                            <span class="badge bg-warning text-dark w-100">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Con facturas anuladas
                            </span>

                        <?php endif; ?>

                    <?php endif; ?>

                </div>

            </td>

            <td class="text-center">
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