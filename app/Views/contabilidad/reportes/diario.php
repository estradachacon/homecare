<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
@media print {
    .card-header .btn, form, .sb-nav-fixed, #layoutSidenav_nav, .sb-topnav { display:none!important; }
    .card { border:none!important; box-shadow:none!important; }
    .card-body { padding:0!important; }
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0"><i class="fa-solid fa-book me-2"></i>Libro Diario</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button></div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Período</label>
                        <select name="periodo_id" class="form-select form-select-sm">
                            <option value="">Por rango de fechas</option>
                            <?php
                            $mn=[1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
                            foreach ($periodos as $p):
                                $sel = $p->id == $periodoId ? 'selected' : '';
                            ?>
                            <option value="<?= $p->id ?>" <?= $sel ?>><?= $mn[$p->mes] ?>/<?= $p->anio ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Fecha desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fechaDesde ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Fecha hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fechaHasta ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100 mb-3">Generar</button>
                    </div>
                </form>

                <?php if (!empty($asientos)): ?>
                <?php $grandDebe = $grandHaber = 0; foreach ($asientos as $a): $grandDebe += $a->total_debe; $grandHaber += $a->total_haber; ?>
                <div class="mb-4 border rounded">
                    <div class="p-2 bg-light d-flex justify-content-between border-bottom">
                        <strong>AST-<?= str_pad($a->numero_asiento,5,'0',STR_PAD_LEFT) ?></strong>
                        <span class="text-muted"><?= date('d/m/Y', strtotime($a->fecha)) ?> — <?= esc($a->descripcion) ?></span>
                        <span class="badge bg-secondary text-white"><?= $a->tipo ?></span>
                    </div>
                    <table class="table table-sm mb-0">
                        <thead class="table-light" style="font-size:0.78rem">
                            <tr><th>Código</th><th>Cuenta</th><th>Descripción</th><th class="text-end">Debe</th><th class="text-end">Haber</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($a->lineas as $l): ?>
                            <tr style="font-size:0.82rem">
                                <td><code><?= esc($l->codigo) ?></code></td>
                                <td><?= esc($l->cuenta_nombre) ?></td>
                                <td class="text-muted"><?= esc($l->descripcion) ?></td>
                                <td class="text-end"><?= $l->debe > 0 ? '$ '.number_format($l->debe,2) : '' ?></td>
                                <td class="text-end"><?= $l->haber > 0 ? '$ '.number_format($l->haber,2) : '' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold" style="font-size:0.82rem">
                                <td colspan="3" class="text-end">Sub-total:</td>
                                <td class="text-end">$ <?= number_format($a->total_debe,2) ?></td>
                                <td class="text-end">$ <?= number_format($a->total_haber,2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endforeach; ?>
                <div class="alert alert-light border fw-bold d-flex justify-content-between">
                    <span>GRAN TOTAL (<?= count($asientos) ?> asientos)</span>
                    <span>Debe: $ <?= number_format($grandDebe,2) ?> | Haber: $ <?= number_format($grandHaber,2) ?></span>
                </div>
                <?php elseif ($periodoId || ($fechaDesde && $fechaHasta)): ?>
                    <div class="alert alert-info">No hay asientos aprobados para el filtro seleccionado</div>
                <?php else: ?>
                    <div class="text-muted text-center py-5">
                        <i class="fa-solid fa-book fa-3x mb-3 text-muted"></i>
                        <p>Selecciona un período o rango de fechas para generar el libro diario</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
