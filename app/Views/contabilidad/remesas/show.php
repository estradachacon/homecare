<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$anulado = ($remesa->estado === 'ANULADO');
$cerrado = ($remesa->estado === 'CERRADO');
?>

<div class="card">
    <!-- Cabecera -->
    <div class="card-header py-2">
        <div class="d-flex flex-wrap justify-content-between">

            <div class="d-flex align-items-center flex-wrap">
                <h5 class="mb-0 mr-2">
                    <i class="fa-solid fa-layer-group text-primary mr-1"></i>
                    <?= esc($remesa->numero_remesa) ?>
                </h5>
                <?php
                $estadoColor = $anulado ? 'danger' : ($cerrado ? 'secondary' : 'success');
                $estadoIcon  = $anulado ? 'fa-ban' : ($cerrado ? 'fa-lock' : 'fa-circle-check');
                ?>
                <span class="badge badge-<?= $estadoColor ?> px-2 py-1 mr-1">
                    <i class="fa-solid <?= $estadoIcon ?> mr-1"></i><?= $remesa->estado ?>
                </span>
                <?php if ($remesa->tipo_partida_nombre): ?>
                    <span class="badge badge-light border px-2 py-1">
                        <i class="fa-solid fa-tag mr-1 text-muted"></i><?= esc($remesa->tipo_partida_nombre) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center">
                <?php if (!$anulado && !$cerrado && tienePermiso('anular_remesa_contable')): ?>
                    <button class="btn btn-outline-danger btn-sm mr-2" onclick="abrirModalAnular()">
                        <i class="fa-solid fa-ban mr-1"></i>Anular remesa
                    </button>
                <?php endif; ?>
                <a href="<?= base_url('contabilidad/remesas') ?>" class="btn btn-light btn-sm border">
                    <i class="fa-solid fa-arrow-left mr-1"></i>Volver
                </a>
            </div>

        </div>
    </div>

    <div class="card-body">

        <!-- Alerta anulación -->
        <?php if ($anulado): ?>
            <div class="alert alert-danger">
                <strong><i class="fa-solid fa-ban mr-1"></i>Remesa anulada</strong>
                <?php if ($remesa->fecha_anulacion): ?>
                    — <?= date('d/m/Y H:i', strtotime($remesa->fecha_anulacion)) ?>
                <?php endif; ?>
                <?php if ($remesa->motivo_anulacion): ?>
                    <br><span class="small">Motivo: <?= esc($remesa->motivo_anulacion) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Datos generales -->
        <div class="row mb-4">
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Fecha</small>
                <strong><?= date('d/m/Y', strtotime($remesa->fecha)) ?></strong>
            </div>
            <div class="col-md-4 mb-2">
                <small class="text-muted d-block">Descripción</small>
                <strong><?= esc($remesa->descripcion) ?></strong>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Tipo de partida</small>
                <strong><?= esc($remesa->tipo_partida_nombre ?? '—') ?></strong>
            </div>
            <div class="col-md-2 mb-2">
                <small class="text-muted d-block">Total remesado</small>
                <strong class="<?= $anulado ? 'text-muted' : 'text-primary' ?>" style="font-size:1.15rem;">
                    <?= $anulado ? '<s>' : '' ?>
                    $<?= number_format($remesa->total, 2) ?>
                    <?= $anulado ? '</s>' : '' ?>
                </strong>
            </div>
            <?php if ($remesa->observaciones): ?>
                <div class="col-md-12 mb-2">
                    <small class="text-muted d-block">Observaciones</small>
                    <span><?= nl2br(esc($remesa->observaciones)) ?></span>
                </div>
            <?php endif; ?>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Creada por</small>
                <span><?= esc($remesa->usuario_nombre ?? '—') ?></span>
            </div>
        </div>

        <!-- Asientos -->
        <h6 class="font-weight-bold mb-2">
            <i class="fa-solid fa-list-check text-primary mr-1"></i>Asientos contables incluidos
        </h6>

        <?php if (empty($detalles)): ?>
            <div class="text-muted small">Sin asientos registrados.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>N° Asiento</th>
                            <th>Fecha</th>
                            <th>Período</th>
                            <th>Tipo Partida</th>
                            <th>Descripción</th>
                            <th>Referencia</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalDetalle = 0; ?>
                        <?php foreach ($detalles as $i => $d): ?>
                            <?php $totalDetalle += (float)$d->monto; ?>
                            <tr>
                                <td class="text-center text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= base_url('contabilidad/asientos/' . $d->asiento_id) ?>"
                                       class="font-weight-bold text-dark" target="_blank">
                                        #<?= $d->numero_asiento ?>
                                    </a>
                                </td>
                                <td class="small"><?= $d->fecha ? date('d/m/Y', strtotime($d->fecha)) : '—' ?></td>
                                <td class="small text-muted">
                                    <?= (!empty($d->anio) && !empty($d->mes))
                                        ? $d->anio . '-' . str_pad($d->mes, 2, '0', STR_PAD_LEFT)
                                        : '—' ?>
                                </td>
                                <td>
                                    <?php if ($d->tipo_partida_nombre): ?>
                                        <span class="badge badge-light border"><?= esc($d->tipo_partida_nombre) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= esc($d->descripcion) ?></td>
                                <td class="small text-muted"><?= esc($d->referencia ?? '—') ?></td>
                                <td class="text-right font-weight-bold text-primary">
                                    $<?= number_format($d->monto, 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light font-weight-bold">
                            <td colspan="7" class="text-right">TOTAL:</td>
                            <td class="text-right text-primary">$<?= number_format($totalDetalle, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal anulación -->
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-ban text-danger mr-2"></i>Anular remesa
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <strong>Al anular esta remesa:</strong>
                    <ul class="mb-0 mt-1 pl-3 small">
                        <li>La remesa <strong><?= esc($remesa->numero_remesa) ?></strong> quedará marcada como anulada</li>
                        <li>Los <?= count($detalles) ?> asiento(s) incluidos quedarán disponibles para ser remesados nuevamente</li>
                        <li>El total de <strong>$<?= number_format($remesa->total, 2) ?></strong> quedará sin efecto</li>
                    </ul>
                </div>
                <label class="font-weight-bold small">Motivo de anulación <span class="text-danger">*</span></label>
                <textarea id="motivoAnulacion" class="form-control" rows="3"
                          placeholder="Describe el motivo por el cual se anula esta remesa..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm" onclick="confirmarAnulacion()">
                    <i class="fa-solid fa-ban mr-1"></i>Confirmar anulación
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModalAnular() {
    document.getElementById('motivoAnulacion').value = '';
    $('#modalAnular').modal('show');
}

function confirmarAnulacion() {
    const motivo = document.getElementById('motivoAnulacion').value.trim();
    if (!motivo) {
        Swal.fire('Requerido', 'Ingresa el motivo de anulación.', 'warning');
        return;
    }

    const form = new FormData();
    form.append('motivo', motivo);

    fetch('<?= base_url('contabilidad/remesas/anular/' . $remesa->id) ?>', { method: 'POST', body: form })
        .then(r => r.json())
        .then(d => {
            $('#modalAnular').modal('hide');
            if (d.success) {
                Swal.fire('Anulada', d.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', d.message, 'error');
            }
        });
}
</script>

<?= $this->endSection() ?>
