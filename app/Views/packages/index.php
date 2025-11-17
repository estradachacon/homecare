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
                <?php
                $tipoServicio = [
                    1 => 'Punto fijo: ',
                    2 => 'Personalizado: ',
                    3 => 'Recolecta de paquete: ',
                    4 => 'Casillero: '
                ];
                ?>
                <?php
                function formatFechaConDia($fecha)
                {
                    if (empty($fecha))
                        return null;

                    // Crear objeto DateTime
                    $dt = new DateTime($fecha);

                    // Diccionario de días (en español)
                    $dias = [
                        'Mon' => 'Lun',
                        'Tue' => 'Mar',
                        'Wed' => 'Mié',
                        'Thu' => 'Jue',
                        'Fri' => 'Vie',
                        'Sat' => 'Sáb',
                        'Sun' => 'Dom'
                    ];

                    $dia = $dias[$dt->format('D')] ?? $dt->format('D');

                    return $dia . ' ' . $dt->format('d/m/Y');
                }
                ?>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th class="col-md-2">Vendedor</th>
                            <th class="col-md-2">Cliente</th>
                            <th class="col-md-3">Tipo Servicio</th>
                            <th>Datos de fechas</th>
                            <th class="col-md-1">Valores</th>
                            <th class="col-md-1">Estatus</th>
                            <th class="col-md-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($packages)): ?>
                            <?php foreach ($packages as $pkg): ?>
                                <tr>
                                    <td><?= esc($pkg['id']) ?></td>
                                    <td><?= esc($pkg['seller_name']) ?></td>
                                    <td><?= esc($pkg['cliente']) ?></td>
                                    <td>
                                        <strong><?= esc($tipoServicio[$pkg['tipo_servicio']] ?? 'Desconocido') ?></strong>

                                        <?php if ($pkg['tipo_servicio'] == 1 && !empty($pkg['point_name'])): ?>
                                            <span class="text-muted ml-2">
                                                <?= esc($pkg['point_name']) ?>
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
                                            <strong>Finalizado:</strong>
                                            <?php if (!empty($pkg['fecha_pack_entregado'])): ?>
                                                <span class="text-muted">
                                                    <?= esc(formatFechaConDia($pkg['fecha_pack_entregado'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Pendiente</span>
                                            <?php endif; ?>
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

                                    <td><?= esc(ucfirst($pkg['estatus'])) ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                data-toggle="dropdown">Acciones
                                            </button>
                                            <ul class="dropdown-menu">

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

                                                <!-- Editar -->
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="<?= base_url('packages/edit/' . $pkg['id']) ?>">
                                                        <i class="fa-solid fa-pencil"></i>Editar
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <?php if (!empty($pkg['foto'])): ?>
                                        <div class="modal fade" id="fotoModal<?= $pkg['id'] ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document"
                                                style="max-width: 90%;">
                                                <div class="modal-content">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Foto del paquete #<?= esc($pkg['id']) ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>

                                                    <div class="modal-body text-center">
                                                        <img src="<?= base_url('upload/paquetes/' . $pkg['foto']) ?>" alt="Foto del paquete"
                                                            class="img-fluid rounded"
                                                            style="max-height: 80vh; object-fit: contain;">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
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
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>