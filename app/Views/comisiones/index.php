<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card shadow-sm">

            <div class="card-header d-flex">
                <h4 class="mb-0">Resumen de Comisiones</h4>

                <?php if (tienePermiso('generar_comisiones')): ?>
                    <a href="<?= base_url('comisiones/generar') ?>"
                        class="btn btn-primary btn-sm ml-auto">
                        Generar Comisión
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">

                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Vendedor</th>
                                <th>Rango</th>
                                <th>Total Ventas</th>
                                <th>Total Comisión</th>
                                <th>% Promedio</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (empty($comisiones)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No hay comisiones registradas
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($comisiones as $c): ?>
                                <tr>

                                    <td><?= $c->id ?></td>

                                    <td><?= esc($c->vendedor_nombre ?? 'N/A') ?></td>

                                    <td>
                                        <?= $c->fecha_inicio ?> <br>
                                        <small>al</small><br>
                                        <?= $c->fecha_fin ?>
                                    </td>

                                    <td>$<?= number_format($c->total_ventas, 2) ?></td>

                                    <td class="text-success">
                                        $<?= number_format($c->total_comision, 2) ?>
                                    </td>

                                    <td><?= number_format($c->porcentaje_promedio, 2) ?>%</td>

                                    <td>
                                        <?php if ($c->estado === 'pagado'): ?>
                                            <span class="badge badge-success">Pagado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pendiente</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="<?= base_url('comisiones/ver/' . $c->id) ?>"
                                            class="btn btn-sm btn-info">
                                            Ver
                                        </a>
                                    </td>

                                </tr>
                            <?php endforeach; ?>

                        </tbody>

                    </table>
                </div>

            </div>

        </div>

    </div>
</div>

<?= $this->endSection() ?>