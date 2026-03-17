<?php if (empty($notifications)): ?>

    <div class="alert alert-info text-center">
        No hay notificaciones
    </div>

<?php else: ?>

    <div class="list-group">

        <?php foreach ($notifications as $n): ?>

            <a href="<?= esc($n->link ?? '#') ?>"
                class="list-group-item list-group-item-action d-flex align-items-start">

                <div class="mr-3">

                    <?php if ($n->tipo == 'warning'): ?>

                        <i class="fa-solid fa-triangle-exclamation text-warning"></i>

                    <?php elseif ($n->tipo == 'success'): ?>

                        <i class="fa-solid fa-circle-check text-success"></i>

                    <?php else: ?>

                        <i class="fa-solid fa-circle-info text-primary"></i>

                    <?php endif ?>

                </div>

                <div class="flex-fill">

                    <div class="font-weight-bold">
                        <?= esc($n->titulo) ?>
                    </div>

                    <small class="text-muted">
                        <?= esc($n->mensaje) ?>
                    </small>

                </div>

                <div class="text-muted small ml-3">
                    <?= date('d/m/Y H:i', strtotime($n->created_at)) ?>
                </div>

            </a>

        <?php endforeach ?>

    </div>

    <div id="pagination-links" class="mt-3">
        <?= $pager->links('default', 'bootstrap_full') ?>
    </div>

<?php endif ?>