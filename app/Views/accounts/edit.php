<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Editar nombre de cuenta: <?= esc($accounts->name) ?></h4>
            </div>

            <div class="card-body">
                <!-- 1. Formulario apuntando al método update -->
                <form action="<?= base_url('accounts/update/' . $accounts->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre de cuenta</label>
                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="<?= esc($accounts->name) ?>"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Tipo de la Cuenta</label>
                            <select name="type" id="type" class="form-select" required>

                                <option value="" disabled
                                    <?= empty($accounts->type) ? 'selected' : '' ?>>
                                    Selecciona un tipo
                                </option>

                                <option value="Efectivo"
                                    <?= $accounts->type == 'Efectivo' ? 'selected' : '' ?>>
                                    Efectivo
                                </option>

                                <option value="Banco"
                                    <?= $accounts->type == 'Banco' ? 'selected' : '' ?>>
                                    Banco
                                </option>

                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Descripción (Comentarios)</label>

                            <textarea
                                name="description"
                                id="description"
                                class="form-control"
                                rows="3"
                                maxlength="200"><?= esc($accounts->description) ?></textarea>

                            <div class="form-text text-muted">
                                Opcional. Puedes agregar comentarios o detalles sobre la cuenta (máximo 200 caracteres).
                            </div>
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