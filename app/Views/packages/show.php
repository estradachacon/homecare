<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?php
/**
 * Función auxiliar para formatear la fecha a día/mes/año (d/m/Y).
 * @param string|null $fecha
 * @return string
 */
function formatDateDMY($fecha)
{
    if (empty($fecha))
        return 'Pendiente';
    $d = new DateTime($fecha);
    return $d->format('d/m/Y');
}

// Asegúrate de que las funciones serviceLabel() y statusBadge() 
// estén definidas en tus Helpers o estén disponibles globalmente.
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Detalle del Paquete #<?= esc($package['id']) ?></h4>
            </div>

            <div class="card-body">

                <h5 class="text-secondary mb-3 border-bottom pb-1">Información General</h5>

                <div class="row">

                    <div class="col-md-8">
                        <table class="table table-striped" style="font-size: 1rem;">
                            <tbody>
                                <tr>
                                    <th style="width: 40%">Cliente</th>
                                    <td><?= esc($package['cliente']) ?></td>
                                </tr>
                                <tr>
                                    <th>Vendedor</th>
                                    <td><?= esc($package['seller_name'] ?? 'N/A') ?></td>
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
                                <tr>
                                    <th>¿Es Frágil?</th>
                                    <td>
                                        <?php if (!empty($package['fragil'])): ?>
                                            <span class="badge badge-danger">SÍ</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">NO</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-4 text-center">
                        <?php
                        $fotoFile = isset($package['foto']) ? $package['foto'] : null;
                        $fotoURL = (!empty($fotoFile) && $fotoFile !== 'null')
                            ? base_url('upload/paquetes/' . $fotoFile)
                            : base_url('upload/no-image.png');
                        ?>
                        <img src="<?= esc($fotoURL) ?>" alt="Foto del Paquete"
                            class="img-thumbnail shadow-sm rounded-lg"
                            style="width: 100%; max-width: 250px; height: 250px; object-fit: cover; cursor: pointer;"
                            data-toggle="modal" data-target="#modalFotoPaquete">

                        <p class="text-muted mt-2 small">Click para ver en grande</p>
                    </div>

                </div>

                <hr class="my-4">

                <h5 class="text-secondary mb-3 border-bottom pb-1">Flete y Pago</h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless" style="font-size: 1rem;">
                            <tbody>
                                <tr>
                                    <th>Flete Total</th>
                                    <td><strong>$<?= number_format($package['flete_total'], 2) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Pago Parcial Activo</th>
                                    <td><?= !empty($package['toggle_pago_parcial']) ? '<span class="badge badge-info">SÍ</span>' : '<span class="badge badge-light">NO</span>' ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless" style="font-size: 1rem;">
                            <tbody>
                                <tr>
                                    <th>Flete Pagado</th>
                                    <td class="text-success">
                                        <strong>$<?= number_format($package['flete_pagado'], 2) ?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Flete Pendiente</th>
                                    <td class="text-danger">
                                        <strong>$<?= number_format($package['flete_pendiente'], 2) ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="text-secondary mb-3 border-bottom pb-1">Destino y Logística</h5>
                <table class="table table-sm table-bordered" style="font-size: 1rem;">
                    <tbody>
                        <tr>
                            <th style="width: 40%">Fecha de Ingreso</th>
                            <td><?= formatDateDMY($package['fecha_ingreso']) ?></td>
                        </tr>

                        <?php if ($package['tipo_servicio'] == 1): // Punto Fijo ?>
                            <tr>
                                <th>Punto Fijo</th>
                                <td><?= esc($package['point_name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Fecha Estimada de Entrega (Punto Fijo)</th>
                                <td><?= formatDateDMY($package['fecha_entrega_puntofijo'] ?? null) ?></td>
                            </tr>

                        <?php elseif ($package['tipo_servicio'] == 2): // Destino Personalizado ?>
                            <tr>
                                <th>Destino Personalizado</th>
                                <td><?= esc($package['destino_personalizado'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Fecha Estimada de Entrega (Personalizado)</th>
                                <td><?= formatDateDMY($package['fecha_entrega_personalizado'] ?? null) ?></td>
                            </tr>

                        <?php elseif ($package['tipo_servicio'] == 3): // Recolección y Entrega Final ?>
                            <tr>
                                <th>Lugar de Recolección</th>
                                <td><?= esc($package['lugar_recolecta_paquete'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Destino Final</th>
                                <td><?= esc($package['destino_entrega_final'] ?? 'Pendiente') ?></td>
                            </tr>
                            <tr>
                                <th>Fecha de Entrega (Personalizado)</th>
                                <td><?= esc($package['fecha_entrega_personalizado'] ?? 'Pendiente') ?></td>
                            </tr>

                        <?php elseif ($package['tipo_servicio'] == 4): // Casillero ?>
                            <tr>
                                <th>Número de Casillero</th>
                                <td><?= esc($package['numero_casillero'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Destino</th>
                                <td><?= esc($package['destino_personalizado'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

                <hr class="my-4">

                <h5 class="text-secondary mb-3 border-bottom pb-1">Comentarios y Tiempos</h5>
                <table class="table table-sm table-bordered" style="font-size: 1rem;">
                    <tbody>
                        <tr>
                            <th style="width: 40%">Comentarios / Nota</th>
                            <td><?= !empty($package['comentarios']) ? esc($package['comentarios']) : 'Sin comentarios' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Cobro Cancelado (Por Estado)</th>
                            <td><?= !empty($package['nocobrar_pack_cancelado']) ? '<span class="badge badge-warning">SÍ</span>' : '<span class="badge badge-light">NO</span>' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Creado</th>
                            <td><?= formatDateDMY($package['created_at']) ?></td>
                        </tr>
                        <tr>
                            <th>Última Actualización</th>
                            <td><?= formatDateDMY($package['updated_at']) ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-right mt-4">
                    <a href="<?= base_url('packages') ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </a>
                    <a href="<?= base_url('packages/edit/' . $package['id']) ?>" class="btn btn-warning ml-2">
                        <i class="fa fa-pencil-alt"></i> Editar Paquete
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="modalFotoPaquete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-body p-0 text-center">
                <img src="<?= esc($fotoURL) ?>" alt="Foto ampliada" style="width: 100%; border-radius: 4px;">
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>