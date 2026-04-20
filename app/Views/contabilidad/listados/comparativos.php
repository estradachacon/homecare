<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0"><i class="fa-solid fa-chart-bar me-2"></i>Comparativo de Cuentas por Año</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i></button></div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Año 1</label>
                        <select name="anio1" class="form-select form-select-sm">
                            <?php foreach ($anios as $a): ?>
                            <option value="<?= $a ?>" <?= $a == $anio1 ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Año 2</label>
                        <select name="anio2" class="form-select form-select-sm">
                            <?php foreach ($anios as $a): ?>
                            <option value="<?= $a ?>" <?= $a == $anio2 ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm w-100">Comparar</button>
                    </div>
                </form>

                <?php if (!empty($filas)): ?>
                <?php
                $tipoBadge=['ACTIVO'=>'primary text-white','PASIVO'=>'danger text-white','CAPITAL'=>'warning','INGRESO'=>'success text-white','COSTO'=>'secondary text-white','GASTO'=>'dark text-white'];
                ?>
                <table class="table table-bordered table-sm table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Cuenta</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-end">Saldo <?= $anio1 ?></th>
                            <th class="text-end">Saldo <?= $anio2 ?></th>
                            <th class="text-end">Variación</th>
                            <th class="text-end">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filas as $f):
                            $var = (float)$f->saldo_anio2 - (float)$f->saldo_anio1;
                            $pct = $f->saldo_anio1 != 0 ? ($var / abs($f->saldo_anio1)) * 100 : 0;
                        ?>
                        <tr>
                            <td><code><?= esc($f->codigo) ?></code></td>
                            <td><?= esc($f->cuenta_nombre) ?></td>
                            <td class="text-center"><span class="badge bg-<?= $tipoBadge[$f->tipo]??'secondary' ?>" style="font-size:0.68rem"><?= $f->tipo ?></span></td>
                            <td class="text-end">$ <?= number_format($f->saldo_anio1, 2) ?></td>
                            <td class="text-end">$ <?= number_format($f->saldo_anio2, 2) ?></td>
                            <td class="text-end <?= $var >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= ($var >= 0 ? '+' : '') . '$ ' . number_format($var, 2) ?>
                            </td>
                            <td class="text-end <?= $pct >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= ($pct >= 0 ? '+' : '') . number_format($pct, 1) ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="text-muted text-center py-4">
                        <?= empty($anios) ? 'No hay datos históricos disponibles' : 'Selecciona los años a comparar' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
