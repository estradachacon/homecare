<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Editar rol: <?= esc($roles['nombre']) ?></h4>
            </div>

            <div class="card-body">
                <!-- 1. Formulario apuntando al método update -->
                <form action="<?= base_url('roles/update/' . $roles['id']) ?>" method="post">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nombre del Rol</label>
                            <input type="text" class="form-control" id="nom" name="nombre"
                                value="<?= esc($roles['nombre']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion"
                                class="form-control text-end input-unit-cost" value="<?= esc($roles['descripcion']) ?>">
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