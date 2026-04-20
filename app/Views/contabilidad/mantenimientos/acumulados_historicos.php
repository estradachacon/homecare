<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Acumulados de Saldos — Años Anteriores</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i></button></div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Año</label>
                        <select name="anio" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Seleccionar año</option>
                            <?php foreach ($anios as $a): ?>
                            <option value="<?= $a ?>" <?= $a == $anioSel ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php if ($anioSel && !empty($filas)): ?>
                <?php
                $tipoBadge=['ACTIVO'=>'primary','PASIVO'=>'danger','CAPITAL'=>'warning','INGRESO'=>'success','COSTO'=>'secondary','GASTO'=>'dark'];
                $mn=[1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];

                // Agrupar por cuenta
                $porCuenta = [];
                foreach ($filas as $f) {
                    $porCuenta[$f->cuenta_id][$f->mes] = $f;
                    $porCuenta[$f->cuenta_id]['_meta'] = $f;
                }
                // Obtener meses disponibles
                $mesesDisp = [];
                foreach ($filas as $f) $mesesDisp[$f->mes] = true;
                ksort($mesesDisp);
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" style="font-size:0.8rem">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Cuenta</th>
                                <th>Tipo</th>
                                <?php foreach (array_keys($mesesDisp) as $m): ?>
                                <th class="text-end"><?= $mn[$m] ?></th>
                                <?php endforeach; ?>
                                <th class="text-end">Total Año</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($porCuenta as $cId => $mesesData):
                                $meta    = $mesesData['_meta'];
                                $totalAnio = 0;
                            ?>
                            <tr>
                                <td><code><?= esc($meta->codigo) ?></code></td>
                                <td><?= esc($meta->cuenta_nombre) ?></td>
                                <td><span class="badge bg-<?= $tipoBadge[$meta->tipo]??'secondary' ?>" style="font-size:0.65rem"><?= $meta->tipo ?></span></td>
                                <?php foreach (array_keys($mesesDisp) as $m):
                                    $sf = isset($mesesData[$m]) ? (float)$mesesData[$m]->saldo_final : null;
                                    $totalAnio += $sf ?? 0;
                                ?>
                                <td class="text-end <?= $sf !== null && $sf < 0 ? 'text-danger' : '' ?>">
                                    <?= $sf !== null ? '$ '.number_format($sf, 2) : '-' ?>
                                </td>
                                <?php endforeach; ?>
                                <td class="text-end fw-bold">$ <?= number_format($totalAnio, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php elseif ($anioSel): ?>
                    <div class="alert alert-info">No hay datos históricos para <?= $anioSel ?></div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">Selecciona un año para ver los acumulados históricos</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
