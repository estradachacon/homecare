<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Crear sucursal</h4>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('branches') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="branch_name" class="form-label">Nombre de la Sucursal</label>
                        <input type="text" name="branch_name" id="branch_name" class="form-control"
                            value="<?= old('branch_name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="branch_direction" class="form-label">Dirección</label>
                        <input type="text" name="branch_direction" id="branch_direction" class="form-control"
                            value="<?= old('branch_direction') ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="<?= site_url('branches') ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>