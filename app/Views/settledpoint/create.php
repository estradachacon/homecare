<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h4 class="mb-3">Nuevo Vendedor</h4>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- ✅ agregamos id="sellerForm" -->
    <form id="sellerForm" action="<?= base_url('sellers') ?>" method="post" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="seller" class="form-label">Nombre del Vendedor</label>
            <input
                type="text"
                name="seller"
                id="seller"
                class="form-control"
                minlength="3"
                required>
            <div class="invalid-feedback">
                El nombre debe tener al menos 3 caracteres.
            </div>
        </div>

        <div class="mb-3">
            <label for="tel_seller" class="form-label">Teléfono</label>
            <input
                type="text"
                name="tel_seller"
                id="tel_seller"
                class="form-control"
                pattern="^[0-9]{8,}$"
                title="El teléfono debe tener al menos 8 dígitos"
                required> <!-- ✅ ahora es obligatorio -->
            <div class="invalid-feedback">
                El teléfono debe tener al menos 8 dígitos.
            </div>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="<?= base_url('sellers') ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
(() => {
    'use strict';
    const form = document.getElementById('sellerForm');

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();  // ❌ evita enviar si hay errores
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
})();
</script>

<?= $this->endSection() ?>
