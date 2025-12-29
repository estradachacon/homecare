<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Mi perfil</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="<?= $user['foto']
                                    ? base_url('upload/perfiles/' . $user['foto'])
                                    : base_url('upload/no-user.png') ?>"
                        class="rounded-circle"
                        width="120">
                </div>

                <form action="<?= base_url('perfil/update') ?>" method="post" enctype="multipart/form-data">

                    <div class="mb-2">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control"
                            value="<?= esc($user['user_name']) ?>" required>
                    </div>

                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" class="form-control"
                            value="<?= esc($user['email']) ?>" disabled>
                    </div>

                    <div class="mb-2">
                        <label>Nueva contraseña</label>
                        <input type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Ingrese nueva contraseña">
                    </div>

                    <div class="mb-2">
                        <label>Confirmar contraseña</label>
                        <input type="password"
                            id="password_confirm"
                            class="form-control"
                            placeholder="Repita la contraseña">
                    </div>

                    <div class="mb-2 text-danger d-none" id="passwordError">
                        Las contraseñas no coinciden
                    </div>

                    <div class="mb-3">
                        <label>Foto de perfil</label>
                        <input type="file" name="foto" class="form-control">
                    </div>

                    <button type="submit"
                        id="btnGuardar"
                        class="btn btn-primary"
                        disabled>
                        Guardar cambios
                    </button>
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

    function validarPasswords() {
        const pass = password.value.trim();
        const conf = confirm.value.trim();

        // 🟢 No se quiere cambiar contraseña
        if (pass === '' && conf === '') {
            btnGuardar.disabled = false;
            error.classList.add('d-none');
            return;
        }

        // 🔴 Uno está lleno y el otro no
        if (pass === '' || conf === '') {
            btnGuardar.disabled = true;
            error.textContent = 'Debe completar ambos campos';
            error.classList.remove('d-none');
            return;
        }

        // 🔴 No coinciden
        if (pass !== conf) {
            btnGuardar.disabled = true;
            error.textContent = 'Las contraseñas no coinciden';
            error.classList.remove('d-none');
            return;
        }

        // 🟢 Coinciden
        btnGuardar.disabled = false;
        error.classList.add('d-none');
    }

    password.addEventListener('input', validarPasswords);
    confirm.addEventListener('input', validarPasswords);

    // Ejecutar al cargar la página
    validarPasswords();
</script>

<?= $this->endSection() ?>