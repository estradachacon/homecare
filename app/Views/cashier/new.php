<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Crear caja</h4>
            </div>

            <div class="card-body">
                <form action="<?= base_url('cashiers/create') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre de caja</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="initial_balance" class="form-label">Monto inicial</label>
                            <input type="number" name="initial_balance" id="initial_balance"
                                class="form-control text-right input-unit-cost" value="" step="0.01" required>
                        </div>
                    </div>
                    <!-- Sucursal y Usuario en la misma fila -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="branch_id" class="form-label">Sucursal</label>
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option value="">Seleccione una sucursal</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= esc($branch->id) ?>"><?= esc($branch->branch_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Asignar usuario</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Seleccione un usuario</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= esc($user['id']) ?>"><?= esc($user['user_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Crear caja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>