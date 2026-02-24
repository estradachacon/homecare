<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Editar nombre: <?= esc($sellers->seller) ?></h4>
            </div>

            <div class="card-body">
                <!-- 1. Formulario apuntando al método update -->
                <form action="<?= base_url('sellers/update/' . $sellers->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="seller" class="form-label">Nombre de vendedor</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="seller" 
                                name="seller" 
                                value="<?= esc($sellers->seller) ?>" 
                                required
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tel_seller" class="form-label">Numero de telefono</label>
                            <input 
                                type="number" 
                                name="tel_seller" 
                                id="tel_seller"
                                class="form-control text-end input-unit-cost" 
                                value="<?= esc($sellers->tel_seller) ?>" 
                                step="0.01" 
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
