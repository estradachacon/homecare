<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .autosize-input {
        overflow: hidden;
        resize: none;
        min-height: 38px;
        line-height: 1.5;
        transition: height 0.2s ease;
        max-height: 146px;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="mb-3">Nuevo Rol</h4>
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

                <form id="roleForm" action="<?= base_url('roles') ?>" method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Rol</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" minlength="3" required>
                        <div class="invalid-feedback">
                            El nombre debe tener al menos 3 caracteres.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" id="description" class="form-control autosize-input" rows="1"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="<?= base_url('roles') ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    (() => {
        'use strict';
        const form = document.getElementById('roleForm');

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();  // ❌ evita enviar si hay errores
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    })();
</script>
<script>
    (() => {
        'use strict';
        const form = document.getElementById('roleForm');

        // ✅ Validación del formulario
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // ✅ Autoajuste del textarea
        const textarea = document.querySelector('.autosize-input');
        if (textarea) {
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto'; // resetea
                textarea.style.height = textarea.scrollHeight + 'px'; // ajusta
            });
        }
    })();
</script>

<?= $this->endSection() ?>