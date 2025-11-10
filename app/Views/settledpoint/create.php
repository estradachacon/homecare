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
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-0">Nuevo Punto Fijo</h4>
            </div>
            <div class="card-body">

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
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
                        <!-- Nombre -->
                        <div class="col-md-6 mb-3">
                            <label for="point_name" class="form-label">Nombre del Punto Fijo</label>
                            <input type="text" name="point_name" id="point_name" class="form-control" minlength="3"
                                required>
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
                                    <?php foreach ($rutas as $ruta): ?>
                                        <option value="<?= esc($ruta->id) ?>">
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
                                        <?php for ($i = 0; $i < 7; $i++): ?>
                                            <td>
                                                <div class="form-check form-switch day-switch">
                                                    <input type="checkbox" class="form-check-input" id="day<?= $i ?>"
                                                        name="days_configuration[]" value="1" checked>
                                                    <input type="hidden" name="days_configuration_hidden[]" value="0">
                                                </div>
                                            </td>
                                        <?php endfor; ?>
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
                            <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                            <div class="invalid-feedback">Ingrese la hora de llegada.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="hora_fin" class="form-label">Hora de salida</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="form-control" required>
                            <div class="invalid-feedback">Ingrese la hora de salida.</div>
                        </div>
                    </div>

                    <div>
                        <a href="<?= base_url('settledpoint') ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('settledPointForm');
        const textarea = document.querySelector('.autosize-input');
        // Seleccionamos todos los checkboxes de la configuración de días
        const daySwitches = document.querySelectorAll('.day-switch input[type="checkbox"]');

        form.addEventListener('submit', async function (event) {
            event.preventDefault(); // Detiene envío real

            // --- Validación Bootstrap ---
            if (!form.checkValidity()) {
                event.stopPropagation();
                form.classList.add('was-validated');
                return; // Detiene si no pasa la validación
            }
            form.classList.add('was-validated');

            // 1. CREAR EL ARRAY DE DÍAS (0s y 1s)
            const days_configuration_array = Array.from(daySwitches).map(sw => sw.checked ? 1 : 0);

            // 2. CAPTURAR EL RESTO DE DATOS
            const formData = new FormData(form);
            const data = {};

            // Agregar todos los campos excepto los 'days_configuration' originales
            // que ahora reemplazaremos por nuestro array simple.
            formData.forEach((value, key) => {
                // Excluimos los inputs de días del formulario HTML
                if (!key.includes('days_configuration')) {
                    data[key] = value;
                }
            });

            // 3. AÑADIR EL ARRAY DE DÍAS SIMPLE AL OBJETO DE DATOS
            // Usaremos el nombre 'days_configuration' que esperaría el controlador.
            data['days_configuration'] = days_configuration_array;

            // Formatear para visualización y portapapeles
            const formatted = JSON.stringify(data, null, 2);

            // --- Intentar copiar al clipboard (Tu código original de copia) ---
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(formatted);
                    alert("📋 Datos copiados al portapapeles:\n\n" + formatted);
                } else {
                    // fallback para HTTP o navegadores viejos
                    const temp = document.createElement('textarea');
                    temp.value = formatted;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                    alert("📋 Datos copiados (fallback):\n\n" + formatted);
                }
            } catch (err) {
                console.error("❌ Error copiando al portapapeles:", err);
                alert("⚠️ No se pudo copiar automáticamente.\nPodés copiarlo manualmente:\n\n" + formatted);
            }
            form.submit();
        });

        // --- Autoajuste del textarea (Tu código original) ---
        if (textarea) {
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            });
        }

        // --- Colorcito para switches activos (Tu código original) ---
        daySwitches.forEach(sw => {
            const cell = sw.closest('td');
            if (sw.checked) cell.classList.add('day-active');
            sw.addEventListener('change', () => {
                cell.classList.toggle('day-active', sw.checked);
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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