<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="mb-3">Nuevo tipo de venta</h4>
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

                <form id="tipoVentaForm" action="<?= base_url('tipo_venta') ?>" method="post" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nombre_tipo_venta" class="form-label">Nombre del tipo de venta</label>
                        <input type="text" name="nombre_tipo_venta" id="nombre_tipo_venta" class="form-control" minlength="3" required>
                        <div class="invalid-feedback">
                            El nombre debe tener al menos 3 caracteres.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Guardar</button>
                    <a href="<?= base_url('tipo_venta') ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    (() => {
        'use strict';
        const form = document.getElementById('tipoVentaForm');

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();  // ❌ evita enviar si hay errores
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    })();
</script>

<?= $this->endSection() ?>