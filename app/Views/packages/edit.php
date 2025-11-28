<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<script>
    const sellerSearchUrl = "<?= base_url('sellers-search') ?>";
</script>

<div class="container mt-4">
    <h3>Editar Paquete ID: <?= $package['id'] ?></h3>
    <hr>

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

            <div class="col-md-6">
                <label>Vendedor</label>

                <select name="vendedor" id="vendedor" class="form-control select2-seller">
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
                    value="<?= esc($package['cliente']) ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label>Tipo Servicio</label>
                <input type="text" name="tipo_servicio" class="form-control"
                    value="<?= esc($package['tipo_servicio']) ?>">
            </div>

            <div class="col-md-8 mt-3">
                <label>Destino Personalizado</label>
                <input type="text" name="destino_personalizado" class="form-control"
                    value="<?= esc($package['destino_personalizado']) ?>">
            </div>

            <div class="col-md-12 mt-3">
                <label>Lugar Recolecta</label>
                <input type="text" name="lugar_recolecta_paquete" class="form-control"
                    value="<?= esc($package['lugar_recolecta_paquete']) ?>">
            </div>

            <div class="col-md-3 mt-3">
                <label>Punto Fijo ID</label>
                <input type="number" name="id_puntofijo" class="form-control"
                    value="<?= esc($package['id_puntofijo']) ?>">
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
                <label>Estatus</label>
                <input type="text" name="estatus" class="form-control"
                    value="<?= esc($package['estatus']) ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label>Estatus 2</label>
                <input type="text" name="estatus2" class="form-control"
                    value="<?= esc($package['estatus2']) ?>">
            </div>

            <div class="col-md-4 mt-3">
                <label>Fragil</label>
                <select name="fragil" class="form-control">
                    <option value="0" <?= $package['fragil'] == 0 ? 'selected' : '' ?>>No</option>
                    <option value="1" <?= $package['fragil'] == 1 ? 'selected' : '' ?>>Sí</option>
                </select>
            </div>

            <div class="col-md-12 mt-3">
                <label>Comentarios</label>
                <textarea name="comentarios" class="form-control" rows="3"><?= esc($package['comentarios']) ?></textarea>
            </div>

        </div>

        <button class="btn btn-primary mt-4">Actualizar</button>
    </form>
</div>

<script src="/backend/assets/js/scripts_packaging_edit.js"></script>
<?= $this->endSection() ?>