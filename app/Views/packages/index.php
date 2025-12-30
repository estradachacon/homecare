<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('backend/assets/css/newpackage.css') ?>">
<script>
    const base_url = "<?= base_url() ?>";
</script>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de Paquetes</h4>
                <?php if (tienePermiso('crear_paquetes')): ?>
                    <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('packages/new') ?>"><i
                            class="fa-solid fa-plus"></i> Registrar nuevo</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form id="formPaquete" method="GET" action="<?= base_url('packages') ?>" class="mb-3">
                    <div class="row">

                        <!-- Vendedor -->
                        <div class="col-md-4">
                            <label for="seller_id" class="form-label">Vendedor</label>
                            <select id="seller_id" name="vendedor_id" class="form-select" style="width: 100%;">
                                <option value=""></option>

                                <?php if (!empty($seller_selected)): ?>
                                    <option value="<?= esc($seller_selected->id) ?>" selected>
                                        <?= esc($seller_selected->seller) ?>
                                    </option>
                                <?php endif; ?>
                            </select>

                            <small class="form-text text-muted">Escribí para buscar un vendedor.</small>
                        </div>

                        <!-- Estatus -->
                        <div class="col-md-2">
                            <label class="form-label">Estatus</label>
                            <select name="estatus" class="form-control">
                                <option value="">Todos</option>
                                <option value="pendiente" <?= ($filter_status == 'pendiente') ? 'selected' : '' ?>>
                                    Pendiente</option>
                                <option value="asignado_para_recolecta" <?= ($filter_status == 'asignado_para_recolecta') ? 'selected' : '' ?>>Asignado para recolecta
                                </option>
                                <option value="asignado_para_entrega" <?= ($filter_status == 'asignado_para_entrega') ? 'selected' : '' ?>>Asignado para entrega
                                </option>
                                <option value="recolectado" <?= ($filter_status == 'recolectado') ? 'selected' : '' ?>>Recolectado
                                </option>
                                <option value="entregado" <?= ($filter_status == 'entregado') ? 'selected' : '' ?>>
                                    Entregado</option>
                                <option value="en_casillero" <?= ($filter_status == 'en_casillero') ? 'selected' : '' ?>>
                                    En casillero</option>
                                <option value="finalizado" <?= ($filter_status == 'finalizado') ? 'selected' : '' ?>>
                                    Finalizado</option>
                                <option value="remunerado" <?= ($filter_status == 'remunerado') ? 'selected' : '' ?>>
                                    Remunerado</option>
                                <option value="no_retirado" <?= ($filter_status == 'no_retirado') ? 'selected' : '' ?>>
                                    No retirado</option>
                                <option value="devuelto" <?= ($filter_status == 'devuelto') ? 'selected' : '' ?>>
                                    Devuelto</option>
                                <option value="reenvio" <?= ($filter_status == 'reenvio') ? 'selected' : '' ?>>
                                    Reenvío</option>
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
                            <label class="form-label">Fecha (inicio) desde</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                value="<?= esc($filter_date_from) ?>">
                        </div>

                        <!-- Fecha hasta -->
                        <div class="col-md-2">
                            <label class="form-label">Fecha (inicio) hasta</label>
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
                <div class="d-flex justify-content-center mt-3">
                    <?= $pager->links('default', 'bitacora_pagination') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        /* -----------------------------------------------------------
         * SELECT2 – Vendedores
         * ----------------------------------------------------------- */
        $('#seller_id').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#formPaquete'), // 👈 CLAVE
            placeholder: '🔍 Buscar vendedor...',
            allowClear: true,
            minimumInputLength: 2,
            width: '100%',
            language: {
                inputTooShort: function(args) {
                    let remaining = args.minimum - args.input.length;
                    return `Por favor ingrese ${remaining} caracter${remaining === 1 ? '' : 'es'} o más`;
                },
                searching: function() {
                    return "Buscando...";
                },
                noResults: function() {
                    return "No se encontraron resultados";
                }
            },
            ajax: {
                url: '<?= base_url('sellers-search') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: function(data, params) {
                    let results = data || [];
                    return {
                        results
                    };
                },
                cache: true
            }
        });

        // Interceptar SOLO los forms de agregar destino
        $('#branch').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar sucursal...',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: branchSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.branch_name
                        }))
                    };
                }
            }
        }).trigger('change'); // <-- Esta línea hace que Select2 lea el option inicial

        // Interceptar el envío del form de agregar destino
        $("form[action*='packages-setDestino']").on("submit", function(e) {
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
<script>
    document.querySelectorAll('.btn-devolver').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const packageId = this.dataset.id;
            const foto = this.dataset.foto;

            // Construir URL de la foto
            let fotoUrl = '';
            if (foto && foto.trim() !== '') {
                fotoUrl = "<?= base_url('upload/paquetes') ?>/" + foto;
            } else {
                fotoUrl = "<?= base_url('upload/no-image.png') ?>";
            }

            Swal.fire({
                title: '¿Devolver paquete?',
                html: `
                <p>Este paquete se marcará como devuelto.</p>
                <img src="${fotoUrl}" 
                     style="max-width: 200px; border-radius: 10px; margin-top: 10px;" />
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, devolver',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            }).then((result) => {

                if (result.isConfirmed) {

                    fetch('<?= base_url("packages-devolver") ?>/' + packageId, {
                            method: 'POST'
                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.status === "ok") {
                                Swal.fire(
                                    '¡Devuelto!',
                                    'El paquete fue marcado como devuelto.',
                                    'success'
                                ).then(() => location.reload());
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al devolver el paquete.',
                                    'error'
                                );
                            }
                        });
                }

            });

        });
    });
</script>
<script>
    document.querySelectorAll('.btn-entregar-casillero').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            const packageId = this.dataset.id;
            const foto = this.dataset.foto;
            const valor = parseFloat(this.dataset.valor || 0).toFixed(2);

            let fotoUrl = foto && foto.trim() !== '' ?
                "<?= base_url('upload/paquetes') ?>/" + foto :
                "<?= base_url('upload/no-image.png') ?>";

            Swal.fire({
                title: '¿Entregar paquete de casillero?',
                html: `
                <p>Este paquete se marcará como entregado.</p>
                <img src="${fotoUrl}" style="max-width: 200px; border-radius: 10px; margin-bottom: 15px;" />

                <div class="alert alert-success text-center fw-bold">
                    Valor del paquete: $${valor}
                </div>

                <div style="text-align:left">
                    <label class="fw-bold mb-1">Cuenta donde se recibió el pago</label>
                    <select id="cuenta_asignada" class="form-control select2-account" style="width:100%">
                        <option></option>
                    </select>
                </div>
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, entregar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#73a92eff',
                cancelButtonColor: '#d33',

                didOpen: () => {
                    // 🔹 Inicializar Select2 dentro del SweetAlert
                    $('#cuenta_asignada').select2({
                        dropdownParent: $('.swal2-popup'),
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: 'Buscar cuenta...',
                        allowClear: true,
                        minimumInputLength: 1,
                        language: {
                            inputTooShort: () => 'Ingrese 1 o más caracteres'
                        },
                        ajax: {
                            url: "<?= base_url('accounts-list') ?>",
                            dataType: 'json',
                            delay: 250,
                            data: params => ({
                                q: params.term
                            }),
                            processResults: data => ({
                                results: data.map(item => ({
                                    id: item.id,
                                    text: item.name
                                }))
                            })
                        }
                    });

                    // 🔹 Seleccionar por defecto cuenta ID = 1
                    $.ajax({
                        url: "<?= base_url('accounts-list') ?>",
                        data: {
                            q: 'efectivo'
                        },
                        dataType: 'json',
                        success: function(data) {
                            const cuenta = data.find(item => item.id == 1);
                            if (!cuenta) return;

                            let option = new Option(cuenta.name, cuenta.id, true, true);
                            $('#cuenta_asignada').append(option).trigger('change');
                        }
                    });
                },

                preConfirm: () => {
                    const cuentaId = $('#cuenta_asignada').val();

                    if (!cuentaId) {
                        Swal.showValidationMessage('Debe seleccionar una cuenta');
                        return false;
                    }

                    return {
                        cuenta_id: cuentaId,
                        valor: valor
                    };
                }


            }).then(result => {

                if (result.isConfirmed) {

                    fetch('<?= base_url("packages-entregar") ?>/' + packageId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                cuenta_id: result.value.cuenta_id,
                                valor: result.value.valor
                            })

                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.status === "ok") {
                                Swal.fire(
                                    'Entregado',
                                    'El paquete fue marcado como entregado.',
                                    'success'
                                ).then(() => location.reload());
                            } else {
                                Swal.fire(
                                    'Error',
                                    'No se pudo registrar la entrega.',
                                    'error'
                                );
                            }
                        });
                }

            });

        });
    });
</script>

<script src="<?= base_url('backend/assets/js/scripts_destino_index_pkg.js') ?>"></script>
<script src="<?= base_url('backend/assets/js/scripts_reenvio_index_pkg.js') ?>"></script>
<?= $this->endSection() ?>