<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .autosize-input {
        overflow: hidden;
        /* oculta scrollbar vertical */
        resize: none;
        /* evita que el usuario cambie tamaño manualmente */
        min-height: 38px;
        /* altura mínima */
        line-height: 1.5;
        /* buena legibilidad */
        transition: height 0.2s ease;
        /* animación suave al crecer */
        max-height: 146px;
        /* aprox 5 líneas */
    }
</style>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="mb-3">Nueva ruta</h4>
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

                <form id="routeForm" action="<?= base_url('routes') ?>" method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="route_name" class="form-label">Nombre de la Ruta</label>
                        <input type="text" name="route_name" id="route_name" class="form-control" minlength="3" required>
                        <div class="invalid-feedback">
                            El nombre debe tener al menos 3 caracteres.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea name="description" id="description" class="form-control autosize-input" rows="1"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="<?= base_url('routes') ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    (() => {
        'use strict';
        const form = document.getElementById('sellerForm');

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
        const form = document.getElementById('sellerForm');

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