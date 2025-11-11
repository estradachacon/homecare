<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Editar ruta: <?= esc($routes->route_name) ?></h4>
            </div>

            <div class="card-body">
                <!-- 1. Formulario apuntando al método update -->
                <form action="<?= base_url('routes/update/' . $routes->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="route_name" class="form-label">Nombre de la Ruta</label>
                            <input type="text" class="form-control" id="route_name" name="route_name"
                                value="<?= esc($routes->route_name) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <input type="text" name="description" id="description"
                                class="form-control text-end input-unit-cost" value="<?= esc($routes->description) ?>">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>