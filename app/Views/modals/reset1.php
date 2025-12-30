<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Recuperar contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="resetEmail" placeholder="correo@ejemplo.com">
                </div>

                <div id="resetAlert" class="alert d-none"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="sendResetCode()">
                    Enviar código
                </button>
            </div>

        </div>
    </div>
</div>
<script>
function sendResetCode() {
    const email = document.getElementById('resetEmail').value.trim();
    const alertBox = document.getElementById('resetAlert');

    alertBox.classList.add('d-none');

    if (!email) {
        showAlert('Debes ingresar un correo', 'danger');
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
        if (data.success) {
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(() => {
        showAlert('Error de conexión', 'danger');
    });

    function showAlert(msg, type) {
        alertBox.textContent = msg;
        alertBox.className = `alert alert-${type}`;
        alertBox.classList.remove('d-none');
    }
}
</script>
