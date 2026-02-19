<?php if (!empty($accounts)): ?>

<div class="list-group list-group-flush shadow-sm rounded border">

    <?php foreach ($accounts as $account): ?>
        <div class="list-group-item py-3">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1 fw-bold text-dark">
                        <?= esc($account->name) ?>
                    </h6>
                    <small class="text-muted">
                        ID #<?= esc($account->id) ?>
                    </small>
                </div>

                <div class="text-end">
                    <div class="fw-bold text-success">
                        $<?= number_format($account->balance, 2) ?>
                    </div>
                    <small class="text-muted">Saldo</small>
                </div>
            </div>

            <!-- INFO -->
            <div class="mt-2 small text-secondary">
                <?= esc($account->description) ?>
            </div>

            <div class="d-flex justify-content-between mt-2">
                <div>
                    <span class="badge badge-secondary">
                        <?= esc($account->type) ?>
                    </span>

                    <?= statusBadge($account->is_active ? 'Activo' : 'Inactivo') ?>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="d-flex justify-content-end mt-3 gap-2">
                <?php if ($account->id != 1): ?>
                    <a href="<?= base_url('accounts/edit/' . $account->id) ?>"
                       class="btn btn-sm btn-outline-info">
                        <i class="fa-solid fa-edit"></i> Editar
                    </a>

                    <button class="btn btn-sm btn-outline-danger delete-btn"
                            data-id="<?= $account->id ?>">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                <?php else: ?>
                    <span class="badge badge-info">
                        No editable
                    </span>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>

</div>

<div class="mt-3">
    <?= $pager->links('default', 'bitacora_pagination') ?>
</div>

<?php else: ?>
    <div class="alert alert-light text-center border">
        No hay cuentas registradas<?= !empty($q) ? ' para la búsqueda "' . esc($q) . '"' : '' ?>
    </div>
<?php endif; ?>
