<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="mb-3">Nueva cuenta</h4>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="accountForm" action="<?= base_url('accounts') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nombre de la Cuenta</label>
                        <input type="text" name="name" id="name" class="form-control" minlength="3" required>
                        <div class="invalid-feedback">
                            El nombre debe tener al menos 3 caracteres.
                        </div>
                    </div>
                    <div class="col-md-6 mb-3"> <label for="type" class="form-label">Tipo de la Cuenta</label>
                        <select name="type" id="type" class="form-select" required>

                            <option value="" disabled selected>Selecciona un tipo</option>

                            <option value="Efectivo">Efectivo</option>

                            <option value="Banco">Banco</option>

                        </select>
                    </div>
                    
                    <div class="mb-3 col-md-12">
                        <label for="description" class="form-label">Descripción (Comentarios)</label>

                        <textarea
                            name="description"
                            id="description"
                            class="form-control"
                            rows="3"
                            maxlength="200"></textarea>

                        <div class="form-text text-muted">
                            Opcional. Puedes agregar comentarios o detalles sobre la cuenta (máximo 200 caracteres).
                        </div>
                    </div>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="<?= base_url('accounts') ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    (() => {
        'use strict';
        const form = document.getElementById('sellerForm');

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault(); // ❌ evita enviar si hay errores
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    })();
</script>

<?= $this->endSection() ?>