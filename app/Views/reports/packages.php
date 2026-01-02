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
                <h4 class="header-title mb-0 col-md-8">Reporte de Paquetes</h4>

                <?php if (!empty($packages)): ?>
                    <div class="ml-auto">
                        <a href="<?= base_url('reports/packages/excel?' . http_build_query($filters)) ?>"
                            class="btn btn-success btn-sm">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </a>

                        <a href="<?= base_url('reports/packages/pdf?' . http_build_query($filters)) ?>"
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
                                <label>Vendedor</label>
                                <select name="vendedor_id" class="form-control select2">
                                    <option value="">Todos</option>
                                    <?php foreach ($sellers as $seller): ?>
                                        <option value="<?= $seller->id ?>"
                                            <?= (!empty($filters['vendedor_id']) && $filters['vendedor_id'] == $seller->id) ? 'selected' : '' ?>>
                                            <?= esc($seller->seller) ?>
                                        </option>
                                    <?php endforeach; ?>
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
                <?php if (!empty($packages)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Servicio</th>
                                    <th>Fecha ingreso</th>
                                    <th>Estatus</th>
                                    <th class="text-right">Flete</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packages as $i => $pkg): ?>
                                    <tr>
                                        <td><?= $pkg['id'] ?></td>
                                        <td><?= esc($pkg['cliente']) ?></td>
                                        <td><?= esc($pkg['vendedor']) ?></td>
                                        <td><?= esc($pkg['tipo_servicio']) ?></td>
                                        <td><?= esc($pkg['fecha_ingreso']) ?></td>
                                        <td>
                                            <?php
                                            $badge = match ($pkg['estatus']) {
                                                'pendiente' => 'warning',
                                                'entregado' => 'success',
                                                'Cancelado' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge badge-<?= $badge ?>">
                                                <?= esc($pkg['estatus']) ?>
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            $<?= number_format($pkg['flete_total'], 2) ?>
                                        </td>
                                        <td class="text-right">
                                            $<?= number_format($pkg['monto'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="6" class="text-right">TOTALES</td>
                                    <td class="text-right">
                                        $<?= number_format(array_sum(array_column($packages, 'flete_total')), 2) ?>
                                    </td>
                                    <td class="text-right">
                                        $<?= number_format(array_sum(array_column($packages, 'monto')), 2) ?>
                                    </td>
                                </tr>
                            </tfoot>

                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            <?= $pager->links('packages', 'bitacora_pagination') ?>
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