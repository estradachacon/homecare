<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Editar nombre del grupo</h4>
            </div>

            <div class="card-body">
                <form action="<?= base_url('content/save') ?>" method="post">
                    <!-- ID oculto -->
                    <input type="hidden" name="id" value="<?= isset($group->id) ? esc($group->id) : '' ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Nombre del grupo</label>
                            <small>Este es el título a mostrar en el view</small>
                            <input type="text" class="form-control" id="title" name="title" required
                                value="<?= isset($group->title) ? esc($group->title) : '' ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="description" name="description"
                                value="<?= isset($group->description) ? esc($group->description) : '' ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipo</label>
                            <input type="text" class="form-control" id="type" name="type" readonly
                                value="<?= isset($group->type) ? esc($group->type) : 'gallery' ?>">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <?= isset($group->id) ? 'Actualizar grupo' : 'Crear grupo' ?>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>