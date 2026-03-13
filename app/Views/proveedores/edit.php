<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card shadow-sm">

            <div class="card-header d-flex">
                <h4 class="header-title mb-0">
                    Editar proveedor: <?= esc($proveedor->nombre) ?>
                </h4>
            </div>

            <div class="card-body">

                <form action="<?= base_url('proveedores/update/' . $proveedor->id) ?>" method="post">

                    <?= csrf_field() ?>

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label for="nombre" class="form-label">Nombre del proveedor</label>

                            <input
                                type="text"
                                class="form-control"
                                id="nombre"
                                name="nombre"
                                value="<?= esc($proveedor->nombre) ?>"
                                required>

                        </div>


                        <div class="col-md-6 mb-3">

                            <label for="telefono" class="form-label">Número de teléfono</label>

                            <input
                                type="text"
                                name="telefono"
                                id="telefono"
                                class="form-control"
                                value="<?= esc($proveedor->telefono) ?>">

                        </div>


                        <div class="col-md-6 mb-3">

                            <label for="email" class="form-label">Email</label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= esc($proveedor->email) ?>">

                        </div>


                        <div class="col-md-6 mb-3">

                            <label for="direccion" class="form-label">Dirección</label>

                            <input
                                type="text"
                                name="direccion"
                                id="direccion"
                                class="form-control"
                                value="<?= esc($proveedor->direccion) ?>">

                        </div>

                    </div>


                    <div class="text-end">

                        <button type="submit" class="btn btn-success">
                            Guardar cambios
                        </button>

                        <a href="<?= base_url('proveedores') ?>" class="btn btn-secondary">
                            Cancelar
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>

<?= $this->endSection() ?>