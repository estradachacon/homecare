<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
/** @var array        $periodos */
/** @var array        $anios */
/** @var int          $anioSel */
/** @var array        $meses */
/** @var int          $totalCreados */
/** @var int          $totalCerrados */
/** @var bool         $cierreAnualEjecutado */
/** @var string|null  $fechaCierreAnual */
/** @var array        $asientosPorPeriodo */

$todosCerrados = $totalCreados === 12 && $totalCerrados === 12;
$porcCerrados  = round(($totalCerrados / 12) * 100);
$porcAbiertos  = $totalCreados > $totalCerrados
    ? round((($totalCreados - $totalCerrados) / 12) * 100)
    : 0;

$mesesExistentes = [];
foreach ($periodos as $p) $mesesExistentes[$p->mes] = $p;
?>

<style>
.periodo-card {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 14px;
    height: 100%;
    transition: box-shadow .15s;
    background: #fff;
    border-left-width: 4px;
}
.periodo-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,.1); }
.periodo-card.estado-abierto  { border-left-color: #28a745; background: #f6fff8; }
.periodo-card.estado-cerrado  { border-left-color: #6c757d; background: #f8f9fa; }
.periodo-card.estado-anual    { border-left-color: #dc3545; background: #fff5f5; }
.periodo-card.estado-vacio    { border-left-color: #ced4da; border-style: dashed; background: #fafafa; opacity:.8; }
.periodo-card .mes-nombre     { font-weight: 600; font-size: 1rem; }
.periodo-card .mes-anio       { font-size: .75rem; color: #6c757d; }
.periodo-card .stats-box      { background: rgba(0,0,0,.04); border-radius: 5px; padding: 6px 8px; font-size: .72rem; margin-bottom: 8px; }
.banner-anual { border-radius: 0; border-left: 0; border-right: 0; border-top: 0; margin-bottom: 0; }
.anio-select  { border: 0; background: transparent; font-size: .9rem; width: 72px; padding: 0; }
.anio-select:focus { outline: none; box-shadow: none; }
</style>

<!-- ── CABECERA ──────────────────────────────────────────────────────── -->
<div class="card mb-3">
    <div class="card-header">
        <div class="d-flex justify-content-between flex-wrap">

            <div class="d-flex align-items-center flex-wrap">
                <i class="fa-solid fa-calendar-days fa-lg text-primary mr-2"></i>
                <h4 class="mb-0 mr-2">Períodos Contables</h4>

                <span class="badge badge-secondary mr-2"><?= $anioSel ?></span>

                <?php if ($cierreAnualEjecutado): ?>
                    <span class="badge badge-danger">
                        <i class="fa-solid fa-lock mr-1"></i>Año cerrado anualmente
                    </span>
                <?php elseif ($todosCerrados): ?>
                    <span class="badge badge-warning text-dark">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>Listo para cierre anual
                    </span>
                <?php else: ?>
                    <span class="badge badge-success">
                        <i class="fa-solid fa-circle-play mr-1"></i>En curso
                    </span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0">

                <?php if (!$cierreAnualEjecutado && tienePermiso('ejecutar_cierre_mes')): ?>
                    <a href="<?= base_url('contabilidad/procesos/cierre-mes') ?>"
                       class="btn btn-sm btn-outline-secondary mr-2">
                        <i class="fa-solid fa-circle-xmark mr-1"></i>Cierre de mes
                    </a>
                <?php endif; ?>

                <?php if ($todosCerrados && !$cierreAnualEjecutado && tienePermiso('ejecutar_cierre_anual')): ?>
                    <a href="<?= base_url('contabilidad/procesos/cierre-anual') ?>"
                       class="btn btn-sm btn-danger mr-2">
                        <i class="fa-solid fa-calendar-xmark mr-1"></i>Cierre anual
                    </a>
                <?php endif; ?>

                <!-- Selector de año -->
                <div class="d-flex align-items-center border rounded px-2 py-1 bg-light">
                    <i class="fa-solid fa-calendar text-muted mr-1" style="font-size:.8rem"></i>
                    <small class="text-muted mr-1">Año:</small>
                    <select class="anio-select" onchange="cambiarAnio(this.value)">
                        <?php for ($y = (int)date('Y') + 1; $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $anioSel ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <!-- Banners de estado -->
    <?php if ($cierreAnualEjecutado): ?>
        <div class="alert alert-danger banner-anual mb-0 py-2 px-3 rounded-0" style="border-bottom:1px solid #f5c6cb;">
            <i class="fa-solid fa-lock mr-1"></i>
            <strong>Año <?= $anioSel ?> cerrado anualmente.</strong>
            Los períodos de este año no pueden reabrirse ni crearse.
            <?php if ($fechaCierreAnual): ?>
                <span class="ml-2 text-muted" style="font-size:.85rem">
                    Cierre ejecutado el <?= date('d/m/Y', strtotime($fechaCierreAnual)) ?>
                </span>
            <?php endif; ?>
        </div>
    <?php elseif ($todosCerrados): ?>
        <div class="alert alert-warning banner-anual mb-0 py-2 px-3 rounded-0" style="border-bottom:1px solid #ffeeba;">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
            Todos los meses de <strong><?= $anioSel ?></strong> están cerrados.
            <?php if (tienePermiso('ejecutar_cierre_anual')): ?>
                Puedes ejecutar el
                <a href="<?= base_url('contabilidad/procesos/cierre-anual') ?>" class="font-weight-bold">cierre anual</a>.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Barra de progreso -->
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted">
                <strong><?= $totalCerrados ?></strong> de <strong>12</strong> meses cerrados
                &nbsp;&middot;&nbsp;
                <strong><?= $totalCreados ?></strong> de <strong>12</strong> creados
            </small>
            <small class="<?= $porcCerrados === 100 ? 'text-success font-weight-bold' : 'text-muted' ?>">
                <?= $porcCerrados ?>%
            </small>
        </div>
        <div class="progress" style="height:8px; border-radius:4px;">
            <div class="progress-bar bg-success"
                 style="width:<?= $porcCerrados ?>%"
                 title="<?= $totalCerrados ?> mes(es) cerrado(s)"></div>
            <div class="progress-bar bg-success"
                 style="width:<?= $porcAbiertos ?>%; opacity:.3;"
                 title="<?= $totalCreados - $totalCerrados ?> mes(es) abierto(s)"></div>
        </div>
    </div>
</div>

<!-- ── GRID DE 12 MESES ──────────────────────────────────────────────── -->
<div class="card">
    <div class="card-body">
        <div class="row">

            <?php for ($m = 1; $m <= 12; $m++):
                $p             = $mesesExistentes[$m] ?? null;
                $stats         = ($p && isset($asientosPorPeriodo[$p->id])) ? $asientosPorPeriodo[$p->id] : null;
                $esCerradoAnual = $p && !empty($p->cierre_anual) && (int)$p->cierre_anual === 1;

                if (!$p)                  $cardClass = 'estado-vacio';
                elseif ($esCerradoAnual)  $cardClass = 'estado-anual';
                elseif ($p->estado === 'ABIERTO') $cardClass = 'estado-abierto';
                else                      $cardClass = 'estado-cerrado';
            ?>

                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="periodo-card <?= $cardClass ?>">

                        <!-- Encabezado del mes -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="mes-nombre"><?= $meses[$m] ?></div>
                                <div class="mes-anio"><?= $anioSel ?></div>
                            </div>
                            <?php if ($esCerradoAnual): ?>
                                <i class="fa-solid fa-calendar-xmark text-danger" title="Cierre anual ejecutado"></i>
                            <?php elseif ($p && $p->estado === 'ABIERTO'): ?>
                                <i class="fa-solid fa-lock-open text-success" title="Abierto"></i>
                            <?php elseif ($p): ?>
                                <i class="fa-solid fa-lock text-secondary" title="Cerrado"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-circle-minus text-muted" title="No creado"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Badge de estado -->
                        <?php if ($p): ?>
                            <div class="mb-2">
                                <?php if ($esCerradoAnual): ?>
                                    <span class="badge badge-danger" style="font-size:.7rem">
                                        <i class="fa-solid fa-calendar-xmark mr-1"></i>Cierre anual
                                    </span>
                                <?php elseif ($p->estado === 'ABIERTO'): ?>
                                    <span class="badge badge-success" style="font-size:.7rem">
                                        <i class="fa-solid fa-lock-open mr-1"></i>ABIERTO
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="font-size:.7rem">
                                        <i class="fa-solid fa-lock mr-1"></i>CERRADO
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Stats -->
                            <?php if ($stats): ?>
                                <div class="stats-box">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Asientos</span>
                                        <strong><?= number_format($stats->total) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-muted">Movimientos</span>
                                        <strong>$<?= number_format($stats->suma_debe, 0) ?></strong>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-2" style="font-size:.75rem;">Sin asientos</p>
                            <?php endif; ?>

                            <!-- Fecha de cierre -->
                            <?php if ($p->fecha_cierre): ?>
                                <p class="text-muted mb-2" style="font-size:.72rem;">
                                    <i class="fa-solid fa-calendar-check mr-1"></i>
                                    <?= date('d/m/Y', strtotime($p->fecha_cierre)) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Botones -->
                            <?php if (tienePermiso('cerrar_periodo_contable') && $p->estado === 'ABIERTO' && !$esCerradoAnual): ?>
                                <button class="btn btn-sm btn-outline-danger btn-block"
                                        onclick="cerrarPeriodo(<?= $p->id ?>, '<?= $meses[$m] ?> <?= $anioSel ?>')">
                                    <i class="fa-solid fa-lock mr-1"></i>Cerrar
                                </button>
                            <?php elseif (tienePermiso('cerrar_periodo_contable') && $p->estado === 'CERRADO'): ?>
                                <?php if ($esCerradoAnual): ?>
                                    <button class="btn btn-sm btn-outline-secondary btn-block"
                                            disabled
                                            title="No se puede reabrir: año con cierre anual ejecutado">
                                        <i class="fa-solid fa-ban mr-1"></i>Bloqueado
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-warning btn-block"
                                            onclick="reabrirPeriodo(<?= $p->id ?>, '<?= $meses[$m] ?> <?= $anioSel ?>')">
                                        <i class="fa-solid fa-lock-open mr-1"></i>Reabrir
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php else: ?>

                            <p class="text-muted mb-2" style="font-size:.8rem;">No creado</p>

                            <?php if (tienePermiso('crear_periodo_contable') && !$cierreAnualEjecutado): ?>
                                <button class="btn btn-sm btn-outline-primary btn-block"
                                        onclick="crearPeriodo(<?= $anioSel ?>, <?= $m ?>)">
                                    <i class="fa-solid fa-plus mr-1"></i>Crear
                                </button>
                            <?php endif; ?>

                        <?php endif; ?>

                    </div>
                </div>

            <?php endfor; ?>

        </div>
    </div>
</div>

<!-- ── MODAL NUEVO PERÍODO ───────────────────────────────────────────── -->
<div class="modal fade" id="modalPeriodo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-plus mr-1"></i>Nuevo Período
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Año</label>
                    <input type="number" id="nAnio" class="form-control" value="<?= $anioSel ?>">
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Mes</label>
                    <select id="nMes" class="form-control">
                        <?php foreach ($meses as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarPeriodo()">
                    <i class="fa-solid fa-check mr-1"></i>Crear
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function cambiarAnio(anio) {
        window.location = '<?= base_url('contabilidad/periodos') ?>?anio=' + anio;
    }

    function crearPeriodo(anio, mes) {
        $('#nAnio').val(anio);
        $('#nMes').val(mes);
        $('#modalPeriodo').modal('show');
    }

    function guardarPeriodo() {
        const form = new FormData();
        form.append('anio', $('#nAnio').val());
        form.append('mes',  $('#nMes').val());
        fetch('<?= base_url('contabilidad/periodos/store') ?>', { method: 'POST', body: form })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Swal.fire('Creado', d.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', d.message, 'error');
                }
            });
    }

    function cerrarPeriodo(id, nombre) {
        Swal.fire({
            title: '¿Cerrar período?',
            html: `<strong>${nombre}</strong><br><small class="text-muted">Los asientos en borrador deben aprobarse o anularse antes.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Cerrar',
            cancelButtonText: 'Cancelar'
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch(`<?= base_url('contabilidad/periodos/cerrar/') ?>${id}`, { method: 'POST' })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire('Cerrado', d.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', d.message, 'error');
                    }
                });
        });
    }

    function reabrirPeriodo(id, nombre) {
        Swal.fire({
            title: '¿Reabrir período?',
            html: `<strong>${nombre}</strong><br><small class="text-muted">Se podrán agregar o modificar asientos en este período.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Reabrir',
            cancelButtonText: 'Cancelar'
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch(`<?= base_url('contabilidad/periodos/reabrir/') ?>${id}`, { method: 'POST' })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        Swal.fire('Reabierto', d.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Bloqueado', d.message, 'error');
                    }
                });
        });
    }
</script>

<?= $this->endSection() ?>
