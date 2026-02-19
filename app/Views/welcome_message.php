<?= $this->extend('Layouts/mainbody_welcome') ?>
<?= $this->section('content') ?>

<div class="login-card">
    <div class="card">
        <div class="card-header text-center py-3">
            <h5 class="mb-0">
                <i class="fas fa-user-lock me-2"></i>Iniciar Sesión
            </h5>
        </div>

        <div class="card-body p-4" id="authContainer">

            <form id="loginForm" action="<?= base_url('login') ?>" method="POST">

                <!-- Usuario -->
                <div class="mb-3">
                    <label class="form-label">Correo electrónico o usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" required autocomplete="username">
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Recordar -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Recordar mis datos</label>
                </div>

                <!-- Error -->
                <div class="alert alert-danger d-none" id="loginError"></div>

                <!-- Botón -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </button>
                </div>

                <div class="text-center mt-3">
                    <a href="#" id="forgotPasswordLink" class="text-dark text-decoration-none">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

            </form>

        </div>
    </div>

    <p class="text-center text-white mt-3 small">
        © <?= date('Y') ?> - Sistema de Gestión
    </p>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const authContainer = document.getElementById('authContainer');
        const forgotLink = document.getElementById('forgotPasswordLink');
        // Toggle contraseña
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Login AJAX
        const form = document.getElementById('loginForm');
        const errorDiv = document.getElementById('loginError');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = form.querySelector('button[type="submit"]');
            lockButton(btn, 'Ingresando...');

            fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || '<?= base_url() ?>';
                    } else {
                        unlockButton(btn);
                        errorDiv.textContent = data.message || 'Credenciales incorrectas';
                        errorDiv.classList.remove('d-none');
                    }
                })
                .catch(() => {
                    unlockButton(btn);
                    errorDiv.textContent = 'Error de conexión';
                    errorDiv.classList.remove('d-none');
                });
        });
        /* ===============================
       TEMPLATE: RECUPERAR - PASO 1
    =============================== */
        function renderResetEmail() {
            authContainer.innerHTML = `
            <h5 class="text-center mb-3">Recuperar contraseña</h5>

            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="resetEmail">
            </div>

            <div id="resetAlert" class="alert d-none"></div>

            <div class="d-grid gap-2">
                <button class="btn btn-primary" id="btnSendCode">
                    Enviar código
                </button>
                <button class="btn btn-link" id="backToLogin">
                    Volver al login
                </button>
            </div>
        `;
            animateSwap(authContainer);

            document.getElementById('backToLogin')
                .addEventListener('click', renderLogin);

            document.getElementById('btnSendCode')
                .addEventListener('click', sendResetCode);
        }

        /* ===============================
           TEMPLATE: RECUPERAR - PASO 2
        =============================== */
        function renderResetPassword(email) {
            authContainer.innerHTML = `
            <h5 class="text-center mb-3">Nueva contraseña</h5>

            <p class="small text-muted">
                Código enviado a <strong>${email}</strong>
            </p>

            <div class="mb-3">
                <label>Código</label>
                <input type="text" class="form-control" id="resetCode">
            </div>

            <div class="mb-3">
                <label>Nueva contraseña</label>
                <input type="password" class="form-control" id="resetNewPass">
            </div>

            <div id="codeAlert" class="alert d-none"></div>

            <div class="d-grid">
                <button class="btn btn-primary" id="btnResetPass">
                    Cambiar contraseña
                </button>
            </div>
        `;
            animateSwap(authContainer);

            document.getElementById('btnResetPass')
                .addEventListener('click', () => resetPassword(email));
        }

        /* ===============================
           ACCIONES
        =============================== */
        function sendResetCode() {
            const email = document.getElementById('resetEmail').value.trim();
            const alertBox = document.getElementById('resetAlert');
            const btn = document.getElementById('btnSendCode');

            alertBox.classList.add('d-none');

            if (!email) {
                showAlert(alertBox, 'Ingresa un correo', 'danger');
                return;
            }

            lockButton(btn, 'Enviando...');

            const formData = new FormData();
            formData.append('email', email);

            fetch("<?= base_url('auth/send-reset-code') ?>", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        unlockButton(btn);
                        showAlert(alertBox, data.message, 'danger');
                        return;
                    }
                    renderResetPassword(email);
                })
                .catch(() => {
                    unlockButton(btn);
                    showAlert(alertBox, 'Error de conexión', 'danger');
                });
        }


        function resetPassword(email) {
            const btn = document.getElementById('btnResetPass');
            lockButton(btn, 'Guardando...');

            const code = document.getElementById('resetCode').value.trim();
            const pass = document.getElementById('resetNewPass').value.trim();
            const alertBox = document.getElementById('codeAlert');

            alertBox.classList.add('d-none');

            if (!code || !pass) {
                showAlert(alertBox, 'Completa todos los campos', 'danger');
                return;
            }

            const verifyData = new FormData();
            verifyData.append('email', email);
            verifyData.append('code', code);

            fetch("<?= base_url('auth/verify-reset-code') ?>", {
                    method: 'POST',
                    body: verifyData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        showAlert(alertBox, data.message, 'danger');
                        return;
                    }

                    const passData = new FormData();
                    passData.append('email', email);
                    passData.append('user_password', pass);

                    return fetch("<?= base_url('auth/reset-password') ?>", {
                        method: 'POST',
                        body: passData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlert(alertBox, data.message, 'success');
                        setTimeout(renderLogin, 2000);
                    } else {
                        showAlert(alertBox, data.message, 'danger');
                    }
                })
                .catch(() => showAlert(alertBox, 'Error de conexión', 'danger'));
        }

        /* ===============================
           UTILIDAD
        =============================== */
        function showAlert(el, msg, type) {
            el.textContent = msg;
            el.className = 'alert alert-' + type;
            el.classList.remove('d-none');
        }

        /* ===============================
           EVENTO LINK
        =============================== */
        if (forgotLink) {
            forgotLink.addEventListener('click', function(e) {
                e.preventDefault();
                renderResetEmail();
            });
        }

        /* ===============================
           VOLVER AL LOGIN (recarga suave)
        =============================== */
        function renderLogin() {
            location.reload();
        }

        function animateSwap(container) {
            container.classList.remove('fade-slide');
            void container.offsetWidth; // reflow
            container.classList.add('fade-slide');
        }

        function lockButton(btn, text = 'Procesando...') {
            btn.dataset.originalText = btn.innerHTML;
            btn.classList.add('btn-loading');
            btn.setAttribute('disabled', 'disabled');
            btn.innerHTML = `<span class="btn-spinner me-2"></span>${text}`;
        }

        function unlockButton(btn) {
            btn.classList.remove('btn-loading');
            btn.removeAttribute('disabled');
            btn.innerHTML = btn.dataset.originalText;
        }


    });
</script>
<?= $this->endSection() ?>