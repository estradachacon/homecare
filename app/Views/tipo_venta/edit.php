<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Editar nombre: <?= esc($tipo_venta->nombre_tipo_venta) ?></h4>
            </div>

            <div class="card-body">
                <!-- 1. Formulario apuntando al método update -->
                <form action="<?= base_url('tipo_venta/update/' . $tipo_venta->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_tipo_venta" class="form-label">Nombre del tipo de venta</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nombre_tipo_venta" 
                                name="nombre_tipo_venta" 
                                value="<?= esc($tipo_venta->nombre_tipo_venta) ?>" 
                                required
                            >
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
