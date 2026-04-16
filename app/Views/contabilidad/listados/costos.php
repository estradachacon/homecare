<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0"><i class="fa-solid fa-boxes-packing me-2"></i>Listado de Costos</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i></button></div>
            </div>
            <div class="card-body">
                <form method="get" action="" class="row g-2 mb-3">
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
                <table class="table table-bordered table-sm table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Cuenta</th>
                            <th class="text-end">Debe</th>
                            <th class="text-end">Haber</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalDebe=$totalHaber=$totalSaldo=0; foreach ($filas as $f): $totalDebe+=$f->total_debe; $totalHaber+=$f->total_haber; $totalSaldo+=$f->saldo_final; ?>
                        <tr>
                            <td><code><?= esc($f->codigo) ?></code></td>
                            <td><?= esc($f->nombre) ?></td>
                            <td class="text-end">$ <?= number_format($f->total_debe, 2) ?></td>
                            <td class="text-end">$ <?= number_format($f->total_haber, 2) ?></td>
                            <td class="text-end fw-bold">$ <?= number_format($f->saldo_final, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="2">TOTALES</td>
                            <td class="text-end">$ <?= number_format($totalDebe,2) ?></td>
                            <td class="text-end">$ <?= number_format($totalHaber,2) ?></td>
                            <td class="text-end text-danger">$ <?= number_format($totalSaldo,2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php elseif ($periodoId): ?>
                    <div class="alert alert-info">No hay costos registrados en este período</div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">Selecciona un período para ver los costos</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
