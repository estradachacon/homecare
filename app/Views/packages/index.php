<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de Paquetes</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('packages/new') ?>"><i
                        class="fa-solid fa-plus"></i> Nuevo</a>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Tipo Servicio</th>
                            <th>Destino</th>
                            <th>Fecha Ingreso</th>
                            <th>Flete</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($packages)): ?>
                            <?php foreach ($packages as $pkg): ?>
                                <tr>
                                    <td><?= esc($pkg['id']) ?></td>
                                    <td><?= esc($pkg['vendedor']) ?></td>
                                    <td><?= esc($pkg['cliente']) ?></td>
                                    <td><?= esc($pkg['tipo_servicio']) ?></td>
                                    <td><?= esc($pkg['destino']) ?></td>
                                    <td><?= esc($pkg['fecha_ingreso']) ?></td>
                                    <td>$<?= number_format($pkg['flete_total'], 2) ?></td>
                                    <td><?= esc(ucfirst($pkg['estatus'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('packages/' . $pkg['id']) ?>" class="btn btn-sm btn-info">Ver
                                            Detalles</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No hay paquetes registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>