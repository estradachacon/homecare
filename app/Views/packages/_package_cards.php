<?php if (!empty($packages)): ?>
    <?php foreach ($packages as $pkg): ?>
        <div class="card mb-2 shadow-sm border-0" style="border-radius: 8px;">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between mb-1">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-dark mr-2">#<?= esc($pkg['id']) ?></span>
                        <?= statusBadge($pkg['estatus']) ?>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-link btn-sm text-muted p-0" type="button" data-toggle="dropdown">
                            <i class="fa-solid fa-circle-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow border-0">
                            <li>
                                <a class="dropdown-item" href="<?= base_url('packages/' . $pkg['id']) ?>">
                                    <i class="fa-solid fa-arrow-trend-up"></i>Info Paquete
                                </a>
                            </li>
                            <li>
                                <?php if (!empty($pkg['foto'])): ?>
                                    <a class="dropdown-item btn-ver-foto"
                                        href="#"
                                        data-foto="<?= esc($pkg['foto']) ?>">
                                        <i class="fa-solid fa-image"></i> Ver foto
                                    </a>
                                <?php else: ?>
                                    <!-- Botón deshabilitado -->
                                    <span class="dropdown-item text-muted"
                                        style="cursor:not-allowed; opacity:0.6;">
                                        <i class="fa-solid fa-image"></i> Ver foto
                                    </span>
                                <?php endif; ?>
                            </li>

                            <?php if ($pkg['estatus2'] != 'devuelto'): ?>

                                <?php if (
                                    $pkg['estatus'] == 'pendiente' ||
                                    $pkg['estatus'] == 'recolectado' ||
                                    $pkg['estatus'] == 'en_casillero' ||
                                    $pkg['estatus'] == 'no_retirado'
                                ): ?>
                                    <li>
                                        <a class="dropdown-item"
                                            href="<?= base_url('packages/edit/' . $pkg['id']) ?>">
                                            <i class="fa-solid fa-pencil"></i>Editar paquete
                                        </a>
                                    </li>
                                <?php endif; ?>

                            <?php endif; ?>
                            <!-- AGREGAR DESTINO (solo si es recolecta y no tiene destino final) -->
                            <?php if ($pkg['tipo_servicio'] == 3 && empty($pkg['point_name']) && empty($pkg['destino_personalizado'])): ?>
                                <li>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#setDestinoModal<?= $pkg['id'] ?>">
                                        <i class="fa-solid fa-location-dot"></i> Agregar destino
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- CONFIGURAR REENVÍO -->
                            <?php if (
                                $pkg['estatus'] === 'no_retirado'
                                && (!isset($pkg['estatus2']) || $pkg['estatus2'] !== 'devuelto')
                            ): ?>
                                <li>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#reenvioModal<?= $pkg['id'] ?>">
                                        <i class="fa-solid fa-repeat"></i> Configurar reenvío
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- ENTREGAR PAQUETE DEL CASILLERO -->
                            <?php if ($pkg['estatus2'] != 'devuelto'): ?>
                                <?php if (
                                    $pkg['estatus'] == 'en_casillero'
                                ): ?>
                                    <li>
                                        <a class="dropdown-item btn-entregar-casillero"
                                            href="#"
                                            data-id="<?= $pkg['id'] ?>"
                                            data-foto="<?= esc($pkg['foto'] ?? '') ?>"
                                            data-valor="<?= number_format($pkg['monto'], 2, '.', '') ?>">

                                            <i class="fa-solid fa-box-open"></i> Entrega de paquete
                                        </a>

                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- DEVOLVER PAQUETE -->
                            <?php if ($pkg['estatus2'] != 'devuelto'): ?>
                                <?php if (
                                    $pkg['estatus'] == 'pendiente' ||
                                    $pkg['estatus'] == 'recolectado' ||
                                    $pkg['estatus'] == 'en_casillero' ||
                                    $pkg['estatus'] == 'no_retirado'
                                ): ?>
                                    <li>
                                        <a class="dropdown-item btn-devolver"
                                            href="#"
                                            data-id="<?= $pkg['id'] ?>"
                                            data-foto="<?= esc($pkg['foto'] ?? '') ?>">
                                            <i class="fa-solid fa-undo"></i> Devolver paquete
                                        </a>

                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row no-gutters mb-1">
                    <div class="col-7 border-right pr-2">
                        <div class="text-truncate">
                            <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Cliente:</small><br>
                            <span class="font-weight-bold" style="font-size: 0.9rem;"><?= esc($pkg['cliente']) ?></span>
                        </div>
                        <div class="text-truncate mt-1">
                            <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Servicio:</small><br>
                            <span class="text-primary" style="font-size: 0.85rem; font-weight: 500;">
                                <?= esc($tipoServicio[$pkg['tipo_servicio']] ?? 'N/A') ?>
                            </span>
                        </div>
                    </div>

                    <div class="col-5 pl-2">
                        <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Vendedor:</small><br>
                        <div class="text-truncate text-secondary" style="font-size: 0.85rem;">
                            <?= esc($pkg['seller_name']) ?>
                        </div>

                        <!-- MINI FOTO LAZY -->
                        <?php if (!empty($pkg['foto'])): ?>
                            <div class="mt-1">
                                <img src="<?= base_url('upload/paquetes/' . esc($pkg['foto'])) ?>"
                                    class="img-thumbnail btn-ver-foto"
                                    data-foto="<?= esc($pkg['foto']) ?>"
                                    loading="lazy"
                                    style="
                    width: 55px;
                    height: 55px;
                    object-fit: cover;
                    cursor: pointer;
                 "
                                    alt="Foto paquete">
                            </div>
                        <?php endif; ?>

                        <div class="mt-1">
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="fa-regular fa-calendar-check mr-1"></i>
                                <?= esc($pkg['fecha_entrega_puntofijo'] ?? 'Pendiente') ?>
                            </small>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-between pt-1 border-top" style="background-color: #f8f9fa; margin: 0 -0.5rem -0.5rem -0.5rem; padding: 0.4rem 0.5rem; border-radius: 0 0 8px 8px;">
                    <div>
                        <small class="text-muted">Ingreso: </small>
                        <small class="font-weight-bold"><?= esc(date('d/m/y', strtotime($pkg['fecha_ingreso']))) ?></small>
                    </div>
                    <div>
                        <span class="badge badge-success px-2" style="font-size: 0.85rem;">
                            $<?= number_format($pkg['monto'], 2) ?>
                        </span>
                        <span class="text-muted mx-1">|</span>
                        <small class="text-dark font-weight-bold">$<?= number_format($pkg['flete_total'], 2) ?> <i class="fa-solid fa-truck" style="font-size: 0.7rem;"></i></small>
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
    <div class="modal fade" id="fotoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Foto del paquete</h6>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body p-2 text-center">
                    <img id="fotoModalImg"
                        src=""
                        class="img-fluid rounded"
                        loading="lazy"
                        alt="Foto paquete">
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).on('click', '.btn-ver-foto', function(e) {
            e.preventDefault();

            let foto = $(this).data('foto');

            if (!foto) return;

            let imgUrl = "<?= base_url('upload/paquetes') ?>/" + foto;

            $('#fotoModalImg')
                .attr('src', imgUrl)
                .hide()
                .on('load', function() {
                    $(this).fadeIn(150);
                });

            $('#fotoModal').modal('show');
        });
    </script>

<?php else: ?>
    <div class="text-center py-4 text-muted">No hay paquetes</div>
<?php endif; ?>