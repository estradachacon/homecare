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
                            <th class="col-md-1"># DTE</th>
                            <th class="col-md-1">Tipo DTE</th>
                            <th>Proveedor</th>
                            <th class="col-md-2">Fecha DTE</th>
                            <th class="col-md-2">Total</th>
                            <th class="col-md-1">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($compras)): ?>
                            <?php foreach ($compras as $compra): ?>
                                <?php
                                $correlativo = !empty($compra->numero_control)
                                    ? substr($compra->numero_control, -6)
                                    : 'N/D';

                                // tipo de DTE (ej: 01, 03, etc)
                                $tipoDte = $compra->tipo_dte ?? 'N/D';

                                // opcional: traducirlo bonito
                                $tipos = [
                                    '01' => 'Factura',
                                    '03' => 'CCF',
                                    '05' => 'Crédito Fiscal',
                                ];

                                $tipoTexto = $tipos[$tipoDte] ?? $tipoDte;
                                ?>

                                <tr>
                                    <!-- Número de factura -->
                                    <td>
                                            <?= $correlativo ?>
                                    </td>

                                    <!-- Tipo -->
                                    <td><?= $tipoTexto ?></td>

                                    <!-- Cliente (en compras es proveedor) -->
                                    <td><?= $compra->proveedor_nombre ?? 'Sin proveedor' ?></td>

                                    <!-- Fecha -->
                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($compra->fecha_emision)) ?>
                                    </td>

                                    <!-- Total -->
                                    <td class="text-center">$<?= number_format($compra->total_pagar, 2) ?></td>

                                    <!-- Acciones -->
                                    <td>
                                        <a href="<?= base_url('purchases/' . $compra->id) ?>"
                                            class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
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