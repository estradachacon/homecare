<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    /* Fuerza a que la celda activa se vea verde */
    td.day-active {
        background-color: #c8f7c5 !important;
        /* un verde suave */
        transition: background-color 0.2s ease-in-out;
    }

    table.table td {
        height: 48px;
        /* Ajusta a tu gusto: 40, 50, 60... */
        vertical-align: middle !important;
        /* Centra el contenido verticalmente */
    }

    /* Ajustar padding para dar más espacio dentro del td */
    table.table td {
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }

    /* Centrar el switch dentro de la celda */
    td .day-switch {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        /* Usa toda la altura */
    }

    /* Asegura que no haya padding que rompa el borde */
    .day-switch .form-check-input {
        cursor: pointer;
    }

    /* Para centrar mejor el switch dentro de la celda */
    td .day-switch {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 6px 0;
    }

    .day-container {
        display: inline-block;
        position: relative;
        cursor: pointer;
        width: 28px;
        height: 28px;
        user-select: none;
    }

    /* Ocultar checkbox original */
    .day-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    /* Caja del checkbox */
    .day-checkmark {
        position: absolute;
        top: 0;
        left: 0;
        height: 28px;
        width: 28px;
        background-color: #f8f3f3ff;
        border: #28a745 2px solid;
        border-radius: 7px;
        transition: 0.25s;
    }

    /* Hover */
    .day-container:hover input~.day-checkmark {
        background-color: #f6f4f4ff;
    }

    /* Estados de color según seleccionado */
    .day-container input:checked~.day-checkmark {
        background-color: #28a745;
        /* Verde similar al que me mostraste */
    }

    /* Estilo del “check” interno */
    .day-checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Mostrar el checkmark */
    .day-container input:checked~.day-checkmark:after {
        display: block;
    }

    /* Diseño del check interno */
    .day-container .day-checkmark:after {
        left: 9px;
        top: 5px;
        width: 6px;
        height: 12px;
        border: solid white;
        border-width: 0 3px 3px 0;
        transform: rotate(45deg);
    }

    /* Altura de celda para que se vea bonito */
    table.table td {
        height: 48px !important;
        vertical-align: middle !important;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm rounded-lg">
            <div class="card-header d-flex justify-content-between bg-primary text-white">
                <h4 class="mb-0">Nuevo Punto Fijo</h4>
            </div>
            <div class="card-body">

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Errores de Validación</h4>
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="settledPointForm" action="<?= base_url('settledpoint') ?>" method="post" novalidate>
                    <?= csrf_field() ?>
                    <div class="row">
                        <!-- Nombre/Descripción (Usando textarea con autosize-input) -->
                        <div class="col-md-6 mb-3">
                            <label for="point_name" class="form-label">Nombre del Punto Fijo</label>
                            <!-- Cambiado a textarea para usar la clase autosize-input y la lógica JS -->
                            <textarea name="point_name" id="point_name" class="form-control autosize-input" minlength="3"
                                required><?= old('point_name') ?? '' ?></textarea>
                            <div class="invalid-feedback">
                                El nombre debe tener al menos 3 caracteres.
                            </div>
                        </div>

                        <!-- Ruta -->
                        <div class="col-md-6 mb-3">
                            <label for="ruta_id" class="form-label">Ruta</label>
                            <select name="ruta_id" id="ruta_id" class="form-select" required>
                                <option value="">Seleccione una ruta</option>
                                <?php if (isset($rutas) && count($rutas) > 0): ?>
                                    <?php
                                    $selectedRuta = old('ruta_id');
                                    foreach ($rutas as $ruta):
                                    ?>
                                        <option value="<?= esc($ruta->id) ?>" <?= $selectedRuta == $ruta->id ? 'selected' : '' ?>>
                                            <?= esc($ruta->route_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar una ruta.</div>
                        </div>
                    </div>

                    <!-- Configuración de días -->
                    <div class="mb-3 p-3 border rounded shadow-sm">
                        <label class="form-label d-block mb-2 text-primary fw-bold">Días activos de configuración</label>
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
                                        $oldDays = session()->getFlashdata('days') ?? []; // Para retener el estado después del error
                                        foreach ($days as $day):
                                        ?>
                                            <td class="p-0 text-center">
                                                <label class="day-container">
                                                    <input type="hidden" name="<?= $day ?>" value="0"> <!-- SIEMPRE manda 0 si no se chequea -->
                                                    <input type="checkbox" name="<?= $day ?>" id="<?= $day ?>" value="1"
                                                        <?= in_array($day, $oldDays) || old($day) == 1 ? 'checked' : '' ?>>
                                                    <span class="day-checkmark"></span>
                                                </label>
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
                            <label for="hora_inicio" class="form-label">Hora de llegada (HH:MM)</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" value="<?= old('hora_inicio') ?? '' ?>" required>
                            <div class="invalid-feedback">Ingrese la hora de llegada.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="hora_fin" class="form-label">Hora de salida (HH:MM)</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control" value="<?= old('hora_fin') ?? '' ?>" required>
                            <div class="invalid-feedback">Ingrese la hora de salida.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-3 border-top">
                        <a href="<?= base_url('settledpoint') ?>" class="btn btn-secondary m-1">Cancelar</a>
                        <button type="submit" class="btn btn-success m-1">Guardar Punto Fijo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('settledPointForm');
        // Usamos querySelector para el textarea que ahora es 'point_name'
        const autosizeTextarea = document.querySelector('.autosize-input');
        const daySwitches = document.querySelectorAll('.day-switch input[type="checkbox"]');

        // --- Lógica de Autoajuste del textarea ---
        const resizeTextarea = (element) => {
            if (element) {
                element.style.height = 'auto';
                element.style.height = element.scrollHeight + 'px';
            }
        };

        if (autosizeTextarea) {
            // Ajuste inicial por si carga con contenido antiguo (old())
            resizeTextarea(autosizeTextarea);
            autosizeTextarea.addEventListener('input', () => resizeTextarea(autosizeTextarea));
        }

        // --- Lógica de Coloreo de celdas para switches activos ---
        daySwitches.forEach(sw => {
            const cell = sw.closest('td');

            // Función para alternar la clase 'day-active'
            const toggleActiveClass = () => {
                cell.classList.toggle('day-active', sw.checked);
            };

            // Aplicar el color inicial si ya está chequeado (ej. por old data)
            toggleActiveClass();

            // Escuchar el cambio
            sw.addEventListener('change', toggleActiveClass);
        });

        // --- Lógica de Validación del Formulario (Bootstrap) ---
        form.addEventListener('submit', function(event) {
            // Validación de Bootstrap 5
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Validación adicional para asegurar que al menos un día esté activo (opcional, pero útil)
            const oneDayActive = Array.from(daySwitches).some(sw => sw.checked);
            if (!oneDayActive) {
                // Si necesitas que al menos un día sea obligatorio, aquí lo validarías
                // Pero por ahora, solo validamos lo nativo de HTML y Bootstrap.
            }

            form.classList.add('was-validated');
            // Si todo está bien, el form se envía automáticamente.
        });
    });
</script>
<?= $this->endSection() ?>