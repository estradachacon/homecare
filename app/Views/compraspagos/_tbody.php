<?php if (!empty($pagos)): ?>
    <?php foreach ($pagos as $pago): ?>
        <?php
        $total   = (float)($pago->total ?? 0);
        $anulado = ($pago->anulado ?? 0) == 1;
        ?>
        <tr>
            <td class="text-center">
                <?= esc($pago->numero_pago ?? 'N/D') ?>
            </td>
            <td>
                <?= esc($pago->proveedor_nombre ?? 'Sin proveedor') ?>
            </td>
            <td class="text-center">
                <?= $pago->fecha_pago ? date('d/m/Y', strtotime($pago->fecha_pago)) : 'N/D' ?>
            </td>
            <td class="text-center">
                <?php
                $formas = [
                    'efectivo'     => ['label' => 'Efectivo',      'class' => 'text-white bg-success'],
                    'transferencia'=> ['label' => 'Transferencia', 'class' => 'text-white bg-primary'],
                    'cheque'       => ['label' => 'Cheque',        'class' => 'text-white bg-info'],
                    'tarjeta'      => ['label' => 'Tarjeta',       'class' => 'bg-warning text-dark'],
                ];
                $forma = $formas[$pago->forma_pago] ?? ['label' => $pago->forma_pago ?? 'N/D', 'class' => 'bg-secondary'];
                ?>
                <span class="badge badge-estado1 <?= $forma['class'] ?>">
                    <?= $forma['label'] ?>
                </span>
            </td>
            <td class="text-end fw-semibold">
                $ <?= number_format($total, 2) ?>
            </td>
            <td class="text-center">
                <?php if ($anulado): ?>
                    <span class="badge badge-estado bg-danger text-white">Anulado</span>
                <?php else: ?>
                    <span class="badge badge-estado bg-success text-white">Activo</span>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <a href="<?= base_url('compraspagos/' . $pago->id) ?>" class="btn btn-sm btn-info">
                    <i class="fa-solid fa-eye"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="text-center text-muted py-3">No hay pagos registrados</td>
    </tr>
<?php endif; ?>