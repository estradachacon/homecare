<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
@media print {
    .card-header .btn, form { display:none!important; }
    .card { border:none!important; }
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0"><i class="fa-solid fa-table-list me-2"></i>Libro Auxiliar de Cuentas</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button></div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Período</label>
                        <select name="periodo_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Seleccionar período</option>
                            <?php
                            $mn=[1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                                 7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                            foreach ($periodos as $p):
                                $sel = $p->id == $periodoId ? 'selected' : '';
                            ?>
                            <option value="<?= $p->id ?>" <?= $sel ?>><?= $mn[$p->mes] ?> <?= $p->anio ?> — <?= $p->estado ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php if ($periodoId && !empty($filas)): ?>
                <?php
                $tipos = ['ACTIVO','PASIVO','CAPITAL','INGRESO','COSTO','GASTO'];
                $tipoBadge=['ACTIVO'=>'primary','PASIVO'=>'danger','CAPITAL'=>'warning','INGRESO'=>'success','COSTO'=>'secondary','GASTO'=>'dark'];
                $filasPorTipo = [];
                foreach ($filas as $f) $filasPorTipo[$f->tipo][] = $f;
                $grandD=$grandH=$grandS=0;
                foreach ($filas as $f) { $grandD+=$f->total_debe; $grandH+=$f->total_haber; $grandS+=$f->saldo_final; }
                ?>

                <?php foreach ($tipos as $tipo):
                    if (empty($filasPorTipo[$tipo])) continue;
                    $subD=$subH=$subS=0;
                    foreach ($filasPorTipo[$tipo] as $f) { $subD+=$f->total_debe; $subH+=$f->total_haber; $subS+=$f->saldo_final; }
                ?>
                <div class="mb-4">
                    <h6 class="fw-bold text-<?= $tipoBadge[$tipo]??'secondary' ?> mb-2">
                        <span class="badge bg-<?= $tipoBadge[$tipo]??'secondary' ?> me-1"><?= $tipo ?></span>
                    </h6>
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <thead class="table-light" style="font-size:0.8rem">
                            <tr>
                                <th>Código</th><th>Cuenta</th>
                                <th class="text-end">Saldo Inicial</th>
                                <th class="text-end">Debe</th>
                                <th class="text-end">Haber</th>
                                <th class="text-end">Saldo Final</th>
                            </tr>
                        </thead>
                        <tbody style="font-size:0.82rem">
                            <?php foreach ($filasPorTipo[$tipo] as $f): ?>
                            <tr>
                                <td><code><?= esc($f->codigo) ?></code></td>
                                <td><?= esc($f->cuenta_nombre) ?></td>
                                <td class="text-end">$ <?= number_format($f->saldo_inicial,2) ?></td>
                                <td class="text-end">$ <?= number_format($f->total_debe,2) ?></td>
                                <td class="text-end">$ <?= number_format($f->total_haber,2) ?></td>
                                <td class="text-end fw-bold <?= $f->saldo_final < 0 ? 'text-danger' : '' ?>">$ <?= number_format($f->saldo_final,2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold" style="font-size:0.82rem">
                                <td colspan="2" class="text-end">Subtotal <?= $tipo ?>:</td>
                                <td class="text-end"></td>
                                <td class="text-end">$ <?= number_format($subD,2) ?></td>
                                <td class="text-end">$ <?= number_format($subH,2) ?></td>
                                <td class="text-end">$ <?= number_format($subS,2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endforeach; ?>

                <div class="alert alert-light border fw-bold d-flex justify-content-between">
                    <span>GRAN TOTAL</span>
                    <span>Debe: $<?= number_format($grandD,2) ?> | Haber: $<?= number_format($grandH,2) ?> | Saldo Neto: $<?= number_format($grandS,2) ?></span>
                </div>
                <?php elseif ($periodoId): ?>
                    <div class="alert alert-info">No hay saldos para el período seleccionado</div>
                <?php else: ?>
                    <div class="text-muted text-center py-5">
                        <i class="fa-solid fa-table-list fa-3x mb-3"></i>
                        <p>Selecciona un período para ver el Auxiliar de Cuentas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
