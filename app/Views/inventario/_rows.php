<?php if (!empty($productos)): ?>

    <?php foreach ($productos as $p): ?>

        <tr>
            <td>
                <div class="d-flex flex-column">

                    <strong><?= esc($p->descripcion) ?></strong>

                    <div class="mt-1">
                        <?php
                        $tipoTexto = match((int)$p->tipo) { 2 => 'Servicio', 3 => 'Otro', default => 'Bien' };
                        ?>
                        <small class="text-muted"><?= $tipoTexto ?></small>

                        <?php if (!empty($p->marca)): ?>
                            <small class="text-muted"> · <?= esc($p->marca) ?></small>
                        <?php endif ?>

                        <?php if (!empty($p->clasificacion_nombre)): ?>
                            <span class="badge badge-light border ml-1" style="font-size:11px">
                                <?= esc($p->clasificacion_nombre) ?>
                            </span>
                        <?php endif ?>
                    </div>

                </div>
            </td>

            <td>
                <div class="fw-semibold">
                    <?= esc($p->codigo) ?>
                </div>
            </td>

            <td class="text-center">

                <?php if ($p->activo): ?>

                    <span class="badge bg-success text-white">
                        Activo
                    </span>

                <?php else: ?>

                    <span class="badge bg-danger text-white">
                        Inactivo
                    </span>

                <?php endif ?>

            </td>

            <td class="text-center">

                <?php if ($p->stock <= 0): ?>

                    <span class="badge bg-danger text-white stock-badge">
                        <?= $p->stock ?>
                    </span>

                <?php else: ?>

                    <span class="badge bg-info text-white stock-badge">
                        <?= $p->stock ?>
                    </span>

                <?php endif ?>

            </td>

            <td class="text-center">

                <a href="<?= base_url('productos/' . $p->id) ?>"
                    class="btn btn-sm btn-info">

                    <i class="fa-solid fa-eye"></i>

                </a>

                <button
                    class="btn btn-sm btn-warning btnEditar"
                    data-id="<?= $p->id ?>"
                    data-descripcion="<?= esc($p->descripcion) ?>"
                    data-codigo="<?= esc($p->codigo) ?>"
                    data-tipo="<?= (int)$p->tipo ?>"
                    data-activo="<?= $p->activo ?>"
                    data-marca="<?= esc($p->marca ?? '') ?>"
                    data-clasificacion-id="<?= $p->clasificacion_id ?? '' ?>">
                    <i class="fa-solid fa-pen"></i>
                </button>

            </td>
        </tr>

    <?php endforeach ?>

<?php else: ?>

    <tr>
        <td colspan="6" class="text-center">
            No hay productos registrados
        </td>
    </tr>

<?php endif ?>