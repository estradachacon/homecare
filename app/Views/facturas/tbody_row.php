<?php if (!empty($facturas)): ?>
<?php foreach ($facturas as $factura): ?>
    <?php
    $facturaAnulada = (($factura->anulada ?? 0) == 1);
    $facturaPagada  = (($factura->saldo ?? 0) == 0);
    $rowClass       = $facturaAnulada ? 'factura-row-anulada' : ($facturaPagada ? 'factura-row-pagada' : '');
    ?>
    <tr class="factura-row-card <?= $rowClass ?>" data-href="<?= base_url('facturas/' . $factura->id) ?>">
        <td data-label="Correlativo" class="text-center factura-card-head">
            <a href="<?= base_url('facturas/' . $factura->id) ?>" class="font-weight-bold factura-main-link">
                <?= esc(substr($factura->numero_control, -6)) ?>
            </a>
            <span class="factura-mobile-date d-none">
                <?= date('d/m/Y', strtotime($factura->fecha_emision)) ?>
            </span>
        </td>

        <td data-label="Tipo doc" class="factura-type-cell">
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
                <small class="text-muted factura-doc-desc">
                    <?= esc($descripcion) ?>
                </small>
            <?php else: ?>
                <span class="text-muted">Desconocido</span>
            <?php endif; ?>
        </td>

        <td data-label="Cliente" class="factura-client-cell">
            <span class="factura-client-name"><?= esc($factura->cliente_nombre ?? 'Sin cliente') ?></span>
            <div class="factura-seller">
                <small class="text-muted">
                    Vendedor: <?= esc($factura->vendedor ?? 'Sin vendedor') ?>
                </small>
            </div>
        </td>

        <td data-label="Fecha" class="text-center factura-date-cell">
            <?= date('d/m/Y', strtotime($factura->fecha_emision)) ?>
            <br>
            <small class="text-muted">
                <?= date('H:i', strtotime($factura->hora_emision)) ?>
            </small>
        </td>

        <td data-label="Condicion" class="text-center factura-condition-cell">
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
            <?php if ($sigla && $descripcion): ?>
                <span class="badge badge-estado1 bg-info text-white d-none factura-mobile-type-badge">
                    <?= esc($sigla) ?>
                </span>
            <?php endif; ?>
        </td>

        <td data-label="Total" class="text-end factura-total-cell">
            <span class="factura-total-state">
                <span>$ <?= number_format($factura->total_pagar, 2) ?></span>
                <?php if ($facturaAnulada): ?>
                    <span class="badge badge-estado bg-danger text-white d-none factura-mobile-state">Anulado</span>
                <?php elseif ($facturaPagada): ?>
                    <span class="badge badge-estado bg-info text-white d-none factura-mobile-state">
                        <i class="fa-solid fa-check-circle"></i> Pagada
                    </span>
                <?php else: ?>
                    <span class="badge badge-estado bg-warning text-dark d-none factura-mobile-state">Activa</span>
                <?php endif; ?>
            </span>
        </td>

        <td data-label="Saldo" class="text-end">
            $ <?= number_format($factura->saldo, 2) ?>
        </td>

        <td data-label="Estado" class="text-center factura-state-cell">

            <?php if ($facturaAnulada): ?>

                <span class="badge badge-estado badge-estado bg-danger text-white">
                    Anulado
                </span>

            <?php elseif ($facturaPagada): ?>

                <span class="badge badge-estado bg-info text-white">
                    <i class="fa-solid fa-check-circle"></i> Pagada
                </span>

            <?php else: ?>

                <span class="badge badge-estado bg-warning text-dark">
                    Activa
                </span>

            <?php endif; ?>

        </td>

        <td data-label="Menu" class="text-center factura-action-cell">
            <a href="<?= base_url('facturas/' . $factura->id) ?>"
                class="btn btn-sm btn-info">
                <i class="fa-solid fa-eye"></i>
            </a>
        </td>
    </tr>
<?php endforeach; ?>
<?php else: ?>
    <tr class="factura-empty-row">
        <td colspan="9" class="text-center">
            No hay facturas registradas
        </td>
    </tr>
<?php endif; ?>
