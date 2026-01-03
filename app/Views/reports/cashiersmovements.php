<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">

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
                <h4 class="header-title mb-0 col-md-8">Reporte de Movimientos de Cajas</h4>

                <?php if (!empty($cashier_movements)): ?>
                    <div class="ml-auto">
                        <a href="<?= base_url('reports/cashiersmovements/excel?' . http_build_query($filters)) ?>"
                            class="btn btn-success btn-sm">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </a>

                        <a href="<?= base_url('reports/cashiersmovements/pdf?' . http_build_query($filters)) ?>"
                            class="btn btn-danger btn-sm">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>
                    </div>
                <?php endif; ?>

                <a href="<?= base_url('reports') ?>" class="btn btn-primary btn-sm ml-2">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card-body">

                <!-- FILTROS -->
                <form method="get" action="<?= current_url() ?>">
                    <div class="row">
                        <div class="col-md-2">
                            <label>Fecha desde</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                        </div>

                        <div class="col-md-2">
                            <label>Fecha hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control"
                                value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Tipo de movimiento</label>
                            <select name="tipo" class="form-control select2">
                                <option value="">Todos</option>
                                <option value="in" <?= ($filters['tipo'] ?? '') === 'in' ? 'selected' : '' ?>>
                                    Entrada
                                </option>
                                <option value="out" <?= ($filters['tipo'] ?? '') === 'out' ? 'selected' : '' ?>>
                                    Salida
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa-solid fa-search"></i> Generar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- PER PAGE -->
                <form method="get" action="<?= current_url() ?>" class="form-inline my-3">
                    <?php foreach ($filters as $k => $v): ?>
                        <?php if ($v !== ''): ?>
                            <input type="hidden" name="<?= esc($k) ?>" value="<?= esc($v) ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <label class="mr-2">Mostrar</label>
                    <select name="perPage" class="form-control form-control-sm"
                        onchange="this.form.submit()">
                        <?php foreach ([10, 25, 50, 100] as $n): ?>
                            <option value="<?= $n ?>" <?= ($perPage ?? 25) == $n ? 'selected' : '' ?>>
                                <?= $n ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="ml-2">registros</span>
                </form>

                <hr>

                <?php if (!empty($cashier_movements)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Caja</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cashier_movements as $r): ?>
                                    <tr>
                                        <td><?= $r['id'] ?></td>
                                        <td><?= $r['cashier_id'] ?></td>
                                        <td>
                                            <span class="badge badge-<?= $r['type'] === 'in' ? 'success' : 'danger' ?>">
                                                <?= $r['type'] === 'in' ? 'Entrada' : 'Salida' ?>
                                            </span>
                                        </td>
                                        <td><?= esc($r['concept']) ?></td>
                                        <td>
                                            <?= esc($r['reference_type'] ?? '-') ?>
                                            <?= $r['reference_id'] ? '#'.$r['reference_id'] : '' ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                                        <td class="text-right">$<?= number_format($r['amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="6" class="text-right">TOTAL</td>
                                    <td class="text-right">$<?= number_format($total, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="d-flex justify-content-center mt-3">
                            <?= $pager->links('cashier_movements', 'bitacora_pagination') ?>
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
