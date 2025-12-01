<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<script>
    const sellerSearchUrl = "<?= base_url('sellers-search') ?>";
    const puntoFijoSearchUrl = "<?= base_url('settledPoints/getList') ?>";
    const branchSearchUrl = "<?= base_url('branches-list') ?>";
</script>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex">
                <h3 class="header-title">Editar Paquete ID: <?= $package['id'] ?></h3>
                <hr>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $e): ?>
                                <li><?= $e ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('packages/update/' . $package['id']) ?>" method="post">

                    <?= csrf_field() ?>
                    <div class="row">

                        <div class="col-md-6 text-dark">
                            <label>Vendedor</label>

                            <select name="vendedor" id="vendedor" class="form-control select2-seller" required>
                                <?php if (!empty($package['vendedor'])): ?>
                                    <option value="<?= esc($package['vendedor']) ?>" selected>
                                        <?= esc($package['seller_name']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>


                        <div class="col-md-6">
                            <label>Cliente</label>
                            <input type="text" name="cliente" class="form-control"
                                value="<?= esc($package['cliente']) ?>" required>
                        </div>
                        <?php
                        $tiposServicio = [
                            1 => 'Punto Fijo',
                            2 => 'Entrega Personalizada',
                            3 => 'Recolecta',
                            4 => 'Casillero'
                        ];
                        ?>
                        <div class="col-md-4 mt-3">
                            <label>Tipo Servicio</label>
                            <select name="tipo_servicio" id="tipo_servicio" class="form-control" required>
                                <option value="">Seleccione...</option>

                                <?php foreach ($tiposServicio as $key => $nombre): ?>
                                    <option value="<?= $key ?>"
                                        <?= ($package['tipo_servicio'] == $key) ? 'selected' : '' ?>>
                                        <?= esc($nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8 mt-3">
                            <label>Destino Personalizado</label>
                            <input type="text" name="destino_personalizado" class="form-control"
                                value="<?= esc($package['destino_personalizado']) ?>">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label>Lugar Recolecta</label>
                            <input type="text" name="lugar_recolecta_paquete" class="form-control"
                                value="<?= esc($package['lugar_recolecta_paquete']) ?>">
                        </div>

                        <!-- Sucursal para casillero -->
                        <div class="col-md-6 mt-3 sucursal-container" id="sucursal_container">
                            <label class="form-label">Sucursal</label>
                            <select name="branch"
                                id="branch"
                                class="form-control select2-branch"
                                data-initial-id="<?= esc($package['branch_id'] ?? '') ?>"
                                data-initial-text="<?= esc($package['branch_name'] ?? '') ?>">
                            </select>
                        </div>

                        <div class="col-md-6 mt-3 punto-fijo-container" id="punto_fijo_container">
                            <label class="form-label">Punto Fijo</label>

                            <select name="id_puntofijo" id="punto_fijo" class="form-control select2-punto_fijo">
                                <?php if (!empty($package['id_puntofijo'])): ?>
                                    <option value="<?= esc($package['id_puntofijo']) ?>" selected>
                                        <?= esc($package['point_name']) ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mt-3">
                            <label>Fecha Ingreso</label>
                            <input type="date" name="fecha_ingreso" class="form-control"
                                value="<?= esc($package['fecha_ingreso']) ?>">
                        </div>

                        <div class="col-md-3 mt-3">
                            <label>Fecha Entrega Personalizado</label>
                            <input type="date" name="fecha_entrega_personalizado" class="form-control"
                                value="<?= esc($package['fecha_entrega_personalizado']) ?>">
                        </div>

                        <div class="col-md-3 mt-3">
                            <label>Fecha Entrega Punto Fijo</label>
                            <input type="date" name="fecha_entrega_puntofijo" class="form-control"
                                value="<?= esc($package['fecha_entrega_puntofijo']) ?>">
                        </div>

                        <div class="col-md-4 mt-3">
                            <label>Flete Total</label>
                            <input type="number" step="0.01" name="flete_total" class="form-control"
                                value="<?= esc($package['flete_total']) ?>">
                        </div>

                        <div class="col-md-4 mt-3">
                            <label>Flete Pagado</label>
                            <input type="number" step="0.01" name="flete_pagado" class="form-control"
                                value="<?= esc($package['flete_pagado']) ?>">
                        </div>

                        <div class="col-md-4 mt-3">
                            <label>Flete Pendiente</label>
                            <input type="number" step="0.01" name="flete_pendiente" class="form-control"
                                value="<?= esc($package['flete_pendiente']) ?>">
                        </div>

                        <div class="col-md-4 mt-3">
                            <label>Monto del paquete</label>
                            <input type="number" step="0.01" name="monto" class="form-control"
                                value="<?= esc($package['monto']) ?>">
                        </div>

                        <div class="col-md-4 text-center mt-3">
                            <label class="form-label d-block mb-2">¿Es frágil?</label>
                            <div class="toggle-pill">

                                <input
                                    type="radio"
                                    id="fragilNo"
                                    name="fragil"
                                    value="0"
                                    <?= $package['fragil'] == 0 ? 'checked' : '' ?>>
                                <label for="fragilNo">No</label>

                                <input
                                    type="radio"
                                    id="fragilSi"
                                    name="fragil"
                                    value="1"
                                    <?= $package['fragil'] == 1 ? 'checked' : '' ?>>
                                <label for="fragilSi">Sí</label>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label>Comentarios</label>
                            <textarea name="comentarios" class="form-control" rows="3"><?= esc($package['comentarios']) ?></textarea>
                        </div>

                    </div>

                    <button class="btn btn-primary mt-4">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="/backend/assets/js/scripts_packaging_edit.js"></script>
<?= $this->endSection() ?>