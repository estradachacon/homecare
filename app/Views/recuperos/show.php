<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$formaCobro = [
    'efectivo'      => ['label' => 'Efectivo',      'icon' => 'fa-money-bill-wave',   'color' => 'success'],
    'cheque'        => ['label' => 'Cheque',         'icon' => 'fa-money-check',       'color' => 'info'],
    'transferencia' => ['label' => 'Transferencia',  'icon' => 'fa-mobile-screen-button', 'color' => 'primary'],
    'deposito'      => ['label' => 'Depósito bancario', 'icon' => 'fa-building-columns', 'color' => 'warning'],
];
$fc      = $formaCobro[$recupero->forma_cobro] ?? ['label' => ucfirst($recupero->forma_cobro), 'icon' => 'fa-circle', 'color' => 'secondary'];
$anulado   = ($recupero->estado === 'ANULADO');
$aplicado  = ($recupero->estado === 'APLICADO');
$tipos     = ['01' => 'FAC', '03' => 'CCF', '05' => 'N.C.', '06' => 'N.D.'];
?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa-solid fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<div class="card">
    <!-- Cabecera -->
    <div class="card-header py-2">
        <div class="d-flex flex-wrap justify-content-between">

            <!-- Izquierda: número + badges -->
            <div class="d-flex align-items-center flex-wrap">
                <h5 class="mb-0 mr-2">
                    <i class="fa-solid fa-file-invoice-dollar text-success mr-1"></i>
                    <?= esc($recupero->numero_recupero) ?>
                </h5>
                <?php
                $estadoBadge = $anulado ? 'danger' : ($aplicado ? 'primary' : 'success');
                $estadoIcon  = $anulado ? 'fa-ban' : ($aplicado ? 'fa-link' : 'fa-circle-check');
                ?>
                <span class="badge badge-<?= $estadoBadge ?> mr-1 px-2 py-1">
                    <i class="fa-solid <?= $estadoIcon ?> mr-1"></i><?= $recupero->estado ?>
                </span>
                <span class="badge badge-<?= $fc['color'] ?> px-2 py-1">
                    <i class="fa-solid <?= $fc['icon'] ?> mr-1"></i><?= $fc['label'] ?>
                </span>
            </div>

            <!-- Derecha: acciones -->
            <div class="d-flex align-items-center">
                <?php if ($aplicado): ?>
                    <span class="badge badge-primary px-2 py-1 mr-2" style="font-size:.8rem;">
                        <i class="fa-solid fa-lock mr-1"></i>Vinculado al pago
                        <?php if ($recupero->pago_id): ?>
                            <a href="<?= base_url('payments/' . $recupero->pago_id) ?>"
                               class="text-white ml-1" title="Ver pago">#<?= $recupero->pago_id ?></a>
                        <?php endif; ?>
                    </span>
                <?php elseif (!$anulado && tienePermiso('anular_recupero')): ?>
                    <button class="btn btn-outline-danger btn-sm mr-2"
                            onclick="abrirModalAnular()">
                        <i class="fa-solid fa-ban mr-1"></i>Anular recupero
                    </button>
                <?php endif; ?>
                <a href="<?= base_url('recuperos') ?>" class="btn btn-light btn-sm border">
                    <i class="fa-solid fa-arrow-left mr-1"></i>Volver
                </a>
            </div>

        </div>
    </div>

    <div class="card-body">

        <!-- Alerta de anulación -->
        <?php if ($anulado): ?>
            <div class="alert alert-danger">
                <strong><i class="fa-solid fa-ban mr-1"></i>Recupero anulado</strong>
                <?php if ($recupero->fecha_anulacion): ?>
                    — <?= date('d/m/Y H:i', strtotime($recupero->fecha_anulacion)) ?>
                <?php endif; ?>
                <?php if ($recupero->motivo_anulacion): ?>
                    <br><span class="small">Motivo: <?= esc($recupero->motivo_anulacion) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Alerta de aplicado -->
        <?php if ($aplicado): ?>
            <div class="alert alert-primary">
                <strong><i class="fa-solid fa-link mr-1"></i>Recupero aplicado</strong>
                — Este recupero fue consumido y vinculado a un pago.
                <?php if ($recupero->pago_id): ?>
                    <a href="<?= base_url('payments/' . $recupero->pago_id) ?>" class="alert-link ml-1">
                        Ver pago #<?= $recupero->pago_id ?>
                    </a>
                <?php endif; ?>
                <br><small class="text-muted">No puede anularse mientras esté vinculado a un pago activo.</small>
            </div>
        <?php endif; ?>

        <!-- Datos generales -->
        <div class="row mb-4">
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block">Cliente</small>
                <strong><?= esc($recupero->cliente_nombre) ?></strong>
                <?php if ($recupero->numero_documento): ?>
                    <div class="small text-muted"><?= esc($recupero->numero_documento) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-2 mb-2">
                <small class="text-muted d-block">Fecha</small>
                <strong><?= date('d/m/Y', strtotime($recupero->fecha)) ?></strong>
            </div>
            <div class="col-md-2 mb-2">
                <small class="text-muted d-block">Forma de cobro</small>
                <strong><i class="fa-solid <?= $fc['icon'] ?> mr-1 text-<?= $fc['color'] ?>"></i><?= $fc['label'] ?></strong>
            </div>
            <?php if ($recupero->referencia): ?>
                <div class="col-md-2 mb-2">
                    <small class="text-muted d-block">Referencia</small>
                    <strong><?= esc($recupero->referencia) ?></strong>
                </div>
            <?php endif; ?>
            <div class="col-md-2 mb-2">
                <small class="text-muted d-block">Total remesado</small>
                <strong class="<?= $anulado ? 'text-muted' : 'text-success' ?>" style="font-size:1.1rem;">
                    <?= $anulado ? '<s>' : '' ?>
                    $<?= number_format($recupero->total, 2) ?>
                    <?= $anulado ? '</s>' : '' ?>
                </strong>
            </div>
            <?php if ($recupero->observaciones): ?>
                <div class="col-md-12 mb-2">
                    <small class="text-muted d-block">Observaciones</small>
                    <span><?= nl2br(esc($recupero->observaciones)) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Facturas del recupero -->
        <h6 class="font-weight-bold mb-2">
            <i class="fa-solid fa-file-lines text-warning mr-1"></i>Facturas incluidas en la remesa
        </h6>

        <div class="alert alert-info py-2 mb-3 small">
            <i class="fa-solid fa-circle-info mr-1"></i>
            El monto remesado <strong>no afecta el saldo</strong> de las facturas.
            El saldo solo se reduce al registrar el pago formal en contabilidad.
        </div>

        <?php if (empty($detalles)): ?>
            <div class="text-muted small">Sin facturas registradas.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-dark">#</th>
                            <th class="text-dark">Documento</th>
                            <th class="text-dark">Tipo</th>
                            <th class="text-dark">Fecha factura</th>
                            <th class="text-right text-dark">Total factura</th>
                            <th class="text-right text-dark">Saldo pendiente</th>
                            <th class="text-right text-dark">Monto remesado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalRemesado = 0; ?>
                        <?php foreach ($detalles as $i => $d): ?>
                            <?php $totalRemesado += (float)$d->monto_aplicado; ?>
                            <tr>
                                <td class="text-center text-muted"><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= base_url('facturas/' . $d->factura_id) ?>" class="font-weight-bold">
                                        <?= esc($d->numero_control) ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?= $tipos[$d->tipo_dte] ?? esc($d->tipo_dte) ?>
                                    </span>
                                </td>
                                <td class="small"><?= $d->fecha_emision ? date('d/m/Y', strtotime($d->fecha_emision)) : '—' ?></td>
                                <td class="text-right text-muted">$<?= number_format($d->total_pagar, 2) ?></td>
                                <td class="text-right">
                                    <?php $saldo = (float)($d->saldo_actual ?? 0); ?>
                                    <span class="<?= $saldo > 0 ? 'text-danger' : 'text-success' ?>">
                                        $<?= number_format($saldo, 2) ?>
                                    </span>
                                    <?php if ($saldo == 0): ?>
                                        <span class="badge badge-success ml-1" style="font-size:.6rem;">Pagada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right font-weight-bold text-primary">
                                    $<?= number_format($d->monto_aplicado, 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light font-weight-bold">
                            <td colspan="6" class="text-right">TOTAL REMESADO:</td>
                            <td class="text-right text-primary">$<?= number_format($totalRemesado, 2) ?></td>
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
                    <i class="fa-solid fa-ban text-danger mr-2"></i>Anular recupero
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <strong>Efecto de la anulación:</strong>
                    <ul class="mb-0 mt-1 pl-3 small">
                        <li>El recupero <strong><?= esc($recupero->numero_recupero) ?></strong> quedará como anulado</li>
                        <li>El saldo de cada factura incluida será restaurado</li>
                        <li>El monto de <strong>$<?= number_format($recupero->total, 2) ?></strong> no se devolverá automáticamente al cliente</li>
                    </ul>
                </div>
                <label class="font-weight-bold small">Motivo de anulación <span class="text-danger">*</span></label>
                <textarea id="motivoAnulacion" class="form-control" rows="3"
                          placeholder="Describe el motivo por el cual se anula este recupero..."></textarea>
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
        Swal.fire('Requerido', 'Ingresa el motivo de anulación', 'warning');
        return;
    }

    const form = new FormData();
    form.append('motivo', motivo);

    fetch('<?= base_url('recuperos/anular/' . $recupero->id) ?>', { method: 'POST', body: form })
        .then(r => r.json())
        .then(d => {
            $('#modalAnular').modal('hide');
            if (d.success) {
                Swal.fire('Anulado', d.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', d.message, 'error');
            }
        });
}
</script>

<?= $this->endSection() ?>
