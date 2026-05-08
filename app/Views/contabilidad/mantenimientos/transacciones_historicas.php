<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
@media print {
    .card-header .btn, form, .no-print,
    .sb-nav-fixed, #layoutSidenav_nav, .sb-topnav { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-body { padding: 0 !important; }
    a { color: inherit !important; text-decoration: none !important; }
}
.fila-debe   { background: #f0f7ff; }
.fila-haber  { background: #f0fff4; }
.fila-mixta  { background: #fff; }
.saldo-neg   { color: #dc2626; }
.saldo-pos   { color: #16a34a; }
.badge-tipo  { font-size: 0.62rem; letter-spacing: .5px; }
    .select2-container .select2-selection--single {
        height: 38px !important;
        /* altura estándar Bootstrap */
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        /* centra texto */
        padding-left: .75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    /* focus igual que form-control */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
    }
    /* Forzar altura uniforme de 38px para los controles del filtro */
.form-select, 
.btn-consultar,
.select2-container .select2-selection--single {
    height: 38px !important;
    line-height: 1 !important;
}

/* Ajuste específico para el botón de limpiar si lo necesitas alineado */
.btn-light.btn-sm.w-100 {
    height: 38px !important;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php
$mesesN = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
           7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
$tipoBadge = [
    'DIARIO'   => 'primary',
    'AJUSTE'   => 'info',
    'CIERRE'   => 'dark',
    'APERTURA' => 'secondary',
    'VENTA'    => 'success',
];
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between py-2">
                <h5 class="mb-0">
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                    Transacciones Históricas
                    <?php if ($cuenta): ?>
                        <span class="text-muted fw-normal fs-6 ms-2">
                            — <code><?= esc($cuenta->codigo) ?></code> <?= esc($cuenta->nombre) ?>
                        </span>
                    <?php endif; ?>
                </h5>
                <div class="d-flex gap-2 no-print">
                    <?php if (!empty($filas)): ?>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Imprimir
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">

                <!-- Filtros -->
                <form method="get" class="row g-2 mb-4 no-print align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold mb-1">Cuenta contable</label>
                        <select name="cuenta_id" class="form-select form-select-sm" id="cuentaSelect">
                            <?php if ($cuenta): ?>
                            <option value="<?= $cuenta->id ?>" selected>
                                <?= esc($cuenta->codigo . ' - ' . $cuenta->nombre) ?>
                            </option>
                            <?php else: ?>
                            <option value="">Escriba para buscar...</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Año</label>
                        <select name="anio" class="form-select">
                            <?php foreach ($anios as $a): ?>
                            <option value="<?= $a ?>" <?= (int)$a === $anioSel ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold mb-1">Mes</label>
                        <select name="mes" class="form-select">
                            <option value="">Todos los meses</option>
                            <?php foreach ($mesesN as $k => $v): ?>
                            <option value="<?= $k ?>" <?= (string)$k === (string)$mesSel ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            <i class="fa-solid fa-magnifying-glass"></i> Consultar
                        </button>
                    </div>
                    <?php if ($cuentaId): ?>
                    <div class="col-md-1">
                        <a href="<?= base_url('contabilidad/mantenimientos/transacciones-hist') ?>"
                           class="btn btn-light btn-sm w-100" title="Limpiar">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </form>

                <?php if (!empty($filas)): ?>

                    <?php
                    $tDebe = $tHaber = 0;
                    foreach ($filas as $f) { $tDebe += $f->debe; $tHaber += $f->haber; }
                    $neto = $tDebe - $tHaber;
                    ?>

                    <!-- Resumen -->
                    <div class="row g-3 mb-3 no-print">
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center bg-light">
                                <div class="text-muted small">Movimientos</div>
                                <div class="fw-bold fs-5"><?= count($filas) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center" style="background:#f0f7ff">
                                <div class="text-muted small">Total Debe</div>
                                <div class="fw-bold fs-5 text-primary">$ <?= number_format($tDebe, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center" style="background:#f0fff4">
                                <div class="text-muted small">Total Haber</div>
                                <div class="fw-bold fs-5 text-success">$ <?= number_format($tHaber, 2) ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center <?= $neto < 0 ? 'bg-danger bg-opacity-10' : 'bg-light' ?>">
                                <div class="text-muted small">Saldo Neto</div>
                                <div class="fw-bold fs-5 <?= $neto < 0 ? 'text-danger' : 'text-dark' ?>">
                                    $ <?= number_format(abs($neto), 2) ?>
                                    <small class="fs-6"><?= $neto >= 0 ? 'D' : 'H' ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0" style="font-size:0.82rem">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width:85px">Fecha</th>
                                    <th class="text-center" style="width:95px">Asiento</th>
                                    <th>Descripción</th>
                                    <th class="text-center" style="width:75px">Tipo</th>
                                    <th class="text-end" style="width:110px">Debe</th>
                                    <th class="text-end" style="width:110px">Haber</th>
                                    <th class="text-end" style="width:115px">Saldo acum.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filas as $f):
                                    $esDebe  = $f->debe  > 0 && $f->haber == 0;
                                    $esHaber = $f->haber > 0 && $f->debe  == 0;
                                    $rowCls  = $esDebe ? 'fila-debe' : ($esHaber ? 'fila-haber' : 'fila-mixta');
                                    $tipo    = $f->tipo_asiento ?? '';
                                    $badgeColor = $tipoBadge[$tipo] ?? 'secondary';
                                    $numAsiento = $f->numero_asiento ?? 0;
                                ?>
                                <tr class="<?= $rowCls ?>">
                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($f->fecha)) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($f->asiento_id): ?>
                                        <a href="<?= base_url('contabilidad/asientos/' . $f->asiento_id) ?>"
                                           class="text-decoration-none fw-semibold" title="Ver asiento">
                                            AST-<?= str_pad($numAsiento, 5, '0', STR_PAD_LEFT) ?>
                                        </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($f->descripcion) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $badgeColor ?> badge-tipo">
                                            <?= esc($tipo) ?>
                                        </span>
                                    </td>
                                    <td class="text-end <?= $esDebe ? 'text-primary fw-semibold' : 'text-muted' ?>">
                                        <?= $f->debe > 0 ? '$ ' . number_format($f->debe, 2) : '—' ?>
                                    </td>
                                    <td class="text-end <?= $esHaber ? 'text-success fw-semibold' : 'text-muted' ?>">
                                        <?= $f->haber > 0 ? '$ ' . number_format($f->haber, 2) : '—' ?>
                                    </td>
                                    <td class="text-end fw-semibold <?= $f->saldo_acumulado < 0 ? 'saldo-neg' : 'saldo-pos' ?>">
                                        $ <?= number_format(abs($f->saldo_acumulado), 2) ?>
                                        <small class="fw-normal"><?= $f->saldo_acumulado >= 0 ? 'D' : 'H' ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light fw-bold" style="font-size:0.84rem">
                                    <td colspan="4" class="text-end">TOTALES</td>
                                    <td class="text-end text-primary">$ <?= number_format($tDebe, 2) ?></td>
                                    <td class="text-end text-success">$ <?= number_format($tHaber, 2) ?></td>
                                    <td class="text-end <?= $neto < 0 ? 'saldo-neg' : '' ?>">
                                        $ <?= number_format(abs($neto), 2) ?>
                                        <small class="fw-normal"><?= $neto >= 0 ? 'D' : 'H' ?></small>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-muted small mt-2 no-print">
                        <?= count($filas) ?> transaccion<?= count($filas) != 1 ? 'es' : '' ?>
                        — <?= esc($cuenta->codigo . ' ' . $cuenta->nombre) ?>
                        — <?= $anioSel ?><?= $mesSel ? ' / ' . $mesesN[(int)$mesSel] : '' ?>
                    </div>

                <?php elseif ($cuentaId): ?>

                    <div class="alert alert-info d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-info fa-lg"></i>
                        <div>
                            No hay transacciones históricas para
                            <strong><?= $cuenta ? esc($cuenta->codigo . ' ' . $cuenta->nombre) : 'esta cuenta' ?></strong>
                            en <?= $anioSel ?><?= $mesSel ? ' / ' . $mesesN[(int)$mesSel] : '' ?>.
                            <br><small class="text-muted">Las transacciones se registran al aprobar asientos contables.</small>
                        </div>
                    </div>

                <?php else: ?>

                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 opacity-25"></i>
                        <p class="mb-1 fw-semibold">Selecciona una cuenta para ver su historial de movimientos</p>
                        <small>Las transacciones se registran automáticamente al aprobar asientos contables.</small>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    $('#cuentaSelect').select2({
        width: '100%',
        placeholder: 'Escriba para buscar una cuenta...',
        minimumInputLength: 1,
        language: 'es',
        ajax: {
            url: '<?= base_url('contabilidad/plan-cuentas/search') ?>',
            dataType: 'json',
            delay: 200,
            data: p => ({ q: p.term ?? '' }),
            processResults: d => d,
            cache: true
        }
    });
});
</script>

<?= $this->endSection() ?>
