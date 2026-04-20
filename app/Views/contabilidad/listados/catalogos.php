<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0"><i class="fa-solid fa-book me-2"></i>Catálogo de Cuentas</h4>
                <div class="ms-auto"><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i></button></div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Filtrar por Tipo</label>
                        <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los tipos</option>
                            <?php foreach (['ACTIVO','PASIVO','CAPITAL','INGRESO','COSTO','GASTO'] as $t): ?>
                            <option value="<?= $t ?>" <?= $t == $tipo ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Nivel</label>
                        <select name="nivel" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <?php for($n=1;$n<=4;$n++): ?>
                            <option value="<?= $n ?>" <?= $n == $nivel ? 'selected' : '' ?>>Nivel <?= $n ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>

                <table class="table table-bordered table-sm table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Naturaleza</th>
                            <th class="text-center">Nivel</th>
                            <th class="text-center">Movimientos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tb=['ACTIVO'=>'primary text-white','PASIVO'=>'danger text-white','CAPITAL'=>'warning','INGRESO'=>'success text-white','COSTO'=>'secondary text-white','GASTO'=>'dark text-white'];
                        foreach ($cuentas as $c): ?>
                        <tr>
                            <td><code class="<?= $c->nivel<=2?'fw-bold':'' ?>"><?= esc($c->codigo) ?></code></td>
                            <td class="<?= $c->nivel<=2?'fw-bold':($c->nivel==3?'fw-semibold':'') ?>"><?= esc($c->nombre) ?></td>
                            <td class="text-center"><span class="badge bg-<?= $tb[$c->tipo]??'secondary' ?>" style="font-size:0.68rem"><?= $c->tipo ?></span></td>
                            <td class="text-center small"><?= $c->naturaleza ?></td>
                            <td class="text-center small"><?= $c->nivel ?></td>
                            <td class="text-center"><?= $c->acepta_movimientos ? '<i class="fa-solid fa-check text-success"></i>' : '<i class="fa-solid fa-minus text-muted"></i>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-muted small"><?= count($cuentas) ?> cuentas encontradas</div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
