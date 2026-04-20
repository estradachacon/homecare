<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0"><i class="fa-solid fa-history me-2"></i>Transacciones Históricas</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i></button></div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Cuenta</label>
                        <select name="cuenta_id" class="form-select" id="cuentaSelect">
                            <?php if (isset($cuenta) && $cuenta): ?>
                            <option value="<?= $cuenta->id ?>" selected><?= esc($cuenta->codigo . ' - ' . $cuenta->nombre) ?></option>
                            <?php else: ?>
                            <option value="">Buscar cuenta...</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">Año</label>
                        <select name="anio" class="form-select form-select-sm">
                            <?php foreach ($anios as $a): ?>
                            <option value="<?= $a ?>" <?= $a == $anioSel ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Mes (opcional)</label>
                        <select name="mes" class="form-select form-select-sm">
                            <option value="">Todos los meses</option>
                            <?php
                            $mn=[1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                                 7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                            foreach ($mn as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $k == $mesSel ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">Ver</button>
                    </div>
                </form>

                <?php if (!empty($filas)): ?>
                <div class="mb-2">
                    <strong><?= esc($cuenta->codigo . ' — ' . $cuenta->nombre) ?></strong>
                    <span class="text-muted ms-2 small">Año: <?= $anioSel ?><?= $mesSel ? ' / ' . $mn[$mesSel] : '' ?></span>
                </div>
                <table class="table table-bordered table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">N° Asiento</th>
                            <th>Descripción</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-end">Debe</th>
                            <th class="text-end">Haber</th>
                            <th class="text-end">Saldo Acum.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $tD=$tH=0; foreach ($filas as $f): $tD+=$f->debe; $tH+=$f->haber; ?>
                        <tr>
                            <td class="text-center small"><?= date('d/m/Y', strtotime($f->fecha)) ?></td>
                            <td class="text-center small">AST-<?= str_pad($f->numero_asiento??0, 5,'0',STR_PAD_LEFT) ?></td>
                            <td class="small"><?= esc($f->descripcion) ?></td>
                            <td class="text-center"><span class="badge bg-secondary" style="font-size:0.65rem"><?= $f->tipo_asiento ?></span></td>
                            <td class="text-end"><?= $f->debe > 0 ? '$ '.number_format($f->debe,2) : '-' ?></td>
                            <td class="text-end"><?= $f->haber > 0 ? '$ '.number_format($f->haber,2) : '-' ?></td>
                            <td class="text-end fw-bold <?= $f->saldo_acumulado < 0 ? 'text-danger' : '' ?>">
                                $ <?= number_format($f->saldo_acumulado, 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="4" class="text-end">TOTALES:</td>
                            <td class="text-end">$ <?= number_format($tD,2) ?></td>
                            <td class="text-end">$ <?= number_format($tH,2) ?></td>
                            <td class="text-end">Neto: $ <?= number_format($tD-$tH,2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-muted small"><?= count($filas) ?> transacciones</div>
                <?php elseif ($cuentaId): ?>
                    <div class="alert alert-info">No hay transacciones históricas para los filtros seleccionados</div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">Selecciona una cuenta para ver su historial</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$('#cuentaSelect').select2({
    width:'100%', placeholder:'Buscar cuenta...', minimumInputLength:2, language:'es',
    ajax: {
        url: '<?= base_url('contabilidad/plan-cuentas/search') ?>',
        dataType:'json', delay:200,
        data: p => ({q: p.term}),
        processResults: d => d,
        cache: true
    }
});
</script>

<?= $this->endSection() ?>
