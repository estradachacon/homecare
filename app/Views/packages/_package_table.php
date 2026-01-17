                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="col-md-2">Vendedor</th>
                            <th class="col-md-2">Cliente</th>
                            <th class="col-md-3">Tipo Servicio</th>
                            <th>Datos de fechas</th>
                            <th class="col-md-1">Valores</th>
                            <th>Estatus</th>
                            <th class="col-md-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="line-height: 18px;">
                        <?php if (!empty($packages)): ?>
                            <?php foreach ($packages as $pkg): ?>
                                <tr>
                                    <td><?= esc($pkg['id']) ?></td>
                                    <td><?= esc($pkg['seller_name']) ?></td>
                                    <td><?= esc($pkg['cliente']) ?></td>
                                    <td>
                                        <!-- Servicio principal -->
                                        <strong><?= esc($tipoServicio[$pkg['tipo_servicio']] ?? 'Desconocido') ?></strong>

                                        <!-- Subtexto del servicio actual -->
                                        <?php if ($pkg['tipo_servicio'] == 1 && !empty($pkg['point_name'])): ?>
                                            <span class="text-muted ml-2">
                                                <?= esc($pkg['point_name']) ?>
                                            </span>

                                        <?php elseif ($pkg['tipo_servicio'] == 2 && !empty($pkg['destino_personalizado'])): ?>
                                            <span class="text-muted ml-2">
                                                <?= esc($pkg['destino_personalizado']) ?>
                                            </span>

                                        <?php elseif ($pkg['tipo_servicio'] == 3 && !empty($pkg['lugar_recolecta_paquete'])): ?>
                                            <span class="text-muted ml-2">
                                                <?= esc($pkg['lugar_recolecta_paquete']) ?>
                                            </span>
                                        <?php endif; ?>

                                        <!-- SOLO para tipo_servicio = 3 → mostrar destino -->
                                        <?php if ($pkg['tipo_servicio'] == 3): ?>
                                            <br>

                                            <?php
                                            // Lógica para determinar el destino final
                                            if (!empty($pkg['point_name'])) {
                                                $destino = esc($pkg['point_name']);
                                                $pendiente = false;
                                            } elseif (!empty($pkg['destino_personalizado'])) {
                                                $destino = esc($pkg['destino_personalizado']);
                                                $pendiente = false;
                                            } else {
                                                $destino = 'Destino pendiente';
                                                $pendiente = true;
                                            }
                                            ?>

                                            <!-- Mostrar destino final -->
                                            <small>
                                                <strong>Destino final:</strong>

                                                <?php
                                                $destino = '';
                                                $pendiente = true;

                                                if (!empty($pkg['point_name'])) {

                                                    $destino = esc($pkg['point_name']);
                                                    $pendiente = false;
                                                } elseif (!empty($pkg['destino_personalizado']) && strtolower($pkg['destino_personalizado']) !== 'casillero') {

                                                    // Usar destino_personalizado SOLO si NO dice "Casillero"
                                                    $destino = esc($pkg['destino_personalizado']);
                                                    $pendiente = false;
                                                } elseif (!empty($pkg['branch_name'])) {

                                                    // Si es casillero en tipo 3, usar la sucursal
                                                    $destino = 'Casillero → ' . esc($pkg['branch_name']);
                                                    $pendiente = false;
                                                } else {

                                                    $destino = 'Destino pendiente';
                                                    $pendiente = true;
                                                }
                                                ?>

                                                <?php if ($pendiente): ?>
                                                    <span class="badge bg-warning text-dark"><?= $destino ?></span>
                                                <?php else: ?>
                                                    <span class="text-info"><?= $destino ?></span>
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($pkg['tipo_servicio'] == 4 && !empty($pkg['branch_name'])): ?>
                                            <span class="text-muted ml-2">
                                                <?= esc($pkg['branch_name']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div>
                                            <strong>Inicio:</strong>
                                            <span class="text-muted">
                                                <?= esc(formatFechaConDia($pkg['fecha_ingreso'])) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <strong>Día de entrega:</strong>
                                            <?php
                                            $fechaEntrega = null;

                                            if ($pkg['tipo_servicio'] == 1) {
                                                // Punto fijo
                                                $fechaEntrega = $pkg['fecha_entrega_puntofijo'] ?? null;
                                            } elseif ($pkg['tipo_servicio'] == 2) {
                                                // Personalizado
                                                $fechaEntrega = $pkg['fecha_entrega_personalizado'] ?? null;
                                            } elseif ($pkg['tipo_servicio'] == 3) {
                                                // Recolecta: puede ser personalizado o punto fijo
                                                if (!empty($pkg['id_puntofijo'])) {
                                                    $fechaEntrega = $pkg['fecha_entrega_puntofijo'] ?? null;
                                                } elseif (!empty($pkg['destino_personalizado'])) {
                                                    $fechaEntrega = $pkg['fecha_entrega_personalizado'] ?? null;
                                                }
                                            } elseif ($pkg['tipo_servicio'] == 4) {
                                                // Casillero
                                                $fechaEntrega = null;
                                            }
                                            ?>

                                            <span class="text-muted">
                                                <?= $fechaEntrega ? esc(formatFechaConDia($fechaEntrega)) : 'Pendiente' ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Monto:</strong>
                                            $<?= number_format($pkg['monto'], 2) ?>
                                        </div>

                                        <div>
                                            <strong>Envío:</strong>
                                            $<?= number_format($pkg['flete_total'], 2) ?>
                                        </div>
                                    </td>
                                    <td style="text-align:center; vertical-align:middle;">
                                        <div style="
                                                display: flex;
                                                flex-direction: column;
                                                gap: 4px;
                                                align-items: center;
                                                justify-content: center;
                                                font-size: medium;
                                            ">
                                            <?= statusBadge($pkg['estatus']); ?>

                                            <?php if (!empty($pkg['estatus2'])): ?>
                                                <?= statusBadge($pkg['estatus2']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>


                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                data-toggle="dropdown">Acciones
                                            </button>
                                            <ul class="dropdown-menu" style="min-width: 230px !important;">

                                                <!-- Ver paquete -->
                                                <li>
                                                    <a class="dropdown-item" href="<?= base_url('packages/' . $pkg['id']) ?>">
                                                        <i class="fa-solid fa-arrow-trend-up"></i>Info Paquete
                                                    </a>
                                                </li>
                                                <!-- Ver Foto -->
                                                <li>
                                                    <?php if (!empty($pkg['foto'])): ?>
                                                        <!-- Botón activo -->
                                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                                            data-target="#fotoModal<?= $pkg['id'] ?>">
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
                                            </ul>
                                        </div>
                                    </td>
                                    <?php $this->setVar('pkg', $pkg); ?>
                                    <?= $this->include('modals/package_index_photoview') ?>
                                    <?= $this->include('modals/package_resend') ?>
                                <?php endforeach; ?>
                                </tr>
                                <?php foreach ($packages as $pkg): ?>
                                    <tr>
                                        <?php if ($pkg['tipo_servicio'] == 3 && empty($pkg['point_name']) && empty($pkg['destino_personalizado'])): ?>

                                            <?php $this->setVar('pkg', $pkg); ?>
                                            <?php $this->setVar('puntos_fijos', $puntos_fijos); ?>

                                            <?= $this->include('modals/package_index_add_destino') ?>
                                            <?= $this->include('modals/package_resend') ?>

                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">No hay paquetes registrados</td>
                                </tr>
                            <?php endif; ?>
                    </tbody>
                </table>