<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$fotoPerfil = !empty($user['foto']) && file_exists(FCPATH . 'upload/perfiles/' . $user['foto'])
    ? base_url('upload/perfiles/' . $user['foto'])
    : base_url('upload/profile/user.jpg');
?>

<style>
    .profile-page .card {
        border: 0;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
    }

    .profile-photo-panel {
        border-right: 1px solid #edf0f4;
        min-height: 100%;
    }

    .profile-avatar-wrap {
        width: 168px;
        height: 168px;
        margin: 0 auto;
        padding: 6px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #e9eef5;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .12);
    }

    .profile-avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }

    .profile-section-title {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #7b8794;
        border-bottom: 1px solid #eef1f5;
        padding-bottom: .45rem;
        margin-bottom: 1rem;
    }

    .profile-page label {
        font-size: .78rem;
        font-weight: 700;
        color: #5f6b7a;
        margin-bottom: .25rem;
    }

    .profile-page .form-control,
    .profile-page .custom-file-label {
        font-size: .86rem;
    }

    .profile-page .form-control:disabled {
        background: #f7f9fc;
        color: #6c757d;
    }

    .profile-file-help {
        font-size: .75rem;
        color: #8792a2;
    }

    .profile-summary {
        font-size: .82rem;
        color: #6c757d;
    }

    @media (max-width: 767.98px) {
        .profile-photo-panel {
            border-right: 0;
            border-bottom: 1px solid #edf0f4;
        }
    }
</style>

<div class="row profile-page">
    <div class="col-lg-12 col-xl-12 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between py-3">
                <h4 class="header-title mb-0">
                    <i class="fa-regular fa-user mr-2 text-primary"></i>Mi perfil
                </h4>
                <span class="badge badge-light border px-3 py-2">
                    <i class="fa-solid fa-shield-halved mr-1 text-muted"></i>Cuenta personal
                </span>
            </div>

            <div class="card-body p-0">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                        <i class="fa-solid fa-circle-check mr-1"></i>
                        <?= esc(session()->getFlashdata('success')) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('perfil/update') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row no-gutters">
                        <div class="col-md-4">
                            <div class="profile-photo-panel text-center p-4">
                                <div class="profile-avatar-wrap mb-3">
                                    <img src="<?= esc($fotoPerfil) ?>" alt="Foto de perfil" id="profilePreview" class="profile-avatar">
                                </div>

                                <h5 class="font-weight-bold mb-1"><?= esc($user['user_name'] ?? 'Usuario') ?></h5>
                                <div class="profile-summary mb-3"><?= esc($user['email'] ?? '') ?></div>

                                <div class="custom-file text-left mb-2">
                                    <input type="file" name="foto" id="foto" class="custom-file-input" accept="image/*">
                                    <label class="custom-file-label" for="foto" data-default="Cambiar foto">Cambiar foto</label>
                                </div>
                                <div class="profile-file-help text-left">
                                    Usa una imagen cuadrada si quieres que el recorte circular se vea mejor.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="p-4">
                                <div class="profile-section-title">Información básica</div>

                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label for="nombre">Nombre</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control"
                                            value="<?= esc($user['user_name'] ?? '') ?>" required>
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" class="form-control"
                                            value="<?= esc($user['email'] ?? '') ?>" disabled>
                                    </div>
                                </div>

                                <div class="profile-section-title mt-3">Seguridad</div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="password">Nueva contraseña</label>
                                        <div class="input-group">
                                            <input type="password" id="password" name="password" class="form-control"
                                                placeholder="Dejar en blanco para conservar">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password" title="Mostrar u ocultar">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="password_confirm">Confirmar contraseña</label>
                                        <div class="input-group">
                                            <input type="password" id="password_confirm" class="form-control"
                                                placeholder="Repetir nueva contraseña">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password_confirm" title="Mostrar u ocultar">
                                                    <i class="fa-regular fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-danger py-2 d-none" id="passwordError"></div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" id="btnGuardar" class="btn btn-primary">
                                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const password = document.getElementById('password');
    const confirm = document.getElementById('password_confirm');
    const btnGuardar = document.getElementById('btnGuardar');
    const error = document.getElementById('passwordError');
    const foto = document.getElementById('foto');
    const preview = document.getElementById('profilePreview');

    function validarPasswords() {
        const pass = password.value.trim();
        const conf = confirm.value.trim();

        if (pass === '' && conf === '') {
            btnGuardar.disabled = false;
            error.classList.add('d-none');
            error.textContent = '';
            return;
        }

        if (pass === '' || conf === '') {
            btnGuardar.disabled = true;
            error.textContent = 'Debe completar ambos campos de contraseña.';
            error.classList.remove('d-none');
            return;
        }

        if (pass !== conf) {
            btnGuardar.disabled = true;
            error.textContent = 'Las contraseñas no coinciden.';
            error.classList.remove('d-none');
            return;
        }

        btnGuardar.disabled = false;
        error.classList.add('d-none');
        error.textContent = '';
    }

    function actualizarFoto() {
        const file = foto.files && foto.files[0] ? foto.files[0] : null;
        const label = document.querySelector('label[for="foto"]');

        if (!file) {
            label.textContent = label.dataset.default || 'Cambiar foto';
            return;
        }

        label.textContent = file.name;

        if (!file.type || !file.type.startsWith('image/')) {
            Swal.fire('Archivo no válido', 'Selecciona una imagen para la foto de perfil.', 'warning');
            foto.value = '';
            label.textContent = label.dataset.default || 'Cambiar foto';
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    document.querySelectorAll('[data-toggle-password]').forEach(button => {
        button.addEventListener('click', function () {
            const input = document.querySelector(this.dataset.togglePassword);
            const icon = this.querySelector('i');
            const visible = input.type === 'text';

            input.type = visible ? 'password' : 'text';
            icon.classList.toggle('fa-eye', visible);
            icon.classList.toggle('fa-eye-slash', !visible);
        });
    });

    password.addEventListener('input', validarPasswords);
    confirm.addEventListener('input', validarPasswords);
    foto.addEventListener('change', actualizarFoto);

    validarPasswords();
</script>

<?= $this->endSection() ?>
