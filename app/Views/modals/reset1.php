<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" id="resetModalContent">

            <!-- Contenido inicial -->
            <div class="modal-header">
                <h5 class="modal-title">Recuperar contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="resetModalBody">
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="resetEmail" placeholder="correo@ejemplo.com">
                </div>
                <div id="resetAlert" class="alert d-none"></div>
            </div>

            <div class="modal-footer" id="resetModalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSendCode">Enviar código</button>
            </div>

        </div>
    </div>
</div>

<script>
const modalContent = document.getElementById('resetModalContent');
const modalBody = document.getElementById('resetModalBody');
const modalFooter = document.getElementById('resetModalFooter');
const btnSendCode = document.getElementById('btnSendCode');
const resetAlert = document.getElementById('resetAlert');
let resetEmailValue = '';

function showAlert(element, msg, type) {
    element.textContent = msg;
    element.className = `alert alert-${type}`;
    element.classList.remove('d-none');
}

// Paso 1: enviar código
btnSendCode.addEventListener('click', () => {
    const email = document.getElementById('resetEmail').value.trim();
    resetEmailValue = email;
    resetAlert.classList.add('d-none');

    if(!email){
        showAlert(resetAlert, 'Debes ingresar un correo', 'danger');
        return;
    }

    const formData = new FormData();
    formData.append('email', email);

    fetch("<?= base_url('auth/send-reset-code') ?>", {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            // Cambiar el contenido del modal
            modalBody.innerHTML = `
                <p>Hemos enviado un código a <strong>${email}</strong>.</p>
                <div class="mb-3">
                    <label class="form-label">Código recibido</label>
                    <input type="text" class="form-control" id="resetCode" placeholder="Ingresa el código">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nueva contraseña</label>
                    <input type="password" class="form-control" id="resetNewPass" placeholder="Nueva contraseña">
                </div>
                <div id="codeAlert" class="alert d-none"></div>
            `;
            modalFooter.innerHTML = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnResetPass">Cambiar contraseña</button>
            `;

            // Agregar listener al nuevo botón
            document.getElementById('btnResetPass').addEventListener('click', resetPassword);
        } else {
            showAlert(resetAlert, data.message, 'danger');
        }
    })
    .catch(() => {
        showAlert(resetAlert, 'Error de conexión', 'danger');
    });
});

// Función para paso 2: verificar código y cambiar contraseña
function resetPassword() {
    const email = resetEmailValue;
    const code  = document.getElementById('resetCode').value.trim();
    const pass  = document.getElementById('resetNewPass').value.trim();
    const codeAlert = document.getElementById('codeAlert');

    codeAlert.classList.add('d-none');

    if(!code || !pass){
        showAlert(codeAlert, 'Debes completar todos los campos', 'danger');
        return;
    }

    if(pass.length < 6){
        showAlert(codeAlert, 'La contraseña debe tener al menos 6 caracteres', 'danger');
        return;
    }

    const formData = new FormData();
    formData.append('email', email);
    formData.append('code', code);

    fetch("<?= base_url('auth/verify-reset-code') ?>", {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        console.log('RESPUESTA RAW:', text);
        const data = JSON.parse(text);
        if(!data.success){
            showAlert(codeAlert, data.message || 'Código inválido o expirado', 'danger');
            return;
        }

        const formPass = new FormData();
        formPass.append('email', email);
        formPass.append('user_password', pass);

        fetch("<?= base_url('auth/reset-password') ?>", {
            method: 'POST',
            body: formPass
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                showAlert(codeAlert, data.message, 'success');
                setTimeout(() => {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('resetModal'));
                    modal.hide();
                }, 2000);
            } else {
                showAlert(codeAlert, data.message, 'danger');
            }
        });
    })
    .catch(() => showAlert(codeAlert, 'Error de conexión', 'danger'));
}
</script>
