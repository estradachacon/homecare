<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    /* Estilos existentes */
    #filter_motorista,
    #filter_status {
        width: 100%;
        height: 38px;
        padding: 8px 10px;
        font-size: 14px;
        border-radius: 4px;
    }

    .table-primary {
        background-color: #d1ecf1 !important;
        /* celeste claro */
        font-weight: 600;
    }

    /* --- NUEVOS ESTILOS PARA REDUCIR ALTURA DE FILAS --- */
    /* Reduce el padding de las celdas del encabezado (th) y del cuerpo (td) */
    .table-striped th,
    .table-striped td {
        /* Ejemplo: 4px arriba/abajo, 8px izquierda/derecha. Ajusta a tu gusto */
        padding: 8px 10px;
        vertical-align: middle;
        /* Opcional: Centra verticalmente el contenido */
        font-size: 14px;
        /* Opcional: Reduce un poco el tamaño de la fuente si lo necesitas */
    }

    /* Para las filas de grupo con color (table-primary) */
    .table-primary td {
        padding: 8px 10px !important;
        /* Asegúrate de que las filas de grupo también sean compactas */
    }

    /* Puedes quitar el line-height: 10px; de la tabla y tbody si usas el padding */
    .table {
        /* line-height: 1.5; o simplemente no lo especifiques y usa el padding */
        line-height: 1.2;
        /* Una línea más apretada puede ayudar */
    }

    /* Quitamos el line-height que tenías en el tbody si ya se aplica a la tabla/td */
    /* .table-striped tbody { line-height: 10px; } */
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card">

            <div class="card-header d-flex">
                <h4 class="header-title">Listado de Trackings</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('tracking/new') ?>">
                    <i class="fa-solid fa-plus"></i> Nuevo Tracking
                </a>
            </div>

            <div class="card-body">

                <!-- Formulario de filtros REORGANIZADO -->
                <form method="GET" action="<?= base_url('tracking') ?>" class="mb-4">

                    <!-- PRIMERA FILA: Filtros de selección (Motorista, Ruta, Estado) -->
                    <div class="row">
                        <!-- Filtro motorista -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_motorista">Motorista</label>
                            <select name="motorista_id" id="filter_motorista" class="form-control">
                                <option value="">-- Todos --</option>
                                <?php foreach ($motoristas as $m): ?>
                                    <option value="<?= $m['id'] ?>"
                                        <?= ($filter_motorista_id == $m['id']) ? 'selected' : '' ?>>
                                        <?= esc($m['user_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro ruta -->
                        <div class="col-md-3 mb-3">
                            <label for="filter_ruta">Ruta</label>
                            <select name="ruta_id" id="filter_ruta" class="form-control">
                                <option value="">-- Todas --</option>
                                <?php foreach ($rutas as $r): ?>
                                    <option value="<?= $r->id ?>"
                                        <?= ($filter_ruta_id == $r->id) ? 'selected' : '' ?>>
                                        <?= esc($r->route_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtro múltiple estado -->
                        <div class="col-md-3 mb-3">
                            <label>Estado</label>
                            <select class="form-select js-status-select" name="status[]" multiple>
                                <?php foreach ($statusList as $st): ?>
                                    <option value="<?= $st ?>"
                                        <?= (!empty($filter_status) && in_array($st, $filter_status)) ? 'selected' : '' ?>>
                                        <?= ucfirst($st) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- SEGUNDA FILA: Fechas, Búsqueda y Botones -->
                    <div class="row align-items-end">

                        <!-- Fecha Desde -->
                        <div class="col-md-3">
                            <label>Fecha Desde</label>
                            <input type="date" name="date_from" class="form-control" value="<?= esc($filter_date_from) ?>">
                        </div>

                        <!-- Fecha Fin -->
                        <div class="col-md-3">
                            <label>Fecha Hasta</label>
                            <input type="date" name="date_to" class="form-control" value="<?= esc($filter_date_to) ?>">
                        </div>

                        <!-- Búsqueda por ID -->
                        <div class="col-md-3">
                            <label>Buscar ID</label>
                            <input type="text" name="search_id" class="form-control" placeholder="ID Tracking"
                                value="<?= esc($filter_search_id) ?>">
                        </div>

                        <!-- Botones de Acción -->
                        <div class="col-md-3 d-flex">
                            <button type="submit" class="btn btn-primary w-50 me-2">Filtrar</button>
                            <a href="<?= base_url('tracking') ?>" class="btn btn-secondary w-50 me-2">Limpiar</a>
                        </div>
                    </div>
                </form>

                <!-- TABLA -->
                <table class="table table-striped table-bordered table-hover" style="line-height: 20px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Motorista</th>
                            <th>Ruta</th>
                            <th>Fecha de ruta</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody style="line-height: 10px;">
                        <?php if (!empty($trackings)): ?>

                            <?php
                            $currentDate = null;
                            foreach ($trackings as $t):
                                $trackingDate = date('d/m/Y', strtotime($t->date));
                                // Cada vez que cambia la fecha, mostramos una fila de grupo
                                if ($currentDate !== $trackingDate):
                                    $currentDate = $trackingDate;
                            ?>
                                    <tr class="table-primary">
                                        <td colspan="6" class="fw-bold">
                                            Fecha: <?= $currentDate ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <tr>
                                    <td><?= $t->id ?></td>
                                    <td><?= esc($t->motorista_name) ?></td>
                                    <td><?= esc($t->route_name) ?></td>
                                    <td><?= esc($trackingDate) ?></td>
                                    <td><?= statusBadge($t->status ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                data-toggle="dropdown">Acciones</button>
                                            <ul class="dropdown-menu">

                                                <li>
                                                    <a class="dropdown-item" href="<?= base_url('tracking/' . $t->id) ?>">
                                                        <i class="fa-solid fa-eye"></i> Ver Tracking
                                                    </a>
                                                </li>

                                                <!-- MODIFICACIÓN: Ocultar si el estado es 'finalizado' -->
                                                <?php if ($t->status !== 'finalizado'): ?>
                                                    <a class="dropdown-item" href="<?= base_url('tracking-rendicion/' . $t->id) ?>">
                                                        <i class="fa-solid fa-truck"></i> Seguimiento
                                                    </a>
                                                    </li>
                                                <?php endif; ?>

                                                <li>
                                                    <a class="dropdown-item" href="<?= base_url('tracking/edit/' . $t->id) ?>">
                                                        <i class="fa-solid fa-pencil"></i> Editar
                                                    </a>
                                                </li>

                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay trackings registrados</td>
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

<!-- Select2 -->
<script>
    $(document).ready(function() {
        $('#filter_motorista').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar motorista...',
            allowClear: true,
            width: '100%'
        });

        $('#filter_status').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar estatus...',
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.js-status-select').select2({
            width: '100%',
            placeholder: "Selecciona estado(s)...",
            allowClear: true
        });
    });
</script>

<?= $this->endSection() ?>