<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card shadow-sm">

            <div class="card-header d-flex">
                <h4 class="mb-3">Nuevo Proveedor</h4>
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

                <form id="proveedorForm" action="<?= base_url('proveedores/create') ?>" method="post" novalidate>

                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Proveedor</label>
                        <input type="text"
                            name="nombre"
                            id="nombre"
                            class="form-control"
                            minlength="3"
                            required>

                        <div class="invalid-feedback">
                            El nombre debe tener al menos 3 caracteres.
                        </div>
                    </div>


                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>

                            <input type="text"
                                name="telefono"
                                id="telefono"
                                class="form-control"
                                pattern="^[0-9]{8,}$"
                                title="El teléfono debe tener al menos 8 dígitos">

                            <div class="invalid-feedback">
                                El teléfono debe tener al menos 8 dígitos.
                            </div>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>

                            <input type="email"
                                name="email"
                                id="email"
                                class="form-control">

                            <div class="invalid-feedback">
                                Debe ingresar un correo válido.
                            </div>
                        </div>

                    </div>


                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>

                        <textarea
                            name="direccion"
                            id="direccion"
                            class="form-control"
                            rows="3"></textarea>
                    </div>


                    <button type="submit" class="btn btn-success">
                        Guardar
                    </button>

                    <a href="<?= base_url('proveedores') ?>" class="btn btn-secondary">
                        Cancelar
                    </a>

                </form>

            </div>

        </div>

    </div>
</div>


<script>
    (() => {

        'use strict';

        const form = document.getElementById('proveedorForm');

        form.addEventListener('submit', function(event) {

            if (!form.checkValidity()) {

                event.preventDefault();
                event.stopPropagation();

            }

            form.classList.add('was-validated');

        }, false);

    })();
</script>

<?= $this->endSection() ?>