<?php if (!empty($compras)): ?>
    <?php foreach ($compras as $compra): ?>
        <?php
        $siglas       = dte_siglas();
        $descripciones = dte_descripciones();
        $codigo       = $compra->tipo_dte ?? null;
        $sigla        = $siglas[$codigo] ?? null;
        $descripcion  = $sigla ? ($descripciones[$sigla] ?? null) : null;
        $saldo        = (float)($compra->saldo ?? 0);
        $total        = (float)($compra->total_pagar ?? 0);
        $condicion    = $compra->condicion_operacion ?? 1;
        ?>
        <tr>
            <td class="text-center"><?= esc(substr($compra->numero_control, -6)) ?></td>
            <td>
                <?php if ($sigla && $descripcion): ?>
                    <span class="badge badge-estado1 bg-info text-white"><?= esc($sigla) ?></span>
                    <br><small class="text-muted"><?= esc($descripcion) ?></small>
                <?php else: ?>
                    <span class="text-muted">Desconocido</span>
                <?php endif; ?>
            </td>
            <td><?= esc($compra->proveedor_nombre ?? 'Sin proveedor') ?></td>
            <td class="text-center"><?= date('d/m/Y', strtotime($compra->fecha_emision)) ?></td>
            <td class="text-center">
                <?php if ($condicion == 1): ?>
                    <span class="badge badge-estado bg-success text-white">Contado</span>
                <?php elseif ($condicion == 2): ?>
                    <span class="badge badge-estado bg-warning text-dark">Crédito</span>
                <?php else: ?>
                    <span class="badge badge-estado bg-secondary text-white">N/D</span>
                <?php endif; ?>
            </td>
            <td class="text-end">$ <?= number_format($total, 2) ?></td>
            <td class="text-end <?= $saldo > 0 ? 'text-danger fw-semibold' : 'text-success' ?>">
                $ <?= number_format($saldo, 2) ?>
            </td>
            <td class="text-center">
                <?php if (($compra->anulada ?? 0) == 1): ?>
                    <span class="badge badge-estado bg-danger text-white">Anulada</span>
                <?php elseif ($saldo <= 0): ?>
                    <span class="badge badge-estado bg-info text-white">
                        <i class="fa-solid fa-check-circle"></i> Pagada
                    </span>
                <?php elseif ($saldo < $total): ?>
                    <span class="badge badge-estado bg-warning text-dark">Parcial</span>
                <?php else: ?>
                    <span class="badge badge-estado bg-warning text-dark">Activa</span>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <a href="<?= base_url('purchases/' . $compra->id) ?>" class="btn btn-sm btn-info">
                    <i class="fa-solid fa-eye"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="9" class="text-center text-muted py-3">No hay compras registradas</td>
    </tr>
<?php endif; ?>