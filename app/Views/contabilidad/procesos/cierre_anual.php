<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
/** @var array  $anios */
/** @var array  $aniosCerrados */
?>

<div class="row">
    <div class="col-md-8 col-lg-6">

        <!-- ── FORMULARIO DE CIERRE ────────────────────────────── -->
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-calendar-xmark me-2"></i>Proceso de Cierre Anual
                </h4>
            </div>
            <div class="card-body">

                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>Atención:</strong> El cierre anual es un proceso irreversible que:
                    <ul class="mb-0 mt-2">
                        <li>Requiere que <strong>todos los meses</strong> del año estén cerrados</li>
                        <li>Calcula la utilidad o pérdida del ejercicio</li>
                        <li>Genera el asiento de cierre automático</li>
                        <li>Bloquea la reapertura de períodos de ese año</li>
                    </ul>
                </div>

                <?php if (!empty($anios)): ?>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Año a cerrar <span class="text-danger">*</span>
                        </label>
                        <select id="anio" class="form-select">
                            <option value="">— Seleccionar año —</option>
                            <?php foreach ($anios as $a): ?>
                                <option value="<?= $a ?>"><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            Solo aparecen años con todos los períodos cerrados y sin cierre anual previo.
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('contabilidad/periodos') ?>" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i>Cancelar
                        </a>
                        <button class="btn btn-danger ms-auto" onclick="ejecutarCierreAnual()">
                            <i class="fa-solid fa-calendar-xmark me-1"></i>Ejecutar Cierre Anual
                        </button>
                    </div>

                <?php else: ?>

                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-circle-info fa-2x mb-2 d-block"></i>
                        No hay años disponibles para cierre anual.<br>
                        <small>Un año aparece aquí cuando todos sus períodos mensuales están cerrados
                        y no tiene cierre anual previo.</small>
                    </div>

                    <a href="<?= base_url('contabilidad/periodos') ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i>Volver a períodos
                    </a>

                <?php endif; ?>

                <div id="resultadoCierreAnual" class="mt-3 d-none"></div>
            </div>
        </div>

    </div>

    <!-- ── HISTORIAL DE CIERRES ANUALES ─────────────────────────── -->
    <?php if (!empty($aniosCerrados)): ?>
    <div class="col-md-4 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="header-title mb-0">
                    <i class="fa-solid fa-history me-2"></i>Años cerrados anualmente
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Año</th>
                            <th>Fecha de cierre</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aniosCerrados as $ac): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?= esc($ac->anio) ?></span>
                                </td>
                                <td class="text-muted small">
                                    <?= $ac->fecha_cierre_anual
                                        ? date('d/m/Y', strtotime($ac->fecha_cierre_anual))
                                        : '—' ?>
                                </td>
                                <td>
                                    <a href="<?= base_url("contabilidad/periodos?anio={$ac->anio}") ?>"
                                       class="btn btn-xs btn-outline-secondary py-0 px-1"
                                       title="Ver períodos del año">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function ejecutarCierreAnual() {
    const anio = document.getElementById('anio').value;
    if (!anio) { Swal.fire('Requerido', 'Selecciona el año a cerrar', 'warning'); return; }

    Swal.fire({
        title: `¿Ejecutar cierre anual ${anio}?`,
        html: `Se generará el asiento de cierre del ejercicio y el año quedará
               <strong>bloqueado permanentemente</strong>.<br><br>
               ¿Deseas continuar?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, ejecutar cierre',
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
                    const utilidad    = d.utilidad || 0;
                    const esPositivo  = utilidad >= 0;
                    const resultadoTx = esPositivo
                        ? `<strong class="text-success">Utilidad del ejercicio: $${parseFloat(utilidad).toFixed(2)}</strong>`
                        : `<strong class="text-danger">Pérdida del ejercicio: $${Math.abs(utilidad).toFixed(2)}</strong>`;
                    el.innerHTML = `<div class="alert alert-success">
                        <i class="fa-solid fa-check-circle me-2"></i>${d.message}<br>${resultadoTx}
                    </div>`;
                    Swal.fire('Cierre completado', d.message, 'success')
                        .then(() => { window.location = '<?= base_url('contabilidad/periodos') ?>'; });
                } else {
                    el.innerHTML = `<div class="alert alert-danger">
                        <i class="fa-solid fa-times-circle me-2"></i>${d.message}
                    </div>`;
                    Swal.fire('Error', d.message, 'error');
                }
            });
    });
}
</script>

<?= $this->endSection() ?>
