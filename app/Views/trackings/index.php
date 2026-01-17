<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    /* Contenedor de filtros con estilo moderno */
    .filter-wrapper {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .filter-wrapper label {
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        margin-bottom: 5px;
        display: block;
    }

    /* Ajustes de tabla para que se vea limpia */
    .table thead th {
        background-color: #f1f3f5;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }

    .table-primary {
        background-color: #e7f3ff !important;
        color: #004085;
    }

    .table td,
    .table th {
        padding: 12px 10px;
        vertical-align: middle;
        font-size: 14px;
    }

    /* Badge para el separador de fecha */
    .date-divider {
        font-size: 14px;
        display: flex;
        align-items: center;
    }

    .date-divider i {
        margin-right: 8px;
    }

    /* Estilo para los select2 */
    .select2-container--bootstrap4 .select2-selection {
        border-radius: 4px;
        min-height: 38px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center">
                <h4 class="header-title mb-0">Listado de Trackings</h4>
                <?php if (tienePermiso('crear_tracking')): ?>
                    <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('tracking/new') ?>">
                        <i class="fa-solid fa-plus"></i> Nuevo Tracking
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="filter-wrapper">
                    <form method="GET" action="<?= base_url('tracking') ?>">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="filter_motorista"><i class="fa-solid fa-user-tie mr-1"></i> Motorista</label>
                                <select name="motorista_id" id="filter_motorista" class="form-control">
                                    <option value="">-- Todos --</option>
                                    <?php foreach ($motoristas as $m): ?>
                                        <option value="<?= $m['id'] ?>" <?= ($filter_motorista_id == $m['id']) ? 'selected' : '' ?>>
                                            <?= esc($m['user_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="filter_ruta"><i class="fa-solid fa-route mr-1"></i> Ruta</label>
                                <select name="ruta_id" id="filter_ruta" class="form-control">
                                    <option value="">-- Todas --</option>
                                    <?php foreach ($rutas as $r): ?>
                                        <option value="<?= $r->id ?>" <?= ($filter_ruta_id == $r->id) ? 'selected' : '' ?>>
                                            <?= esc($r->route_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label><i class="fa-solid fa-layer-group mr-1"></i> Estado</label>
                                <select class="form-control js-status-select" name="status[]" multiple>
                                    <?php foreach ($statusList as $st): ?>
                                        <option value="<?= $st ?>" <?= (!empty($filter_status) && in_array($st, $filter_status)) ? 'selected' : '' ?>>
                                            <?= ucfirst($st) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label><i class="fa-solid fa-hashtag mr-1"></i> Buscar ID</label>
                                <input type="text" name="search_id" class="form-control" placeholder="Ej: 12345" value="<?= esc($filter_search_id) ?>">
                            </div>
                        </div>

                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label><i class="fa-solid fa-calendar-day mr-1"></i> Fecha Desde</label>
                                <input type="date" name="date_from" class="form-control" value="<?= esc($filter_date_from) ?>">
                            </div>

                            <div class="col-md-3">
                                <label><i class="fa-solid fa-calendar-day mr-1"></i> Fecha Hasta</label>
                                <input type="date" name="date_to" class="form-control" value="<?= esc($filter_date_to) ?>">
                            </div>

                            <div class="col-md-6 d-flex justify-content-end align-items-center mt-3 mt-md-0">
                                <a href="<?= base_url('tracking') ?>" class="btn btn-outline-secondary mr-2">
                                    <i class="fa-solid fa-eraser"></i> Limpiar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-magnifying-glass"></i> Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover shadow-sm">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Motorista</th>
                                <th>Ruta</th>
                                <th>Fecha de ruta</th>
                                <th>Estatus</th>
                                <th width="120" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($trackings)): ?>
                                <?php
                                $currentDate = null;
                                foreach ($trackings as $t):
                                    $trackingDate = date('d/m/Y', strtotime($t->date));
                                    if ($currentDate !== $trackingDate):
                                        $currentDate = $trackingDate;
                                ?>
                                        <tr class="table-primary">
                                            <td colspan="6" class="fw-bold">
                                                <div class="date-divider">
                                                    <i class="fa-solid fa-calendar-check"></i>
                                                    <span>Fecha: <?= $currentDate ?></span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <tr>
                                        <td><strong>#<?= $t->id ?></strong></td>
                                        <td><?= esc($t->motorista_name) ?></td>
                                        <td><?= esc($t->route_name) ?></td>
                                        <td><?= esc($trackingDate) ?></td>
                                        <td><?= statusBadge($t->status ?? 'N/A') ?></td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-toggle="dropdown">
                                                    Opciones
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right shadow border-0">
                                                    <li><a class="dropdown-item" href="<?= base_url('tracking/' . $t->id) ?>"><i class="fa-solid fa-eye text-info mr-2"></i> Ver Tracking</a></li>
                                                    <?php if ($t->status !== 'finalizado'): ?>
                                                        <li><a class="dropdown-item" href="<?= base_url('tracking-rendicion/' . $t->id) ?>"><i class="fa-solid fa-truck text-warning mr-2"></i> Seguimiento</a></li>
                                                        <li><a class="dropdown-item" href="<?= base_url('tracking/edit/' . $t->id) ?>"><i class="fa-solid fa-pencil text-primary mr-2"></i> Editar</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No se encontraron resultados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <?= $pager->links('default', 'bitacora_pagination') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.js-status-select, #filter_motorista, #filter_ruta').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: "Seleccionar..."
        });
    });
</script>

<?= $this->endSection() ?>