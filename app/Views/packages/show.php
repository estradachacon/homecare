<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Detalle del Paquete #<?= $package['id'] ?></h4>
            </div>

            <div class="card-body">

                <!-- Información General + Foto en paralelo -->
                <h5 class="text-secondary mb-3">Información del Paquete</h5>

                <div class="row">

                    <!-- Columna izquierda: Información -->
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%">Cliente</th>
                                <td><?= esc($package['cliente']) ?></td>
                            </tr>
                            <tr>
                                <th>Tipo de Servicio</th>
                                <td><?= serviceLabel($package['tipo_servicio']) ?></td>
                            </tr>
                            <tr>
                                <th>Monto</th>
                                <td><strong>$<?= number_format($package['monto'], 2) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Estatus</th>
                                <td><?= statusBadge($package['estatus'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Columna derecha: Foto -->
                    <div class="col-md-4 text-center">

                        <?php
                        // Normalizar acceso del arreglo
                        $fotoFile = isset($package['foto']) ? trim($package['foto']) : null;

                        // Asegurar que la foto existe en el servidor, si no usar placeholder
                        $rutaReal = FCPATH . 'upload/paquetes/' . $fotoFile;

                        $foto = (!empty($fotoFile) && file_exists($rutaReal))
                            ? base_url('upload/paquetes/' . $fotoFile)
                            : base_url('upload/no-image.png');
                        ?>

                        <img src="<?= $foto ?>" alt="Foto del Paquete" class="img-thumbnail shadow-sm"
                            style="width: 100%; max-width: 200px; height: 200px; object-fit: cover; cursor: pointer;"
                            data-toggle="modal" data-target="#modalFotoPaquete">

                        <p class="text-muted mt-2">Click para ver en grande</p>

                    </div>


                </div>


                <!-- Destino -->
                <h5 class="text-secondary mt-4 mb-3">Destino</h5>
                <table class="table table-bordered">
                    <?php if ($package['tipo_servicio'] == 1): ?>
                        <tr>
                            <th>Punto Fijo</th>
                            <td><?= esc($package->punto_fijo_nombre ?? 'N/A') ?></td>
                        </tr>

                    <?php elseif ($package['tipo_servicio'] == 2): ?>
                        <tr>
                            <th>Destino Personalizado</th>
                            <td><?= esc($package->destino_personalizado ?? 'N/A') ?></td>
                        </tr>

                    <?php elseif ($package['tipo_servicio'] == 3): ?>
                        <tr>
                            <th>Lugar de Recolección</th>
                            <td><?= esc($package->lugar_recolecta_paquete ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Destino Final</th>
                            <td>
                                <?= esc($package->destino_entrega_final ?? 'Pendiente') ?>
                            </td>
                        </tr>

                    <?php elseif ($package->tipo_servicio == 4): ?>
                        <tr>
                            <th>Casillero</th>
                            <td><?= esc($package->numero_casillero ?? 'N/A') ?></td>
                        </tr>
                    <?php endif; ?>
                </table>

                <!-- Notas u otros datos -->
                <h5 class="text-secondary mt-4 mb-3">Información Adicional</h5>
                <table class="table table-bordered">
                    <tr>
                        <t7h>Nota</th>
                        <td><?= !empty($package->nota) ? esc($package->nota) : 'Sin notas' ?></td>
                    </tr>
                    <tr>
                        <th>Creado</th>
                        <td><?= esc($package['created_at']) ?></td>
                    </tr>
                    <tr>
                        <th>Actualizado</th>
                        <td><?= esc($package['updated_at']) ?></td>
                    </tr>
                </table>

                <div class="text-right mt-4">
                    <a href="<?= base_url('packages') ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
<!-- Modal para ver la imagen en grande -->
<div class="modal fade" id="modalFotoPaquete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body p-0 text-center">
                <img 
                    src="<?= $foto ?>" 
                    alt="Foto ampliada" 
                    style="width: 100%; border-radius: 4px;"
                >
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>