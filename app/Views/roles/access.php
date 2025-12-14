<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <h4>Asignar permisos al rol: <strong><?= esc($role['nombre']) ?></strong></h4>
                <a href="<?= site_url('roles') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Roles
                </a>
            </div>

            <div class="card-body">
                <form method="post" action="<?= site_url('access/' . $role['id']) ?>">
                    
                    <input type="hidden" name="_method" value="PUT">

                    <?php foreach ($permisos as $modulo => $acciones): ?>
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white fw-bold">
                                <i class="fas fa-cubes"></i> Módulo: <?= esc($modulo) ?>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($acciones as $accion): ?>
                                        <div class="col-12 col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                    type="checkbox"
                                                    name="permisos[]"
                                                    value="<?= $accion ?>"
                                                    id="<?= $accion ?>"
                                                    <?= in_array($accion, $permisosAsignados) ? 'checked' : '' ?>>

                                                <label class="form-check-label" for="<?= $accion ?>">
                                                    **<?= ucfirst(str_replace('_', ' ', $accion)) ?>**
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-success btn-lg mt-3" type="submit">
                        <i class="fas fa-save"></i> **Guardar permisos**
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>