<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
@media print {
    .card-header .btn, form { display:none!important; }
    .card { border:none!important; box-shadow:none!important; }
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0"><i class="fa-solid fa-book-bookmark me-2"></i>Libro Mayor</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button></div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Cuenta</label>
                        <select name="cuenta_id" class="form-select" id="cuentaSelect">
                            <?php if ($cuenta): ?>
                            <option value="<?= $cuenta->id ?>" selected><?= esc($cuenta->codigo . ' - ' . $cuenta->nombre) ?></option>
                            <?php else: ?>
                            <option value="">Buscar cuenta...</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">Generar</button>
                    </div>
                </form>

                <?php if ($cuenta && !empty($movimientos)): ?>
                <div class="mb-3">
                    <h5 class="fw-bold"><?= esc($cuenta->codigo . ' — ' . $cuenta->nombre) ?></h5>
                    <span class="badge bg-secondary"><?= $cuenta->tipo ?></span>
                    <span class="badge bg-info text-dark ms-1"><?= $cuenta->naturaleza ?></span>
                    <small class="text-muted ms-2"><?= date('d/m/Y', strtotime($fechaDesde)) ?> al <?= date('d/m/Y', strtotime($fechaHasta)) ?></small>
                </div>

                <table class="table table-bordered table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width:100px">Fecha</th>
                            <th class="text-center" style="width:80px">N° Asiento</th>
                            <th>Descripción</th>
                            <th class="text-end" style="width:120px">Debe</th>
                            <th class="text-end" style="width:120px">Haber</th>
                            <th class="text-end" style="width:130px">Saldo Acum.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $tD=$tH=0; foreach ($movimientos as $m): $tD+=$m->debe; $tH+=$m->haber; ?>
                        <tr>
                            <td class="text-center small"><?= date('d/m/Y', strtotime($m->fecha)) ?></td>
                            <td class="text-center"><small>AST-<?= str_pad($m->numero_asiento,5,'0',STR_PAD_LEFT) ?></small></td>
                            <td class="small"><?= esc($m->descripcion ?: $m->desc_asiento) ?></td>
                            <td class="text-end"><?= $m->debe > 0 ? '$ '.number_format($m->debe,2) : '-' ?></td>
                            <td class="text-end"><?= $m->haber > 0 ? '$ '.number_format($m->haber,2) : '-' ?></td>
                            <td class="text-end fw-bold <?= $m->saldo_acumulado < 0 ? 'text-danger' : '' ?>">
                                $ <?= number_format($m->saldo_acumulado, 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">TOTALES:</td>
                            <td class="text-end">$ <?= number_format($tD,2) ?></td>
                            <td class="text-end">$ <?= number_format($tH,2) ?></td>
                            <td class="text-end">Neto: $ <?= number_format($tD-$tH,2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php elseif ($cuentaId): ?>
                    <div class="alert alert-info">No hay movimientos para esta cuenta en el rango seleccionado</div>
                <?php else: ?>
                    <div class="text-muted text-center py-5">
                        <i class="fa-solid fa-book-bookmark fa-3x mb-3"></i>
                        <p>Selecciona una cuenta y rango de fechas para ver el Libro Mayor</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$('#cuentaSelect').select2({
    width: '100%',
    placeholder: 'Buscar cuenta...',
    minimumInputLength: 2,
    language: 'es',
    ajax: {
        url: '<?= base_url('contabilidad/plan-cuentas/search') ?>',
        dataType: 'json',
        delay: 200,
        data: p => ({ q: p.term }),
        processResults: d => d,
        cache: true
    }
});
</script>

<?= $this->endSection() ?>
