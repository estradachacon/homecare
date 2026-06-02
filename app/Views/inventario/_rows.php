<?php if (!empty($productos)): ?>

    <?php foreach ($productos as $p): ?>

        <?php
        $stock       = (float)($p->stock ?? 0);
        $tipoTexto   = match((int)$p->tipo) { 2 => 'Servicio', 3 => 'Otro', default => 'Bien' };
        $tipoBadge   = match((int)$p->tipo) { 2 => 'badge-primary', 3 => 'badge-secondary', default => 'badge-info' };

        if ($stock <= 0)       { $stockBadge = 'badge-danger';  $stockText = 'text-white'; }
        elseif ($stock <= 5)   { $stockBadge = 'badge-warning'; $stockText = 'text-dark'; }
        else                   { $stockBadge = 'badge-success'; $stockText = 'text-white'; }

        $rowClass = $stock <= 0 ? 'table-danger' : '';
        $costo    = (float)($p->costo_promedio ?? 0);
        ?>

        <tr class="<?= $rowClass ?>">

            <td>
                <div class="font-weight-bold"><?= esc($p->descripcion) ?></div>
                <div class="mt-1 d-flex flex-wrap justify-content-end">

                    <span class="badge <?= $tipoBadge ?> mr-1" style="font-size:11px;font-weight:normal">
                        <?= $tipoTexto ?>
                    </span>

                    <?php if (!empty($p->marca)): ?>
                        <small class="text-muted mr-1"><?= esc($p->marca) ?></small>
                    <?php endif ?>

                    <?php if (!empty($p->clasificacion_nombre)): ?>
                        <span class="badge badge-light border text-secondary" style="font-size:11px">
                            <i class="fa fa-tag fa-xs mr-1"></i><?= esc($p->clasificacion_nombre) ?>
                        </span>
                    <?php endif ?>

                </div>
            </td>

            <td>
                <code class="text-dark font-weight-bold"><?= esc($p->codigo) ?></code>
            </td>

            <td class="text-center">
                <?= $p->activo
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>' ?>
            </td>

            <td class="text-center">
                <span class="badge <?= $stockBadge ?> <?= $stockText ?> stock-badge">
                    <?= number_format($stock, 0) ?>
                </span>
            </td>

            <?php if (tienePermiso('ver_costos_inventario')): ?>
            <td class="text-right">
                <?php if ($costo > 0): ?>
                    <span class="text-dark">$<?= number_format($costo, 2) ?></span>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif ?>
            </td>
            <?php endif ?>

            <td class="text-center">

                <div class="btn-group btn-group-sm">

                    <a href="<?= base_url('productos/' . $p->id) ?>"
                        class="btn btn-info" title="Ver kardex">
                        <i class="fa-solid fa-eye"></i>
                    </a>

                    <button class="btn btn-warning btnEditar" title="Editar producto"
                        data-id="<?= $p->id ?>"
                        data-descripcion="<?= esc($p->descripcion) ?>"
                        data-codigo="<?= esc($p->codigo) ?>"
                        data-tipo="<?= (int)$p->tipo ?>"
                        data-activo="<?= $p->activo ?>"
                        data-marca="<?= esc($p->marca ?? '') ?>"
                        data-clasificacion-id="<?= $p->clasificacion_id ?? '' ?>"
                        data-precio-minimo="<?= number_format((float)($p->precio_minimo ?? 0), 2, '.', '') ?>">
                        <i class="fa-solid fa-pen"></i>
                    </button>

                </div>

            </td>

        </tr>

    <?php endforeach ?>

<?php else: ?>

    <tr>
        <td colspan="6" class="text-center py-5 text-muted">
            <i class="fa fa-box-open fa-2x mb-2 d-block"></i>
            No se encontraron productos con los filtros aplicados
        </td>
    </tr>

<?php endif ?>
