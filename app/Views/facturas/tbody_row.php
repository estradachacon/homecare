<?php foreach ($facturas as $factura): ?>
    <tr>
        <td class="text-center">
            <?= esc(substr($factura->numero_control, -6)) ?>
        </td>

        <td>
            <?php
            $siglas = dte_siglas();
            $descripciones = dte_descripciones();

            $codigo = $factura->tipo_dte;
            $sigla = $siglas[$codigo] ?? null;
            $descripcion = $sigla ? ($descripciones[$sigla] ?? null) : null;
            ?>

            <?php if ($sigla && $descripcion): ?>
                <span class="badge bg-info text-white">
                    <?= esc($sigla) ?>
                </span>
                <br>
                <small class="text-muted">
                    <?= esc($descripcion) ?>
                </small>
            <?php else: ?>
                <span class="text-muted">Desconocido</span>
            <?php endif; ?>
        </td>

        <td>
            <?= esc($factura->cliente_nombre ?? 'Sin cliente') ?>
            <div class="text-right">
                <small class="text-muted">
                    Vendedor: <?= esc($factura->vendedor ?? 'Sin vendedor') ?>
                </small>
            </div>
        </td>

        <td class="text-center">
            <?= date('d/m/Y', strtotime($factura->fecha_emision)) ?>
            <br>
            <small class="text-muted">
                <?= date('H:i', strtotime($factura->hora_emision)) ?>
            </small>
        </td>

        <td class="text-center">
            <?php
            $condicion = $factura->condicion_operacion ?? 1;

            if ($condicion == 1) {
                echo '<span class="badge badge-estado1 bg-success badge-estado text-white">Contado</span>';
            } elseif ($condicion == 2) {
                echo '<span class="badge badge-estado1 bg-warning badge-estado text-grey">Crédito</span>';
            } else {
                echo '<span class="badge badge-estado1 bg-secondary badge-estado text-white">N/D</span>';
            }
            ?>
        </td>

        <td class="text-end">
            $ <?= number_format($factura->total_pagar, 2) ?>
        </td>

        <td class="text-end">
            $ <?= number_format($factura->saldo, 2) ?>
        </td>

        <td class="text-center">

            <?php if (($factura->anulada ?? 0) == 1): ?>

                <span class="badge badge-estado badge-estado bg-danger text-white">
                    Anulado
                </span>

            <?php elseif (($factura->saldo ?? 0) == 0): ?>

                <span class="badge badge-estado bg-info text-white">
                    <i class="fa-solid fa-check-circle"></i> Pagada
                </span>

            <?php else: ?>

                <span class="badge badge-estado bg-warning text-dark">
                    Activa
                </span>

            <?php endif; ?>

        </td>

        <td class="text-center">
            <a href="<?= base_url('facturas/' . $factura->id) ?>"
                class="btn btn-sm btn-info">
                <i class="fa-solid fa-eye"></i>
            </a>
        </td>
    </tr>
<?php endforeach; ?>