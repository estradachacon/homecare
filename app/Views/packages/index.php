<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('backend/assets/css/newpackage.css') ?>">
<style>
    /* Forzar que el select ocupe todo el ancho */
    #filter_seller {
        width: 100%;
        height: 38px;
        /* altura visible */
        padding: 5px 10px;
        font-size: 14px;
        border-radius: 4px;
    }

    /* Si usas Select2 */
    .select2-container--bootstrap4 .select2-selection--single {
        height: 38px !important;
        line-height: 28px !important;
        padding: 5px 12px !important;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de Paquetes</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('packages/new') ?>"><i
                        class="fa-solid fa-plus"></i> Registrar nuevo</a>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('packages') ?>" class="mb-3">
                    <div class="row">

                        <!-- Vendedor -->
                        <div class="col-md-3">
                            <label class="form-label">Vendedor</label>
                            <select name="vendedor_id" id="filter_seller" class="form-control">
                                <option value="">-- Todos --</option>
                                <?php foreach ($sellers as $s): ?>
                                    <option value="<?= esc($s->id) ?>" <?= ($filter_vendedor_id == $s->id) ? 'selected' : '' ?>>
                                        <?= esc($s->seller) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Estatus -->
                        <div class="col-md-2">
                            <label class="form-label">Estatus</label>
                            <select name="estatus" class="form-control">
                                <option value="">Todos</option>
                                <option value="pendiente" <?= ($filter_status == 'pendiente') ? 'selected' : '' ?>>
                                    Pendiente</option>
                                <option value="asignado" <?= ($filter_status == 'asignado') ? 'selected' : '' ?>>Asignado
                                </option>
                                <option value="entregado" <?= ($filter_status == 'entregado') ? 'selected' : '' ?>>
                                    Entregado</option>
                                <option value="en_casillero" <?= ($filter_status == 'en_casillero') ? 'selected' : '' ?>>
                                    En casillero</option>
                                <option value="cancelado" <?= ($filter_status == 'cancelado') ? 'selected' : '' ?>>
                                    Cancelado</option>
                            </select>
                        </div>

                        <!-- Tipo de servicio -->
                        <div class="col-md-2">
                            <label class="form-label">Tipo servicio</label>
                            <select name="tipo_servicio" class="form-control">
                                <option value="">Todos</option>
                                <option value="1" <?= ($filter_service == 1) ? 'selected' : '' ?>>Punto fijo</option>
                                <option value="2" <?= ($filter_service == 2) ? 'selected' : '' ?>>Personalizado</option>
                                <option value="3" <?= ($filter_service == 3) ? 'selected' : '' ?>>Recolecta</option>
                                <option value="4" <?= ($filter_service == 4) ? 'selected' : '' ?>>Casillero</option>
                            </select>
                        </div>

                        <!-- Fecha desde -->
                        <div class="col-md-2">
                            <label class="form-label">Fecha desde</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                value="<?= esc($filter_date_from) ?>">
                        </div>

                        <!-- Fecha hasta -->
                        <div class="col-md-2">
                            <label class="form-label">Fecha hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control"
                                value="<?= esc($filter_date_to) ?>">
                        </div>

                    </div>

                    <div class="row mt-3">

                        <!-- Cantidad de resultados -->
                        <div class="col-md-2">
                            <label class="form-label">Mostrar</label>
                            <select name="per_page" class="form-control">
                                <option value="10" <?= ($perPage == 10) ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= ($perPage == 25) ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= ($perPage == 50) ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= ($perPage == 100) ? 'selected' : '' ?>>100</option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block mt-4">Filtrar</button>
                        </div>

                        <div class="col-md-2">
                            <a href="<?= base_url('packages') ?>" class="btn btn-secondary btn-block mt-4">Limpiar</a>
                        </div>

                    </div>
                </form>

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

                                                <?php if ($pendiente): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <?= $destino ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-info"><?= $destino ?></span>
                                                <?php endif; ?>
                                            </small>
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

                                    <td style="text-align:center; vertical-align:middle;">
                                        <div style="
                                            display:flex;
                                            font-size: medium;
                                            align-items:center;      /* centro vertical */
                                            justify-content:center;  /* centro horizontal */
                                            height:15px;             /* más altura del área */
                                        ">
                                            <?= statusBadge($pkg['estatus']); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                data-toggle="dropdown">Acciones
                                            </button>
                                            <ul class="dropdown-menu" style="min-width: 220px !important;">

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
                                                <!-- AGREGAR DESTINO (solo si es recolecta y no tiene destino final) -->
                                                <?php if ($pkg['tipo_servicio'] == 3 && empty($pkg['point_name']) && empty($pkg['destino_personalizado'])): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#setDestinoModal<?= $pkg['id'] ?>">
                                                            <i class="fa-solid fa-location-dot"></i> Agregar destino
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
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
                                                        <img src="<?= base_url('upload/paquetes/' . $pkg['foto']) ?>"
                                                            alt="Foto del paquete" class="img-fluid rounded"
                                                            style="max-height: 80vh; object-fit: contain;">
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </tr>
<?php foreach ($packages as $pkg): ?>
                                <?php if ($pkg['tipo_servicio'] == 3 && empty($pkg['point_name']) && empty($pkg['destino_personalizado'])): ?>

                                <div class="modal fade" id="setDestinoModal<?= $pkg['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-md modal-dialog-centered">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5 class="modal-title">Agregar destino al paquete #<?= $pkg['id'] ?></h5>
                                                <button class="close" data-dismiss="modal"><span>&times;</span></button>
                                            </div>

                                            <form method="post" action="<?= base_url('packages-setDestino') ?>">
                                                <?= csrf_field() ?>

                                                <div class="modal-body">

                                                    <input type="hidden" name="id" value="<?= $pkg['id'] ?>">

                                                    <!-- Tipo de destino -->
                                                    <label class="form-label">Tipo de destino</label>
                                                    <select name="tipo_destino" class="form-control selDestino" data-id="<?= $pkg['id'] ?>">
                                                        <option value="">Seleccione...</option>
                                                        <option value="punto">Punto fijo</option>
                                                        <option value="personalizado">Destino personalizado</option>
                                                        <option value="casillero">Casillero</option>
                                                    </select>

                                                    <!-- PUNTO FIJO -->
                                                    <div class="mt-3 d-none divDestino" id="divPunto<?= $pkg['id'] ?>">
                                                        <label>Punto fijo</label>
                                                        <select name="id_puntofijo" class="form-control select2punto puntoSelect" data-id="<?= $pkg['id'] ?>">
                                                            <option value="">Seleccione...</option>
                                                        </select>
                                                    </div>

                                                    <!-- PERSONALIZADO -->
                                                    <div class="mt-3 d-none divDestino" id="divPersonalizado<?= $pkg['id'] ?>">
                                                        <label>Dirección personalizada</label>
                                                        <input type="text" name="destino_personalizado"
                                                            class="form-control inputPersonalizado" 
                                                            placeholder="Escriba el destino...">
                                                    </div>

                                                    <!-- CASILLERO -->
                                                    <div class="mt-3 d-none divDestino" id="divCasillero<?= $pkg['id'] ?>">
                                                        <label>Destino</label>
                                                        <input type="text" class="form-control" value="Casillero" readonly>
                                                    </div>

                                                    <!-- FECHA DE ENTREGA -->
                                                    <div class="mt-3" id="fechaEntregaBox<?= $pkg['id'] ?>" style="display:none;">
                                                        <label>Fecha de entrega</label>
                                                        <input type="text" name=""
                                                            class="form-control fechaEntrega"
                                                            id="fechaEntrega<?= $pkg['id'] ?>" autocomplete="off">
                                                    </div>

                                                </div>

                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                    <button class="btn btn-primary">Guardar destino</button>
                                                </div>

                                            </form>

                                        </div>
                                    </div>
                                </div>

                                <?php endif; ?>
                                <?php endforeach; ?>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No hay paquetes registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    <?= $pager->links('default', 'bitacora_pagination') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    // ---------------------------
    // Select2 AJAX punto fijo
    // ---------------------------
    $('.select2punto').each(function () {
        $(this).select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '🔍 Buscar punto fijo...',
            dropdownParent: $(this).closest('.modal'),
            ajax: {
                url: "<?= base_url('settledPoints/getList') ?>",
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(item => ({
                        id: item.id,
                        text: item.point_name
                    }))
                })
            }
        });
    });

    // ---------------------------
    // Control del tipo de destino
    // ---------------------------
    $('.selDestino').on('change', function () {

        let id = $(this).data('id');
        let tipo = $(this).val();
        let fechaInput = $('#fechaEntrega' + id);

        // Ocultar todos los bloques
        $('#divPunto' + id).addClass('d-none');
        $('#divPersonalizado' + id).addClass('d-none');
        $('#divCasillero' + id).addClass('d-none');

        // Reset general: mostrar contenedor fecha
        $('#fechaEntregaBox' + id).show();

        // Limpiar datepicker anterior (si existe)
        fechaInput.data('daterangepicker')?.remove();

        if (tipo === 'punto') {
            $('#divPunto' + id).removeClass('d-none');
            fechaInput.attr('name', 'fecha_entrega_puntofijo');
        }

        if (tipo === 'personalizado') {
            $('#divPersonalizado' + id).removeClass('d-none');
            
            // 🔥 Name correcto
            fechaInput.attr('name', 'fecha_entrega_personalizado');

            // Si está vacío, poner fecha de hoy
            if (!fechaInput.val()) {
                fechaInput.val(moment().format('YYYY-MM-DD'));
            }

            // Activar datepicker simple
            fechaInput.daterangepicker({
                singleDatePicker: true,
                autoApply: true,
                showDropdowns: true,
                locale: { format: 'YYYY-MM-DD', firstDay: 1 }
            });
        }

        if (tipo === 'casillero') {
            $('#divCasillero' + id).removeClass('d-none');
            
            // Sin fecha para casillero
            fechaInput.attr('name', '');
            fechaInput.val('');
            $('#fechaEntregaBox' + id).hide();
        }
    });

    // ---------------------------
    // Fecha según punto fijo
    // ---------------------------
    $('.puntoSelect').on('change', function () {

        let puntoId = $(this).val();
        let paqueteId = $(this).data('id');
        let inputFecha = $('#fechaEntrega' + paqueteId);

        if (!puntoId) {
            inputFecha.val('');
            return;
        }

        $.ajax({
            url: "<?= base_url('settledPoints/getDays') ?>/" + puntoId,
            method: "GET",
            dataType: "json",
            success: function (days) {

                const allowedDays = [];
                if (days.sun) allowedDays.push(0);
                if (days.mon) allowedDays.push(1);
                if (days.tus) allowedDays.push(2);
                if (days.wen) allowedDays.push(3);
                if (days.thu) allowedDays.push(4);
                if (days.fri) allowedDays.push(5);
                if (days.sat) allowedDays.push(6);

                let nextValid = moment();
                for (let i = 0; i < 14; i++) {
                    if (allowedDays.includes(nextValid.day())) break;
                    nextValid.add(1, 'days');
                }

                inputFecha.data('daterangepicker')?.remove();

                inputFecha.daterangepicker({
                    singleDatePicker: true,
                    showDropdowns: true,
                    autoApply: true,
                    startDate: nextValid,
                    autoUpdateInput: true,
                    isInvalidDate: d => !allowedDays.includes(d.day()),
                    locale: { format: 'YYYY-MM-DD', firstDay: 1 }
                });

                inputFecha.val(nextValid.format('YYYY-MM-DD'));
            }
        });
    });

});

</script>
<script>
$(document).ready(function () {

    // Interceptar SOLO los forms de agregar destino
    $("form[action*='packages-setDestino']").on("submit", function (e) {
        e.preventDefault();

        let form = this;

        Swal.fire({
            title: "¿Guardar destino?",
            text: "Confirmar que deseas establecer el destino seleccionado",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar"
        }).then((result) => {

            if (result.isConfirmed) {
                form.submit(); // ahora sí envía
            }

        });

    });

});
</script>
<?= $this->endSection() ?>