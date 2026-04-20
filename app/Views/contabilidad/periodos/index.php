<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .modal-content {
    border-radius: 10px;
    border: none;
}

.modal-header {
    border-bottom: 1px solid #eee;
}

.modal-footer {
    border-top: 1px solid #eee;
}

.modal-body label {
    margin-bottom: 2px;
}
</style>
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between flex-wrap gap-2">

                <!-- Título -->
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-calendar-days me-2"></i>Períodos Contables
                </h4>

                <!-- Acciones -->
                <div class="d-flex align-items-center gap-2">

                    <!-- Año -->
                    <div class="d-flex align-items-center gap-1 bg-light border rounded px-2 py-1">
                        <i class="fa-solid fa-calendar text-muted small"></i>
                        <small class="text-muted mr-4">Ver año:</small>
                        <select id="filtroAnio"
                            class="form-select form-select-sm border-2 bg-transparent p-0 shadow-none text-center"
                            style="width:75px"
                            onchange="cambiarAnio(this.value)">

                            <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $anioSel ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                </div>

            </div>
            <div class="card-body">
                <div class="row g-3">

                    <?php
                    $mesesExistentes = [];
                    foreach ($periodos as $p) $mesesExistentes[$p->mes] = $p;

                    for ($m = 1; $m <= 12; $m++):
                        $p = $mesesExistentes[$m] ?? null;

                        $estadoColor = !$p ? 'bg-light'
                            : ($p->estado === 'ABIERTO' ? 'bg-success-subtle border-success' : 'bg-secondary-subtle border-secondary');
                    ?>

                        <div class="col-xl-2 col-md-3 col-6 mb-3">
                            <div class="border rounded-3 h-100 p-3 shadow-sm <?= $estadoColor ?>">

                                <!-- Mes -->
                                <div class="fw-bold">
                                    <?= $meses[$m] ?>
                                </div>

                                <div class="text-muted small mb-2">
                                    <?= $anioSel ?>
                                </div>

                                <!-- Estado -->
                                <?php if ($p): ?>
                                    <div class="mb-2">
                                        <span class="badge text-white <?= $p->estado === 'ABIERTO' ? 'bg-success' : 'bg-secondary' ?>">
                                            <i class="fa-solid fa-<?= $p->estado === 'ABIERTO' ? 'lock-open' : 'lock' ?>"></i>
                                            <?= $p->estado ?>
                                        </span>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="d-flex flex-wrap gap-1">

                                        <?php if (tienePermiso('cerrar_periodo_contable') && $p->estado === 'ABIERTO'): ?>
                                            <button class="btn btn-sm btn-outline-danger w-100"
                                                onclick="cerrarPeriodo(<?= $p->id ?>, '<?= $meses[$m] ?> <?= $anioSel ?>')">
                                                <i class="fa-solid fa-lock"></i> Cerrar
                                            </button>
                                        <?php endif; ?>

                                        <?php if (tienePermiso('cerrar_periodo_contable') && $p->estado === 'CERRADO'): ?>
                                            <button class="btn btn-sm btn-outline-warning w-100"
                                                onclick="reabrirPeriodo(<?= $p->id ?>, '<?= $meses[$m] ?> <?= $anioSel ?>')">
                                                <i class="fa-solid fa-lock-open"></i> Reabrir
                                            </button>
                                        <?php endif; ?>

                                    </div>

                                    <?php if ($p->fecha_cierre): ?>
                                        <div class="text-muted mt-2" style="font-size:0.75rem">
                                            <?= date('d/m/Y', strtotime($p->fecha_cierre)) ?>
                                        </div>
                                    <?php endif; ?>

                                <?php else: ?>

                                    <div class="text-muted small mb-2">
                                        <i class="fa-solid fa-circle-minus"></i> No creado
                                    </div>

                                    <?php if (tienePermiso('crear_periodo_contable')): ?>
                                        <button class="btn btn-sm btn-outline-primary w-100"
                                            onclick="crearPeriodo(<?= $anioSel ?>, <?= $m ?>)">
                                            <i class="fa-solid fa-plus"></i> Crear
                                        </button>
                                    <?php endif; ?>

                                <?php endif; ?>

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
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Período</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Año</label>
                    <input type="number" id="nAnio" class="form-control" value="<?= date('Y') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Mes</label>
                    <select id="nMes" class="form-control">
                        <?php foreach ($meses as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
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
        $('#modalPeriodo').modal('show');
    }

    function crearPeriodo(anio, mes) {
        $('#nAnio').val(anio);
        $('#nMes').val(mes);
        $('#modalPeriodo').modal('show');
    }

    function guardarPeriodo() {
        const form = new FormData();
        form.append('anio', $('#nAnio').val());
        form.append('mes', $('#nMes').val());
        fetch('<?= base_url('contabilidad/periodos/store') ?>', {
                method: 'POST',
                body: form
            })
            .then(r => r.json()).then(d => {
                if (d.success) Swal.fire('Creado', d.message, 'success').then(() => location.reload());
                else Swal.fire('Error', d.message, 'error');
            });
    }

    function cerrarPeriodo(id, nombre) {
        Swal.fire({
            title: '¿Cerrar período?',
            html: `<strong>${nombre}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Cerrar',
            cancelButtonText: 'Cancelar'
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch(`<?= base_url('contabilidad/periodos/cerrar/') ?>${id}`, {
                    method: 'POST'
                })
                .then(r => r.json()).then(d => {
                    if (d.success) Swal.fire('Cerrado', d.message, 'success').then(() => location.reload());
                    else Swal.fire('Error', d.message, 'error');
                });
        });
    }

    function reabrirPeriodo(id, nombre) {
        Swal.fire({
            title: '¿Reabrir período?',
            html: `<strong>${nombre}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Reabrir',
            cancelButtonText: 'Cancelar'
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch(`<?= base_url('contabilidad/periodos/reabrir/') ?>${id}`, {
                    method: 'POST'
                })
                .then(r => r.json()).then(d => {
                    if (d.success) Swal.fire('Reabierto', d.message, 'success').then(() => location.reload());
                    else Swal.fire('Error', d.message, 'error');
                });
        });
    }
</script>
<style>
    .border-dashed {
        border-style: dashed !important;
        opacity: .6
    }
</style>
<?= $this->endSection() ?>