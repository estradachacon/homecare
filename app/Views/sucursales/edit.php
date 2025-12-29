<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Editar sucursal</h4>
            </div>
            <div class="card-body">

                <form action="<?= site_url('branches/update/' . $branch->id) ?>" method="post">

                    <div class="mb-3">
                        <label class="form-label">Nombre de la Sucursal</label>
                        <input type="text"
                            name="branch_name"
                            class="form-control"
                            value="<?= old('branch_name', $branch->branch_name) ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text"
                            name="branch_direction"
                            class="form-control"
                            value="<?= old('branch_direction', $branch->branch_direction) ?>"
                            required>
                    </div>

                    <button class="btn btn-primary">Guardar</button>
                    <a href="<?= site_url('branches') ?>" class="btn btn-secondary">Cancelar</a>
                </form>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>