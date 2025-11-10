<!-- Modal de Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loginModalLabel">Iniciar Sesión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" action="<?= base_url('/login') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Email/Usuario -->
                    <div class="mb-3">
                        <label for="username" class="form-label">Correo electrónico o usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required
                                autocomplete="username">
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required
                                autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Recordarme -->
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Recordar mis datos</label>
                    </div>

                    <!-- Mensaje de error -->
                    <div class="alert alert-danger d-none" id="loginError" role="alert">
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </button>
                        <a href="<?= base_url('auth/forgot-password') ?>" class="btn btn-link">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script para el manejo del formulario -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Toggle de contraseña ---
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // --- Manejo del formulario ---
    const loginForm = document.querySelector('#loginForm');

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // --- Actualizar token CSRF dinámicamente ---
            if (data.csrf) {
                // Buscar input existente
                let csrfInput = loginForm.querySelector('input[name="' + data.csrf.tokenName + '"]');

                // Si no existe, eliminar los viejos y crear uno nuevo
                if (!csrfInput) {
                    loginForm.querySelectorAll('input[name^="<?= csrf_token() ?>"]').forEach(el => el.remove());
                    csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = data.csrf.tokenName;
                    loginForm.appendChild(csrfInput);
                }

                // Actualizar el valor del token
                csrfInput.value = data.csrf.hash;
            }

            // --- Procesar la respuesta ---
            const errorDiv = document.querySelector('#loginError');
            if (data.success) {
                errorDiv.classList.add('d-none');
                window.location.href = data.redirect || '<?= base_url() ?>';
            } else {
                errorDiv.textContent = data.message || 'Error al iniciar sesión';
                errorDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorDiv = document.querySelector('#loginError');
            errorDiv.textContent = 'Error de conexión';
            errorDiv.classList.remove('d-none');
        });
    });
});
</script>


<!-- Estilos adicionales -->
<style>
    .modal-header {
        border-bottom: 2px solid #0056b3;
    }

    .modal-content {
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus {
        border-color: #0056b3;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }

    .btn-primary {
        background-color: #0056b3;
        border-color: #0056b3;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #004494;
        border-color: #004494;
        transform: translateY(-1px);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }

    .input-group .form-control {
        border-left: none;
    }

    .input-group .form-control:focus+.input-group-text {
        border-color: #0056b3;
    }

    #togglePassword {
        border-left: none;
    }

    #togglePassword:hover {
        background-color: #f8f9fa;
    }

    .form-check-input:checked {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    .btn-link {
        color: #0056b3;
        text-decoration: none;
    }

    .btn-link:hover {
        color: #004494;
        text-decoration: underline;
    }

    .alert {
        margin-bottom: 1rem;
        border-radius: 8px;
    }

    /* Personalización del backdrop del modal */
    .modal-backdrop {
        background-color: rgba(33, 37, 41, 0.6);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    .modal-backdrop.show {
        opacity: 1;
    }
</style>