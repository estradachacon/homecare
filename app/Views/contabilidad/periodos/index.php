<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0"><i class="fa-solid fa-calendar-days me-2"></i>Períodos Contables</h4>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <select id="filtroAnio" class="form-select form-select-sm" style="width:120px" onchange="cambiarAnio(this.value)">
                        <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $anioSel ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <?php if (tienePermiso('crear_periodo_contable')): ?>
                    <button class="btn btn-primary btn-sm" onclick="abrirModalNuevo()">
                        <i class="fa-solid fa-plus"></i> Nuevo Período
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $mesesExistentes = [];
                    foreach ($periodos as $p) $mesesExistentes[$p->mes] = $p;
                    for ($m = 1; $m <= 12; $m++):
                        $p = $mesesExistentes[$m] ?? null;
                    ?>
                    <div class="col-md-3 col-6">
                        <div class="card border <?= $p ? ($p->estado === 'CERRADO' ? 'border-secondary' : 'border-success') : 'border-dashed' ?> h-100">
                            <div class="card-body text-center py-3">
                                <div class="fw-bold mb-1"><?= $meses[$m] ?></div>
                                <div class="text-muted small mb-2"><?= $anioSel ?></div>
                                <?php if ($p): ?>
                                    <span class="badge <?= $p->estado === 'ABIERTO' ? 'bg-success' : 'bg-secondary' ?> mb-2">
                                        <i class="fa-solid fa-<?= $p->estado === 'ABIERTO' ? 'lock-open' : 'lock' ?>"></i>
                                        <?= $p->estado ?>
                                    </span>
                                    <div class="d-flex gap-1 justify-content-center mt-2 flex-wrap">
                                        <?php if (tienePermiso('cerrar_periodo_contable') && $p->estado === 'ABIERTO'): ?>
                                        <button class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size:0.75rem" onclick="cerrarPeriodo(<?= $p->id ?>, '<?= $meses[$m] ?> <?= $anioSel ?>')">
                                            <i class="fa-solid fa-lock"></i> Cerrar
                                        </button>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('cerrar_periodo_contable') && $p->estado === 'CERRADO'): ?>
                                        <button class="btn btn-xs btn-outline-warning py-0 px-2" style="font-size:0.75rem" onclick="reabrirPeriodo(<?= $p->id ?>, '<?= $meses[$m] ?> <?= $anioSel ?>')">
                                            <i class="fa-solid fa-lock-open"></i> Reabrir
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($p->fecha_cierre): ?>
                                    <div class="text-muted mt-1" style="font-size:0.72rem">Cerrado: <?= date('d/m/Y', strtotime($p->fecha_cierre)) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small d-block mb-2"><i class="fa-solid fa-circle-minus"></i> No creado</span>
                                    <?php if (tienePermiso('crear_periodo_contable')): ?>
                                    <button class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:0.75rem"
                                        onclick="crearPeriodo(<?= $anioSel ?>, <?= $m ?>)">
                                        <i class="fa-solid fa-plus"></i> Crear
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Período -->
<div class="modal fade" id="modalPeriodo" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nuevo Período</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Año</label>
                    <input type="number" id="nAnio" class="form-control" value="<?= date('Y') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Mes</label>
                    <select id="nMes" class="form-select">
                        <?php foreach ($meses as $k => $v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarPeriodo()">Crear</button>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarAnio(anio) {
    window.location = '<?= base_url('contabilidad/periodos') ?>?anio=' + anio;
}
function abrirModalNuevo() {
    new bootstrap.Modal(document.getElementById('modalPeriodo')).show();
}
function crearPeriodo(anio, mes) {
    $('#nAnio').val(anio); $('#nMes').val(mes);
    new bootstrap.Modal(document.getElementById('modalPeriodo')).show();
}
function guardarPeriodo() {
    const form = new FormData();
    form.append('anio', $('#nAnio').val());
    form.append('mes',  $('#nMes').val());
    fetch('<?= base_url('contabilidad/periodos/store') ?>', { method:'POST', body:form })
        .then(r=>r.json()).then(d=>{
            if(d.success) Swal.fire('Creado', d.message, 'success').then(()=>location.reload());
            else Swal.fire('Error', d.message, 'error');
        });
}
function cerrarPeriodo(id, nombre) {
    Swal.fire({ title:'¿Cerrar período?', html:`<strong>${nombre}</strong>`, icon:'warning',
        showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Cerrar', cancelButtonText:'Cancelar'
    }).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`<?= base_url('contabilidad/periodos/cerrar/') ?>${id}`, {method:'POST'})
            .then(r=>r.json()).then(d=>{
                if(d.success) Swal.fire('Cerrado', d.message, 'success').then(()=>location.reload());
                else Swal.fire('Error', d.message, 'error');
            });
    });
}
function reabrirPeriodo(id, nombre) {
    Swal.fire({ title:'¿Reabrir período?', html:`<strong>${nombre}</strong>`, icon:'question',
        showCancelButton:true, confirmButtonText:'Reabrir', cancelButtonText:'Cancelar'
    }).then(r=>{
        if(!r.isConfirmed) return;
        fetch(`<?= base_url('contabilidad/periodos/reabrir/') ?>${id}`, {method:'POST'})
            .then(r=>r.json()).then(d=>{
                if(d.success) Swal.fire('Reabierto', d.message, 'success').then(()=>location.reload());
                else Swal.fire('Error', d.message, 'error');
            });
    });
}
</script>
<style>.border-dashed{border-style:dashed!important;opacity:.6}</style>
<?= $this->endSection() ?>
