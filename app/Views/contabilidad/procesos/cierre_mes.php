<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title mb-0"><i class="fa-solid fa-lock me-2"></i>Proceso de Cierre de Mes</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>Importante:</strong> El cierre de mes realizará las siguientes acciones:
                    <ul class="mb-0 mt-2">
                        <li>Guardará los saldos del período en el historial</li>
                        <li>Cerrará el período (no se podrán agregar más asientos)</li>
                        <li>Trasladará saldos de cuentas de balance al siguiente período</li>
                        <li>Creará automáticamente el siguiente período</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Período a cerrar <span class="text-danger">*</span></label>
                    <select id="periodoId" class="form-select">
                        <option value="">Seleccionar período abierto</option>
                        <?php
                        $mn=[1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                             7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                        foreach ($periodos as $p): ?>
                        <option value="<?= $p->id ?>"><?= $mn[$p->mes] ?> <?= $p->anio ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (empty($periodos)): ?>
                <div class="alert alert-info">No hay períodos abiertos disponibles para cierre</div>
                <?php else: ?>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('contabilidad') ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Cancelar
                    </a>
                    <button class="btn btn-danger ms-auto" onclick="ejecutarCierre()">
                        <i class="fa-solid fa-lock"></i> Ejecutar Cierre de Mes
                    </button>
                </div>
                <?php endif; ?>

                <div id="resultadoCierre" class="mt-3 d-none"></div>
            </div>
        </div>
    </div>
</div>

<script>
function ejecutarCierre() {
    const periodoId = document.getElementById('periodoId').value;
    if (!periodoId) { Swal.fire('Requerido', 'Selecciona un período', 'warning'); return; }

    Swal.fire({
        title: '¿Confirmar cierre de mes?',
        html: 'Esta acción cerrará el período seleccionado.<br><strong>No se puede deshacer fácilmente.</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Cancelar'
    }).then(r => {
        if (!r.isConfirmed) return;

        const form = new FormData();
        form.append('periodo_id', periodoId);

        fetch('<?= base_url('contabilidad/procesos/cierre-mes/ejecutar') ?>', { method: 'POST', body: form })
            .then(r => r.json())
            .then(d => {
                const el = document.getElementById('resultadoCierre');
                el.classList.remove('d-none');
                if (d.success) {
                    el.innerHTML = `<div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i>${d.message}</div>`;
                    Swal.fire('Éxito', d.message, 'success').then(() => {
                        window.location = '<?= base_url('contabilidad/periodos') ?>';
                    });
                } else {
                    el.innerHTML = `<div class="alert alert-danger"><i class="fa-solid fa-times-circle me-2"></i>${d.message}</div>`;
                    Swal.fire('Error', d.message, 'error');
                }
            });
    });
}
</script>

<?= $this->endSection() ?>
