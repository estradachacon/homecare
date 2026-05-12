<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
/** @var array       $anios */
/** @var array       $aniosCerrados */
$mesesNombres = [
    1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',
    5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',
    9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic',
];
?>

<style>
.anual-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 18px 20px;
    background: #fff;
    transition: box-shadow .15s;
}
.anual-card:hover { box-shadow: 0 3px 14px rgba(0,0,0,.09); }
.anual-card .anio-badge {
    font-size: 1.35rem;
    font-weight: 700;
    color: #1a2b3c;
    line-height: 1;
}
.anual-card .cierre-fecha {
    font-size: .75rem;
    color: #6c757d;
    margin-top: 2px;
}
.anual-card .meses-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin: 12px 0 14px;
}
.anual-card .mes-chip {
    font-size: .65rem;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 4px;
    background: #e9ecef;
    color: #495057;
}
.anual-card .utilidad-line {
    font-size: .8rem;
    color: #495057;
    padding: 6px 10px;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 12px;
}
</style>

<div class="row">

    <!-- ── FORMULARIO DE CIERRE ──────────────────────────────── -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-calendar-xmark mr-2"></i>Ejecutar Cierre Anual
                </h4>
            </div>
            <div class="card-body">

                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                    <strong>Proceso irreversible hasta revertir:</strong>
                    <ul class="mb-0 mt-1 pl-3">
                        <li>Requiere que <strong>todos los meses</strong> del año estén cerrados</li>
                        <li>Calcula la utilidad o pérdida del ejercicio</li>
                        <li>Genera el asiento de cierre automático</li>
                        <li>Bloquea la reapertura de períodos del año</li>
                    </ul>
                </div>

                <?php if (!empty($anios)): ?>

                    <div class="form-group">
                        <label class="font-weight-bold">Año a cerrar <span class="text-danger">*</span></label>
                        <select id="anio" class="form-control">
                            <option value="">— Seleccionar año —</option>
                            <?php foreach ($anios as $a): ?>
                                <option value="<?= $a ?>"><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            Solo aparecen años con los 12 meses cerrados y sin cierre anual previo.
                        </small>
                    </div>

                    <div class="d-flex mt-3">
                        <a href="<?= base_url('contabilidad/periodos') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left mr-1"></i>Cancelar
                        </a>
                        <button class="btn btn-danger btn-sm ml-auto" onclick="ejecutarCierreAnual()">
                            <i class="fa-solid fa-calendar-xmark mr-1"></i>Ejecutar Cierre Anual
                        </button>
                    </div>

                <?php else: ?>

                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-circle-info fa-2x mb-2 d-block"></i>
                        No hay años disponibles para cierre anual.<br>
                        <small>Un año aparece aquí cuando todos sus 12 períodos mensuales están cerrados.</small>
                    </div>
                    <a href="<?= base_url('contabilidad/periodos') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left mr-1"></i>Volver a períodos
                    </a>

                <?php endif; ?>

                <div id="resultadoCierreAnual" class="mt-3 d-none"></div>
            </div>
        </div>
    </div>

    <!-- ── HISTORIAL DE CIERRES ANUALES ─────────────────────── -->
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <h5 class="header-title mb-0">
                    <i class="fa-solid fa-history mr-2"></i>Años cerrados anualmente
                </h5>
                <?php if (!empty($aniosCerrados)): ?>
                    <span class="badge badge-secondary"><?= count($aniosCerrados) ?> año(s)</span>
                <?php endif; ?>
            </div>
            <div class="card-body">

                <?php if (empty($aniosCerrados)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-box-open fa-2x mb-2 d-block"></i>
                        Ningún año ha sido cerrado anualmente todavía.
                    </div>

                <?php else: ?>
                    <div class="row">
                        <?php foreach ($aniosCerrados as $ac): ?>
                            <div class="col-md-6 mb-3">
                                <div class="anual-card">

                                    <!-- Encabezado del card -->
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="anio-badge">
                                                <i class="fa-solid fa-lock text-danger mr-1" style="font-size:1rem;"></i>
                                                <?= esc($ac->anio) ?>
                                            </div>
                                            <div class="cierre-fecha">
                                                Cerrado el
                                                <?= $ac->fecha_cierre_anual
                                                    ? date('d/m/Y', strtotime($ac->fecha_cierre_anual))
                                                    : '—' ?>
                                            </div>
                                        </div>
                                        <span class="badge badge-danger" style="font-size:.7rem;">
                                            Cierre anual
                                        </span>
                                    </div>

                                    <!-- Chips de los 12 meses -->
                                    <div class="meses-strip">
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <span class="mes-chip"><?= $mesesNombres[$m] ?></span>
                                        <?php endfor; ?>
                                    </div>

                                    <!-- Utilidad del ejercicio si está disponible -->
                                    <?php if (isset($ac->utilidad) && $ac->utilidad !== null): ?>
                                        <?php $u = (float)$ac->utilidad; ?>
                                        <div class="utilidad-line">
                                            <?php if ($u >= 0): ?>
                                                <i class="fa-solid fa-arrow-trend-up text-success mr-1"></i>
                                                Utilidad: <strong class="text-success">$<?= number_format($u, 2) ?></strong>
                                            <?php else: ?>
                                                <i class="fa-solid fa-arrow-trend-down text-danger mr-1"></i>
                                                Pérdida: <strong class="text-danger">$<?= number_format(abs($u), 2) ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Acciones -->
                                    <div class="d-flex" style="gap:6px;">
                                        <a href="<?= base_url("contabilidad/periodos?anio={$ac->anio}") ?>"
                                           class="btn btn-outline-secondary btn-sm flex-fill text-center">
                                            <i class="fa-solid fa-calendar-days mr-1"></i>Ver períodos
                                        </a>
                                        <?php if (tienePermiso('ejecutar_cierre_anual')): ?>
                                            <button class="btn btn-outline-warning btn-sm flex-fill"
                                                    onclick="reabrirCierreAnual(<?= (int)$ac->anio ?>)">
                                                <i class="fa-solid fa-lock-open mr-1"></i>Reabrir año
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</div>

<script>
function ejecutarCierreAnual() {
    const anio = document.getElementById('anio').value;
    if (!anio) { Swal.fire('Requerido', 'Selecciona el año a cerrar', 'warning'); return; }

    Swal.fire({
        title: `¿Ejecutar cierre anual ${anio}?`,
        html: `Se generará el asiento de cierre del ejercicio y el año quedará
               <strong>bloqueado</strong> hasta que sea revertido.<br><br>
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
                    const u = d.utilidad || 0;
                    const txt = u >= 0
                        ? `<strong class="text-success">Utilidad del ejercicio: $${parseFloat(u).toFixed(2)}</strong>`
                        : `<strong class="text-danger">Pérdida del ejercicio: $${Math.abs(u).toFixed(2)}</strong>`;
                    el.innerHTML = `<div class="alert alert-success">
                        <i class="fa-solid fa-check-circle mr-2"></i>${d.message}<br>${txt}
                    </div>`;
                    Swal.fire('Cierre completado', d.message, 'success')
                        .then(() => location.reload());
                } else {
                    el.innerHTML = `<div class="alert alert-danger">
                        <i class="fa-solid fa-times-circle mr-2"></i>${d.message}
                    </div>`;
                    Swal.fire('Error', d.message, 'error');
                }
            });
    });
}

function reabrirCierreAnual(anio) {
    Swal.fire({
        title: `¿Reabrir cierre anual ${anio}?`,
        html: `Esto revertirá el cierre anual de <strong>${anio}</strong>:<br><br>
               <ul style="text-align:left;font-size:.875rem;">
                   <li>El asiento de cierre será <strong>anulado</strong></li>
                   <li>Los meses del año podrán reabrirse nuevamente</li>
                   <li>Deberás ejecutar el cierre nuevamente cuando corresponda</li>
               </ul>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e0a800',
        confirmButtonText: 'Sí, reabrir año',
        cancelButtonText: 'Cancelar'
    }).then(r => {
        if (!r.isConfirmed) return;

        const form = new FormData();
        form.append('anio', anio);

        fetch('<?= base_url('contabilidad/procesos/cierre-anual/reabrir') ?>', { method: 'POST', body: form })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Swal.fire('Reabierto', d.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', d.message, 'error');
                }
            });
    });
}
</script>

<?= $this->endSection() ?>
