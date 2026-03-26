<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0">Listado de Compras</h4>

                <div class="ml-auto d-flex">
                    <?php if (tienePermiso('ingresar_compras')): ?>
                        <a class="btn btn-primary btn-sm mr-2"
                            href="<?= base_url('purchases/new') ?>">
                            <i class="fa-solid fa-plus"></i> Nueva compra
                        </a>
                    <?php endif; ?>

                    <?php if (tienePermiso('cargar_compras_json')): ?>
                        <a class="btn btn-success btn-sm"
                            href="<?= base_url('purchases/load') ?>">
                            <i class="fa-solid fa-upload"></i> Cargar json
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">

                <table class="table table-striped table-bordered table-hover">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($compras)): ?>
                            <?php foreach ($compras as $compra): ?>
                                <tr>
                                    <td><?= $compra->id ?></td>

                                    <td>
                                        <?= date('d/m/Y', strtotime($compra->fecha_emision)) ?>
                                    </td>

                                    <td><?= $compra->proveedor_nombre ?? 'Sin proveedor' ?></td>

                                    <td>$<?= number_format($compra->total_pagar, 2) ?></td>

                                    <td>
                                        <a href="<?= base_url('purchases/show/' . $compra->id) ?>"
                                            class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        <a href="<?= base_url('purchases/edit/' . $compra->id) ?>"
                                            class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    No hay compras registradas
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

<?= $this->endSection() ?>