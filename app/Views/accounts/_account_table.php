<table class="table table-striped table-bordered table-hover" id="sellers-table">
    <thead>
        <tr>
            <th>ID</th>
            <th class="col-3">Nombre de cuenta</th>
            <th class="col-3">Descripción</th>
            <th class="col-1">Saldo</th>
            <th class="col-1">Tipo</th>
            <th class="col-1">Estado</th>
            <th class="col-1.5">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($accounts)): ?>
            <?php foreach ($accounts as $account): ?>
                <tr>
                    <td class="text-center fw-bold"><?= esc($account->id) ?></td>
                    <td><strong><?= esc($account->name) ?></strong></td>
                    <td><strong><?= esc($account->description) ?></strong></td>
                    <td class="text-center"><strong>$<?= number_format($account->balance, 2) ?></strong></td>
                    <td class="text-center"><strong><?= statusBadge($account->type) ?></strong></td>
                    <td class="text-center">
                        <?= statusBadge($account->is_active ? 'Activo' : 'Inactivo') ?>
                    </td>
                    <td class="text-center">
                        <?php if ($account->id != 1): ?>
                            <a href="<?= base_url('accounts/edit/' . $account->id) ?>" class="btn btn-sm btn-info">
                                <i class="fa-solid fa-edit"></i>
                            </a>

                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $account->id ?>">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        <?php else: ?>
                            <span class="badge badge-info">Este registro no se puede editar o borrar</span>
                        <?php endif; ?>
                    </td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No hay Cuentas registradas<?= !empty($q) ? ' para la búsqueda "' . esc($q) . '"' : '' ?></td>
                </tr>
            <?php endif; ?>
    </tbody>
</table>
<div class="mt-3" id="pagination-links">
    <?= $pager->links('default', 'bitacora_pagination') ?>
</div>