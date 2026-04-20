<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$estadoBadge = ['BORRADOR' => 'warning', 'APROBADO' => 'success', 'ANULADO' => 'danger'];
$tipoBadge   = ['DIARIO' => 'primary text-white', 'AJUSTE' => 'info', 'CIERRE' => 'dark', 'APERTURA' => 'secondary'];
$mesesN = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header py-2">

                <div class="d-flex flex-wrap justify-content-between gap-2">

                    <!-- IZQUIERDA -->
                    <div class="d-flex align-items-center flex-wrap gap-2">

                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-invoice text-primary mr-1"></i>

                            <span>
                                AST-<?= str_pad($asiento->numero_asiento, 5, '0', STR_PAD_LEFT) ?>
                            </span>
                        </h5>

                        <!-- Badges más sutiles -->
                        <span class="badge ml-3 mr-1 badge-<?= $estadoBadge[$asiento->estado] ?> px-2 py-1">
                            <?= $asiento->estado ?>
                        </span>

                        <span class="badge mr-3 mr-1 badge-light border px-2 py-1">
                            <?= $asiento->tipo ?>
                        </span>

                    </div>

                    <!-- DERECHA -->
                    <div class="d-flex align-items-center gap-2">

                        <?php if ($asiento->estado === 'BORRADOR'): ?>

                            <?php if (tienePermiso('aprobar_asiento')): ?>
                                <button class="btn btn-success btn-sm d-flex align-items-center gap-1 mr-1"
                                    onclick="aprobarAsiento(<?= $asiento->id ?>)">
                                    <i class="fa-solid fa-check-circle"></i>
                                    <span>Aprobar</span>
                                </button>
                            <?php endif; ?>

                            <?php if (tienePermiso('anular_asiento')): ?>
                                <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 mr-1"
                                    onclick="anularAsiento(<?= $asiento->id ?>)">
                                    <i class="fa-solid fa-ban"></i>
                                    <span>Anular</span>
                                </button>
                            <?php endif; ?>

                        <?php elseif ($asiento->estado === 'APROBADO' && tienePermiso('anular_asiento')): ?>

                            <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 mr-1"
                                onclick="anularAsiento(<?= $asiento->id ?>)">
                                <i class="fa-solid fa-ban"></i>
                                <span>Anular</span>
                            </button>

                        <?php elseif ($asiento->estado === 'ANULADO'): ?>

                            <span class="badge badge-danger px-3 py-2 d-flex align-items-center gap-1 mr-1">
                                <i class="fa-solid fa-ban"></i> Anulado
                            </span>

                        <?php endif; ?>

                        <div class="vr mx-1"></div>

                        <a href="<?= base_url('contabilidad/asientos') ?>"
                            class="btn btn-light btn-sm border d-flex align-items-center gap-1">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Volver</span>
                        </a>

                    </div>

                </div>

            </div>

            <div class="card-body">
                <!-- Info cabecera -->
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <small class="text-muted d-block">Fecha</small>
                        <strong><?= date('d/m/Y', strtotime($asiento->fecha)) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Período</small>
                        <strong><?= isset($asiento->mes) ? $mesesN[$asiento->mes] . ' ' . $asiento->anio : 'N/A' ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Descripción</small>
                        <strong><?= esc($asiento->descripcion) ?></strong>
                    </div>
                    <?php if ($asiento->referencia): ?>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Referencia</small>
                            <strong><?= esc($asiento->referencia) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if ($asiento->usuario_nombre): ?>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Creado por</small>
                            <strong><?= esc($asiento->usuario_nombre) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if ($asiento->fecha_aprobacion): ?>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Aprobado</small>
                            <strong><?= date('d/m/Y H:i', strtotime($asiento->fecha_aprobacion)) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if ($asiento->motivo_anulacion): ?>
                        <div class="col-md-12">
                            <div class="alert alert-danger mb-0">
                                <strong>Motivo de anulación:</strong> <?= esc($asiento->motivo_anulacion) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Líneas del asiento -->
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:50px">#</th>
                            <th style="width:130px">Código</th>
                            <th>Cuenta</th>
                            <th>Descripción</th>
                            <th class="text-end" style="width:130px">Debe</th>
                            <th class="text-end" style="width:130px">Haber</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lineas as $l): ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $l->orden ?></td>
                                <td><code><?= esc($l->codigo) ?></code></td>
                                <td><?= esc($l->cuenta_nombre) ?></td>
                                <td class="text-muted small"><?= esc($l->descripcion) ?></td>
                                <td class="text-end <?= $l->debe > 0 ? 'fw-semibold' : 'text-muted' ?>">
                                    <?= $l->debe > 0 ? '$ ' . number_format($l->debe, 2) : '-' ?>
                                </td>
                                <td class="text-end <?= $l->haber > 0 ? 'fw-semibold' : 'text-muted' ?>">
                                    <?= $l->haber > 0 ? '$ ' . number_format($l->haber, 2) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">TOTALES:</td>
                            <td class="text-end text-primary">$ <?= number_format($asiento->total_debe, 2) ?></td>
                            <td class="text-end text-success">$ <?= number_format($asiento->total_haber, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <?php if (abs($asiento->total_debe - $asiento->total_haber) > 0.01): ?>
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Advertencia: El asiento no cuadra. Diferencia: $<?= number_format(abs($asiento->total_debe - $asiento->total_haber), 2) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal anular -->
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anular Asiento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small fw-semibold">Motivo de anulación</label>
                <textarea id="motivoAnulacion" class="form-control" rows="3" placeholder="Ingresa el motivo..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm" onclick="confirmarAnulacion()">Anular</button>
            </div>
        </div>
    </div>
</div>

<script>
    let asientoIdActual = null;

    function aprobarAsiento(id) {
        Swal.fire({
            title: '¿Aprobar asiento?',
            text: 'Esto actualizará los saldos contables.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Aprobar',
            cancelButtonText: 'Cancelar'
        }).then(r => {
            if (!r.isConfirmed) return;
            fetch(`<?= base_url('contabilidad/asientos/aprobar/') ?>${id}`, {
                    method: 'POST'
                })
                .then(r => r.json()).then(d => {
                    if (d.success) Swal.fire('Aprobado', d.message, 'success').then(() => location.reload());
                    else Swal.fire('Error', d.message, 'error');
                });
        });
    }

    function anularAsiento(id) {
        asientoIdActual = id;
        document.getElementById('motivoAnulacion').value = '';
        new bootstrap.Modal(document.getElementById('modalAnular')).show();
    }

    function confirmarAnulacion() {
        const motivo = document.getElementById('motivoAnulacion').value.trim();
        if (!motivo) {
            Swal.fire('Requerido', 'Ingresa el motivo de anulación', 'warning');
            return;
        }
        const form = new FormData();
        form.append('motivo', motivo);
        fetch(`<?= base_url('contabilidad/asientos/anular/') ?>${asientoIdActual}`, {
                method: 'POST',
                body: form
            })
            .then(r => r.json()).then(d => {
                if (d.success) Swal.fire('Anulado', d.message, 'success').then(() => location.reload());
                else Swal.fire('Error', d.message, 'error');
            });
    }
</script>

<?= $this->endSection() ?>