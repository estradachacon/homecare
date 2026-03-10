<?php if (!empty($productos)): ?>

    <?php foreach ($productos as $p): ?>

        <tr>
            <td>
                <div class="fw-semibold">
                    <?= esc($p->descripcion) ?>
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
                    data-activo="<?= $p->activo ?>">
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