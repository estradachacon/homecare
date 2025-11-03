<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">Crear caja</h4>
            </div>
            <div class="card-body">
                <form action="<?= base_url('cashiers/create') ?>" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de caja</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="branch_id" class="form-label">Sucursal</label>
                        <select class="form-select" id="branch_id" name="branch_id" required>
                            <option value="">Seleccione una sucursal</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= esc($branch->id) ?>"><?= esc($branch->branch_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="initial_balance" class="form-label">Monto inicial</label>
                        <input type="number" class="form-control" id="initial_balance" name="initial_balance" required>
                    </div>
                    <div class="mb-3">
                        <label for="current_balance" class="form-label">Monto actual</label>
                        <input type="number" class="form-control" id="current_balance" name="current_balance" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Crear caja</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>