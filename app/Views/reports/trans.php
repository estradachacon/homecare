<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">

    <!-- ALERTA -->
    <div class="alert alert-success alert-dismissible d-none" id="main_alert" role="alert">
        <button type="button" class="close" onclick="$('#main_alert').addClass('d-none')">
            <span aria-hidden="true"><i class="ti-close"></i></span>
        </button>
        <span class="msg"></span>
    </div>

    <div class="col-md-12">
        <div class="card">

            <!-- HEADER -->
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0 col-md-8">Reporte de Movimientos financieros</h4>

                <?php if (!empty($trans)): ?>
                    <div class="ml-auto">
                        <a href="<?= base_url('reports/trans/excel?' . http_build_query($filters)) ?>"
                            class="btn btn-success btn-sm">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </a>

                        <a href="<?= base_url('reports/trans/pdf?' . http_build_query($filters)) ?>"
                            class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>
                    </div>
                <?php endif; ?>

                <a href="<?= base_url('reports') ?>" class="btn btn-primary btn-sm ml-auto">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>

            </div>

            <!-- BODY -->
            <div class="card-body">

                <!-- FILTROS -->
                <form method="get" action="<?= current_url() ?>">

                    <div class="row">

                        <!-- FECHA DESDE -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha desde</label>
                                <input type="date"
                                    name="fecha_desde"
                                    class="form-control"
                                    value="<?= old('fecha_desde', $filters['fecha_desde'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- FECHA HASTA -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Fecha hasta</label>
                                <input type="date"
                                    name="fecha_hasta"
                                    class="form-control"
                                    value="<?= old('fecha_hasta', $filters['fecha_hasta'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- VENDEDOR -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo de movimiento</label>
                                <select name="tipo" class="form-control select2">
                                    <option value="">Todos</option>
                                    <option value="entrada" <?= ($filters['tipo'] ?? '') === 'entrada' ? 'selected' : '' ?>>
                                        Entrada
                                    </option>
                                    <option value="salida" <?= ($filters['tipo'] ?? '') === 'salida' ? 'selected' : '' ?>>
                                        Salida
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- BOTÓN -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa-solid fa-search"></i> Generar
                            </button>
                        </div>

                    </div>
                </form>
                <form method="get" action="<?= current_url() ?>" class="form-inline mb-2">
                    <?php foreach ($filters as $k => $v): ?>
                        <?php if ($v !== ''): ?>
                            <input type="hidden" name="<?= esc($k) ?>" value="<?= esc($v) ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <label class="mr-2">Mostrar</label>

                    <select name="perPage"
                        class="form-control form-control-sm"
                        onchange="this.form.submit()">

                        <?php foreach ([10, 25, 50, 100] as $n): ?>
                            <option value="<?= $n ?>"
                                <?= ($perPage ?? 25) == $n ? 'selected' : '' ?>>
                                <?= $n ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <span class="ml-2">registros</span>
                </form>
                <hr>

                <!-- RESULTADOS -->
                <?php if (!empty($trans)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Cuenta</th>
                                    <th>Tipo</th>
                                    <th>Origen</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                    <th class="text-right">Entradas</th>
                                    <th class="text-right">Salidas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trans as $t): ?>
                                    <tr>
                                        <td><?= $t->id ?></td>
                                        <td><?= $t->account_id ?></td>
                                        <td>
                                            <span class="badge badge-<?= $t->tipo === 'entrada' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($t->tipo) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($t->origen ?? '-') ?></td>
                                        <td><?= esc($t->referencia ?? '-') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($t->created_at)) ?></td>

                                        <!-- ENTRADAS -->
                                        <td class="text-right">
                                            <?= $t->tipo === 'entrada'
                                                ? '$' . number_format($t->monto, 2)
                                                : '-' ?>
                                        </td>

                                        <!-- SALIDAS -->
                                        <td class="text-right">
                                            <?= $t->tipo === 'salida'
                                                ? '$' . number_format($t->monto, 2)
                                                : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <?php
                                $totalEntradas = 0;
                                $totalSalidas  = 0;

                                foreach ($trans as $t) {
                                    if ($t->tipo === 'entrada') {
                                        $totalEntradas += $t->monto;
                                    } elseif ($t->tipo === 'salida') {
                                        $totalSalidas += $t->monto;
                                    }
                                }

                                $balance = $totalEntradas - $totalSalidas;
                                ?>
                                <tr class="font-weight-bold">
                                    <td colspan="6" class="text-right">TOTALES</td>

                                    <!-- TOTAL ENTRADAS -->
                                    <td class="text-right text-success">
                                        $<?= number_format($totalEntradas, 2) ?>
                                    </td>

                                    <!-- TOTAL SALIDAS -->
                                    <td class="text-right text-danger">
                                        $<?= number_format($totalSalidas, 2) ?>
                                    </td>
                                </tr>

                                <tr class="font-weight-bold">
                                    <td colspan="6" class="text-right">BALANCE</td>
                                    <td colspan="2" class="text-right">
                                        <span class="<?= $balance >= 0 ? 'text-success' : 'text-danger' ?>">
                                            $<?= number_format($balance, 2) ?>
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>

                        </table>

                        <div class="d-flex justify-content-center mt-3">
                            <?= $pager->links('trans', 'bitacora_pagination') ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">No hay resultados para los filtros seleccionados.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>