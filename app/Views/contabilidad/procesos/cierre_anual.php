<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title mb-0"><i class="fa-solid fa-calendar-xmark me-2"></i>Proceso de Cierre Anual</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>Atención:</strong> El cierre anual es un proceso crítico que:
                    <ul class="mb-0 mt-2">
                        <li>Genera el asiento de cierre (cargando ingresos y acreditando costos/gastos)</li>
                        <li>Calcula la utilidad o pérdida del ejercicio</li>
                        <li>Registra el resultado en la cuenta de resultados configurada</li>
                        <li>Requiere que <strong>todos los meses</strong> del año estén cerrados</li>
                    </ul>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Año a cerrar <span class="text-danger">*</span></label>
                    <select id="anio" class="form-select">
                        <option value="">Seleccionar año</option>
                        <?php foreach ($anios as $a): ?>
                        <option value="<?= $a ?>"><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($anios)): ?>
                    <small class="text-muted">No hay años con todos los períodos cerrados disponibles.</small>
                    <?php endif; ?>
                </div>

                <?php if (!empty($anios)): ?>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('contabilidad') ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Cancelar
                    </a>
                    <button class="btn btn-danger ms-auto" onclick="ejecutarCierreAnual()">
                        <i class="fa-solid fa-calendar-xmark"></i> Ejecutar Cierre Anual
                    </button>
                </div>
                <?php endif; ?>

                <div id="resultadoCierreAnual" class="mt-3 d-none"></div>
            </div>
        </div>
    </div>
</div>

<script>
function ejecutarCierreAnual() {
    const anio = document.getElementById('anio').value;
    if (!anio) { Swal.fire('Requerido', 'Selecciona el año', 'warning'); return; }

    Swal.fire({
        title: `¿Ejecutar cierre anual ${anio}?`,
        html: 'Se generará el asiento de cierre del ejercicio.<br><strong>¿Deseas continuar?</strong>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, ejecutar',
        cancelButtonText: 'Cancelar'
    }).then(r => {
        if (!r.isConfirmed) return;

        const form = new FormData();
        form.append('anio', anio);

        fetch('<?= base_url('contabilidad/procesos/cierre-anual/ejecutar') ?>', { method: 'POST', body: form })
            .then(r => r.json())
            .then(d => {
                const el = document.getElementById('resultadoCierreAnual');
                el.classList.remove('d-none');
                if (d.success) {
                    const utilidad = d.utilidad || 0;
                    const resultadoTexto = utilidad >= 0
                        ? `<strong>Utilidad del ejercicio: $${parseFloat(utilidad).toFixed(2)}</strong>`
                        : `<strong class="text-danger">Pérdida del ejercicio: $${Math.abs(utilidad).toFixed(2)}</strong>`;
                    el.innerHTML = `<div class="alert alert-success"><i class="fa-solid fa-check-circle me-2"></i>${d.message}<br>${resultadoTexto}</div>`;
                    Swal.fire('Cierre Completado', d.message, 'success').then(() => {
                        window.location = '<?= base_url('contabilidad') ?>';
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
