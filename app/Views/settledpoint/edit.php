<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .autosize-input {
        overflow: hidden;
        /* oculta scrollbar vertical */
        resize: none;
        /* evita que el usuario cambie tamaño manualmente */
        min-height: 38px;
        /* altura mínima */
        line-height: 1.5;
        /* buena legibilidad */
        transition: height 0.2s ease;
        /* animación suave al crecer */
        max-height: 146px;
        /* aprox 5 líneas */
    }

    .table td .form-check {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .form-check-input {
        position: relative;
        top: -1px;
        /* o -2px si sigue un poco descentrado */
    }


    /* centrado del switch en su celda */
    .day-switch {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 40px;
    }

    /* color del switch al estar activo (verde bootstrap) */
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }

    /* colorcito de fondo en la celda activa */
    .table td.day-active {
        background-color: #e8f5e9 !important;
        transition: background-color 0.3s ease;
    }

    /* más compacto visualmente */
    .table th,
    .table td {
        vertical-align: middle !important;
        padding: 0.4rem;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title mb-0">Editar punto fijo: <?= esc($settledPoint->point_name) ?></h4>
            </div>

            <div class="card-body">
                <!-- Formulario apuntando al método update -->
                <form action="<?= base_url('settledpoint/update/' . $settledPoint->id) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="point_name" class="form-label">Nombre del Punto fijo</label>
                            <input
                                type="text"
                                class="form-control"
                                id="point_name"
                                name="point_name"
                                value="<?= esc($settledPoint->point_name) ?>"
                                required>
                        </div>

                        <!-- Ruta -->
                        <div class="col-md-6 mb-3">
                            <label for="ruta_id" class="form-label">Ruta</label>
                            <select name="ruta_id" id="ruta_id" class="form-select" required>
                                <option value="">Seleccione una ruta</option>
                                <?php if (isset($rutas) && count($rutas) > 0): ?>
                                    <?php foreach ($rutas as $ruta): ?>
                                        <option value="<?= esc($ruta->id) ?>" 
                                            <?= $settledPoint->ruta_id == $ruta->id ? 'selected' : '' ?>>
                                            <?= esc($ruta->route_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar una ruta.</div>
                        </div>
                    </div>

                    <!-- Configuración de días -->
                    <div class="mb-3">
                        <label class="form-label d-block mb-2">Días activos</label>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Lun</th>
                                        <th>Mar</th>
                                        <th>Mié</th>
                                        <th>Jue</th>
                                        <th>Vie</th>
                                        <th>Sáb</th>
                                        <th>Dom</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php
                                        $days = ['mon', 'tus', 'wen', 'thu', 'fri', 'sat', 'sun'];
                                        foreach ($days as $day):
                                            $checked = !empty($settledPoint->$day) && $settledPoint->$day == 1 ? 'checked' : '';
                                        ?>
                                            <td>
                                                <div class="form-check form-switch day-switch">
                                                    <input type="hidden" name="<?= $day ?>" value="0">
                                                    <input type="checkbox" class="form-check-input" id="<?= $day ?>" name="<?= $day ?>" value="1" <?= $checked ?>>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-text">Activá los días en que el punto fijo configuraría los paquetes.</div>
                    </div>

                    <!-- Horario -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hora_inicio" class="form-label">Hora de llegada</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" 
                                   value="<?= esc($settledPoint->hora_inicio) ?>" required>
                            <div class="invalid-feedback">Ingrese la hora de llegada.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="hora_fin" class="form-label">Hora de salida</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control" 
                                   value="<?= esc($settledPoint->hora_fin) ?>" required>
                            <div class="invalid-feedback">Ingrese la hora de salida.</div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Guardar cambios</button>
                    </div>
                </form>
            </div> 
        </div> 
    </div> 
</div> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const switches = document.querySelectorAll('.day-switch input[type="checkbox"]');

        switches.forEach(sw => {
            const cell = sw.closest('td');
            if (sw.checked) cell.classList.add('day-active');

            sw.addEventListener('change', () => {
                cell.classList.toggle('day-active', sw.checked);
            });
        });
    });
</script>
<?= $this->endSection() ?>
