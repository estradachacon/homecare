<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<form method="post" action="<?= base_url('settings/update') ?>" enctype="multipart/form-data">

<div class="card">
    <div class="card-header">
        <h5>Editar Datos de la Empresa</h5>
    </div>

    <div class="card-body">

        <div class="form-group mb-2">
            <label>Nombre de la empresa</label>
            <input type="text" name="company_name"
                   class="form-control"
                   value="<?= esc($settings->company_name) ?>" required>
        </div>

        <div class="form-group mb-2">
            <label>Dirección</label>
            <input type="text" name="company_address"
                   class="form-control"
                   value="<?= esc($settings->company_address) ?>" required>
        </div>

        <div class="form-group mb-2">
            <label>Color principal</label>
            <input type="color" name="primary_color"
                   value="<?= esc($settings->primary_color) ?>">
        </div>

        <div class="form-group mb-2">
            <label>Logo</label>
            <input type="file" name="logo" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label>Favicon</label>
            <input type="file" name="favicon" class="form-control">
        </div>

        <button class="btn btn-success">
            <i class="fa fa-save"></i> Guardar cambios
        </button>

        <a href="<?= base_url('settings') ?>" class="btn btn-secondary">
            Cancelar
        </a>

    </div>
</div>

</form>
<?= $this->endSection() ?>