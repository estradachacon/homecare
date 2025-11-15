<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    /* ===============================
   🎨 ESTILOS GENERALES FORMULARIO
   =============================== */

    /* --- Select2 (aplica a todos los campos con theme bootstrap4) --- */
    .select2-container--bootstrap4 .select2-selection {
        display: flex !important;
        align-items: center;
        height: calc(2.5rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1rem !important;
        color: #212529 !important;
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }

    /* Placeholder para todos los Select2 */
    .select2-container--bootstrap4 .select2-selection__placeholder {
        color: #6c757d !important;
    }

    /* Estado focus igual al input Bootstrap */
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #86b7fe !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    /* --- Contenedores que aparecen/ocultan dinámicamente --- */
    .retiro-paquete-container,
    .punto-fijo-container,
    .tipo-entrega-container,
    .destino-container {
        display: none;
        opacity: 0;
        transform: scaleY(0.95);
        transition: all 0.3s ease;
    }

    /* Estado visible con animación */
    .retiro-paquete-container.show,
    .punto-fijo-container.show,
    .tipo-entrega-container.show,
    .destino-container.show {
        display: block;
        opacity: 1;
        transform: scaleY(1);
    }

    /* --- Textarea autosize (para campo "retiro del paquete") --- */
    .autosize-input {
        overflow: hidden;
        /* oculta scrollbar vertical */
        resize: none;
        /* evita que el usuario cambie el tamaño manualmente */
        min-height: 38px;
        /* altura mínima */
        line-height: 1.5;
        /* buena legibilidad */
        transition: height 0.2s ease;
        /* animación suave */
        max-height: 146px;
        /* aprox 5 líneas */
    }

    /* --- Línea divisoria centrada entre secciones --- */
    .line-center {
        width: 95%;
        /* ancho ajustable (entre 50%–90%) */
        height: 2px;
        background-color: #dee2e6;
        /* gris claro tipo Bootstrap */
        margin: 2rem auto;
        /* centra horizontalmente */
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    /* Efecto sutil de brillo (opcional) */
    .line-center::after {
        content: "";
        display: block;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, transparent, #dee2e6, transparent);
    }

    /* --- Estilos responsivos básicos --- */
    @media (max-width: 768px) {
        .line-center {
            width: 95%;
        }
    }

    /* --- Botones --- */
    .btn-success {
        font-weight: 500;
        border-radius: 0.375rem;
        padding: 0.5rem 1.25rem;
    }

    /* --- Encabezado de tarjeta --- */
    .card-header.bg-primary {
        background-color: #007bff !important;
    }

    .header-title {
        font-weight: 600;
    }

    /* --- Campos de formulario (ajuste visual general) --- */
    .form-label {
        font-weight: 500;
        color: #495057;
    }

    .form-control,
    .form-select {
        border-radius: 0.375rem !important;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .toggle-pill {
        display: inline-flex;
        border: 1px solid #ced4da;
        border-radius: 30px;
        overflow: hidden;
    }

    .toggle-pill input {
        display: none;
    }

    .toggle-pill label {
        padding: 6px 16px;
        margin: 0;
        cursor: pointer;
        transition: all .2s ease;
    }

    .toggle-pill input:checked+label {
        background-color: #48b563ff;
        color: white;
    }

    /* ===============================
   🎨 ESTILOS DATERANGEPICKER
   =============================== */

    /* Ocultar días de otros meses */
    .daterangepicker td.off-start,
    .daterangepicker td.off-end,
    .daterangepicker td.off.in-range,
.daterangepicker td.off,
.daterangepicker td.off.disabled,
.disabled-day {
    background-color: #f5f5f5 !important;  /* Gris muy claro */
    color: #cfcdcdff !important;             /* Texto gris suave */
    opacity: 0.22 !important;              /* Efecto "borrador" */
    cursor: default !important;
    pointer-events: none !important;
    border-radius: 6px;
    text-decoration: none !important;
}

    /* Días válidos (seleccionables) */
    .daterangepicker td.available {
        background-color: #e9f7ff !important;
        /* Azul muy suave */
        color: #0a3d62 !important;
        /* Azul oscuro */
        font-weight: 500;
        border-radius: 6px;
        cursor: pointer !important;
        transition: background-color .2s ease, transform .15s ease;
    }

    /* Hover de días válidos */
    .daterangepicker td.available:hover {
        background-color: #d4efff !important;
        /* Un poco más azul */
        transform: scale(1.08);
        /* Efecto de elevación */
        z-index: 2;
    }

    /* Día seleccionado (start y end) */
    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background-color: #0d6efd !important;
        /* Azul Bootstrap */
        color: white !important;
        font-weight: 700;
        transform: scale(1.1);
        border-radius: 8px !important;
    }

    /* Rango intermedio (cuando arrastras fechas) */
    .daterangepicker td.in-range {
        background-color: #cbe7ff !important;
        /* Azul tenue */
        color: #0d3b66 !important;
    }

    /* Días inválidos (bloqueados por lógica) */
    .daterangepicker td.off.disabled,
    .disabled-day {
        background-color: #d8d3d3ff !important;
        color: #8c8989ff !important;
        opacity: .55 !important;
        text-decoration: none !important;
        pointer-events: none !important;
        border-radius: 4px;
    }

    .drop-area {
        border: 2px dashed #7a6fff;
        border-radius: 14px;
        padding: 32px;
        background: linear-gradient(135deg, #f8f9ff 0%, #f1f2ff 100%);
        text-align: center;
        cursor: pointer;
        transition: 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }

    .drop-area:hover {
        background: linear-gradient(135deg, #eef0ff 0%, #e7e9ff 100%);
        border-color: #5b50ff;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .drop-icon {
        font-size: 50px;
        margin-bottom: 12px;
    }

    .drop-text {
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
        font-size: 15px;
    }

    .preview-img {
        margin-top: 18px;
        width: 100%;
        max-width: 270px;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.07);
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class=" header-title mb-0">Registrar nuevo paquete</h5>
                <a href="<?= base_url('packages') ?>" class="btn btn-light btn-sm">Volver</a>
            </div>
            <div class="card-body">
                <form action="<?= base_url('packages/store') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <!-- Select del vendedor -->
                        <div class="col-md-6 mb-3">
                            <label for="seller_id" class="form-label">Vendedor</label>
                            <select id="seller_id" name="seller_id" class="form-select" style="width: 100%;" required>
                                <option value=""></option>
                            </select>
                            <small class="form-text text-muted">Escribí para buscar o crear un nuevo vendedor.</small>
                        </div>

                        <!-- Cliente -->
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <input type="text" name="cliente" class="form-control" required>
                        </div>

                        <!-- Tipo de servicio -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo de servicio</label>
                            <select name="tipo_servicio" id="tipo_servicio" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Punto fijo</option>
                                <option value="2">Personalizado</option>
                                <option value="3">Recolecta de paquete</option>
                                <option value="4">Casillero</option>
                            </select>
                        </div>

                        <!-- Punto de retiro -->
                        <div class="col-md-6 retiro-paquete-container" id="retiro_paquete_container"
                            style="display: none;">
                            <label class="form-label">Retiro del paquete</label>
                            <textarea id="retiro_paquete" name="retiro_paquete" class="form-control autosize-input"
                                rows="1" placeholder="Lugar de recogida" required></textarea>
                        </div>

                        <!-- Definir tipo de entrega (solo visual, no va a la BD) -->
                        <div class="col-md-6 tipo-entrega-container" id="tipo_entrega_container" style="display: none;">
                            <label for="tipo_entrega" class="form-label">Tipo de entrega</label>
                            <select id="tipo_entrega" class="form-select">
                                <option value="">Seleccione destino de entrega</option>
                                <option value="personalizada">Entrega personalizada</option>
                                <option value="5">Entrega en punto fijo</option>
                            </select>
                        </div>

                        <!-- Punto fijo -->
                        <div class="col-md-6 punto-fijo-container" id="punto_fijo_container" style="display: none;">
                            <label class="form-label">Punto fijo de entrega</label>
                            <select name="id_puntofijo" class="form-select" id="puntofijo_select" style="width: 100%;">
                                <option value="">Seleccione un punto fijo</option>
                            </select>
                        </div>

                        <!--destino-->
                        <div class="col-md-6 destino-container" id="destino_container" style="display: none;">
                            <label for="destino_input" class="form-label">Destino (Dirección de Entrega
                                personalizada)</label>
                            <input type="text" name="destino" class="form-control" id="destino_input"
                                placeholder="Colonia o dirección de destino" required>
                        </div>

                        <div class="form-divider line-center"></div>

                        <!-- Fechas -->
                        <div class="col-md-6">
                            <label class="form-label">Fecha de ingreso</label>
                            <input type="date" name="fecha_ingreso" class="form-control" value="<?= date('Y-m-d') ?>"
                                required>
                        </div>

                        <div class="col-md-6" id='fecha_entrega_container' style="display: none;">
                            <label class="form-label">Fecha de entrega</label>
                            <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control">
                        </div>

                        <div class="col-md-6 punto-fijo-container" id="fecha_punto_fijo_container"
                            style="display: none;">
                            <label for="fecha_entrega_puntofijo" class="form-label">Fecha de entrega en punto
                                fijo</label>
                            <input type="text" name="fecha_entrega_puntofijo" id="fecha_entrega_puntofijo"
                                class="form-control datepicker" autocomplete="off" />

                        </div>

                        <div class="form-divider line-center"></div>

                        <div class="col-md-3 text-center">
                            <label class="form-label d-block mb-2">Cobro al vendedor</label>
                            <div class="toggle-pill">
                                <input type="radio" id="pagoParcialNo" name="pago_parcial" value="0" checked>
                                <label for="pagoParcialNo">Pago total</label>
                                <input type="radio" id="pagoParcialSi" name="pago_parcial" value="1">
                                <label for="pagoParcialSi">Pago parcial</label>
                            </div>
                        </div>

                        <!-- Flete total -->
                        <div class="col-md-3" id="flete_total_container">
                            <label class="form-label" id="label_flete_total">Total de envío a cobrar ($)</label>
                            <input type="number" step="0.01" name="flete_total" id="flete_total" class="form-control"
                                required>
                        </div>

                        <!-- Flete pagado -->
                        <div class="col-md-3" id="flete_pagado_container" style="display: none;">
                            <label class="form-label">Envío pagado ($)</label>
                            <input type="number" step="0.01" name="flete_pagado" id="flete_pagado" class="form-control">
                        </div>

                        <!-- Flete pendiente -->
                        <div class="col-md-3" id="flete_pendiente_container" style="display: none;">
                            <label class="form-label">Envío pendiente ($)</label>
                            <input type="number" step="0.01" name="flete_pendiente" id="flete_pendiente"
                                class="form-control" readonly>
                        </div>

                        <div class="form-divider line-center"></div>

                        <div class="col-md-4 text-center">
                            <label class="form-label d-block mb-2">¿Es frágil?</label>
                            <div class="toggle-pill">
                                <input type="radio" id="fragilNo" name="fragil" value="0" checked>
                                <label for="fragilNo">No</label>

                                <input type="radio" id="fragilSi" name="fragil" value="1">
                                <label for="fragilSi">Sí</label>
                            </div>
                        </div>

                        <div class="col-md-4 text-center">
                            <label class="form-label d-block mb-2">¿Paquete ya cancelado?</label>
                            <div class="toggle-pill">
                                <input type="radio" id="toggleCobroNo" name="toggleCobro" value="0" checked>
                                <label for="toggleCobroNo">No</label>

                                <input type="radio" id="toggleCobroSi" name="toggleCobro" value="1">
                                <label for="toggleCobroSi">Sí</label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Monto del paquete ($)</label>
                            <input type="number" id="monto_declarado" step="0.01" name="monto" class="form-control"
                                required>
                        </div>

                        <div class="form-divider line-center"></div>

                        <!-- Foto -->
                        <div class="col-md-6 mx-auto">
                            <label class="form-label d-block mb-2">Foto del paquete</label>

                            <div id="drop-area" class="drop-area">
                                <div class="drop-icon">📦</div>
                                <p class="drop-text">Toca aquí para tomar foto o elegir de la galería</p>
                                <small class="text-muted">También puedes arrastrar y soltar una imagen</small>

                                <!-- Vista previa -->
                                <img id="preview" class="preview-img" style="display:none;">
                            </div>

                            <!-- Input real -->
                            <input type="file" id="fileInput" name="foto" accept="image/*" capture="environment"
                                class="d-none">
                        </div>


                        <div class="form-divider line-center"></div>

                        <!-- Comentarios -->
                        <div class="col-12">
                            <label class="form-label">Comentarios</label>
                            <textarea name="comentarios" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Usuario -->
                        <div class="col-md-12">
                            <input type="hidden" name="user_id" value="<?= session('id') ?>">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4">Guardar paquete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreateSeller" tabindex="-1" role="dialog" aria-labelledby="modalCreateSellerLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formCreateSeller">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear nuevo vendedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="seller">Nombre</label>
                        <input type="text" id="seller" name="seller" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tel_seller">Teléfono</label>
                        <input type="text" id="tel_seller" name="tel_seller" class="form-control" minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $('#formCreateSeller').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '<?= base_url('sellers/create-ajax') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#modalCreateSeller').modal('hide');

                    // Insertar vendedor en el select2 y seleccionarlo
                    let newOption = new Option(response.data.text, response.data.id, true, true);
                    $('#seller_id').append(newOption).trigger('change');

                    Swal.fire('Éxito', 'Vendedor creado y seleccionado.', 'success');
                } else {
                    Swal.fire('Error', response.message || 'No se pudo crear el vendedor.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Ocurrió un error en la petición.', 'error');
            }
        });
    });
</script>
<script>
    window.addEventListener('load', function () {

        // Inicializar Select2
        $('#seller_id').select2({
            theme: 'bootstrap4',
            placeholder: '🔍 Buscar vendedor...',
            allowClear: true,
            minimumInputLength: 2,
            width: '100%',
            ajax: {
                url: '<?= base_url('sellers/search') ?>', // <-- corregido
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data, params) {
                    let results = data || [];

                    // Si no hay resultados, mostrar opción para crear nuevo
                    if (results.length === 0 && params.term && params.term.trim() !== '') {
                        results.push({
                            id: 'create_new',
                            text: '➕ Crear nuevo vendedor'
                        });
                    }

                    return {
                        results: results
                    };
                },

                cache: true
            },
            language: {
                inputTooShort: () => 'Escribí para buscar...',
                searching: () => 'Buscando...',
                noResults: () => 'No se encontraron vendedores'
            }
        });

        // Si selecciona "Crear nuevo vendedor"
        $('#seller_id').on('select2:select', function (e) {
            const selected = e.params.data;
            if (selected.id === 'create_new') {
                $('#seller_id').val(null).trigger('change');
                $('#modalCreateSeller').modal('show');
            }
        });

        // Guardar nuevo vendedor vía AJAX
        $('#formCreateSeller').on('submit', function (e) {
            e.preventDefault();

            $.ajax({
                url: '<?= base_url('sellers/create-ajax') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $('#modalCreateSeller').modal('hide');

                        const newOption = new Option(response.data.text, response.data.id, true, true);
                        $('#seller_id').append(newOption).trigger('change');

                        Swal.fire('Éxito', 'Vendedor creado y seleccionado.', 'success');
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo crear el vendedor.', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Ocurrió un error en la petición.', 'error');
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar Select2 del punto fijo (ya lo tenés)
        const userId = document.querySelector('[name="user_id"]').value;
        $('#puntofijo_select').select2({
            theme: 'bootstrap4',
            placeholder: '🔍 Buscar punto fijo...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '<?= base_url('settledPoints/getList') ?>',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.point_name
                        }))
                    };
                },
                cache: true
            },
            language: {
                inputTooShort: () => 'Escribí para buscar...',
                searching: () => 'Buscando...',
                noResults: () => 'No se encontraron puntos fijos'
            }
        });

        // ✅ Inicializar DateRangePicker como selector de una sola fecha
        $('#fecha_entrega_puntofijo').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                firstDay: 1
            }
        });
        $('#puntofijo_select').on('change', function () {
            const puntoId = $(this).val();
            const dateInput = $('#fecha_entrega_puntofijo');

            if (!puntoId) {
                dateInput.val('');
                dateInput.data('daterangepicker').setStartDate(moment());
                return;
            }

            $.ajax({
                url: `<?= base_url('settledPoints/getDays') ?>/${puntoId}`,
                method: 'GET',
                dataType: 'json',
                success: function (days) {
                    const allowedDays = [];
                    if (days.sun) allowedDays.push(0);
                    if (days.mon) allowedDays.push(1);
                    if (days.tus) allowedDays.push(2);
                    if (days.wen) allowedDays.push(3);
                    if (days.thu) allowedDays.push(4);
                    if (days.fri) allowedDays.push(5);
                    if (days.sat) allowedDays.push(6);

                    // Buscar el primer día válido a partir de hoy
                    let nextValidDate = moment();
                    for (let i = 0; i < 14; i++) {
                        if (allowedDays.includes(nextValidDate.day())) break;
                        nextValidDate.add(1, 'days');
                    }

                    // Reinicializar el calendario
                    dateInput.data('daterangepicker')?.remove();

                    dateInput.daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        autoApply: true, // ✅ aplica al hacer clic
                        startDate: nextValidDate,
                        autoUpdateInput: true,
                        isInvalidDate: function (date) {
                            return !allowedDays.includes(date.day());
                        },
                        locale: {
                            format: 'YYYY-MM-DD',
                            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                            monthNames: [
                                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                            ],
                            firstDay: 1
                        }
                    });

                    // Rellenar con la fecha sugerida
                    dateInput.val(nextValidDate.format('YYYY-MM-DD'));

                },
                error: function () {
                    console.error('Error al cargar días del punto fijo');
                }
            });
        });



        // ✅ Actualizar el input cuando se selecciona una fecha
        $('#fecha_entrega_puntofijo').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
    });
</script>
<script src="<?= base_url('backend/assets/js/scripts_packaging.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const form = document.querySelector('form[action="<?= base_url('packages/store') ?>"]');

        if (form) {

            form.addEventListener('submit', function (event) {

                event.preventDefault();

                const formData = new FormData(form);
                const dataObject = {};

                for (let [key, value] of formData.entries()) {

                    if (key === 'foto') {

                        // Caso 1: sí hay archivo
                        if (value instanceof File && value.name) {
                            dataObject[key] = value.name;
                        }
                        // Caso 2: no hay archivo seleccionado
                        else {
                            dataObject[key] = null; // o "sin-foto"
                        }

                    } else {
                        dataObject[key] = value;
                    }
                }

                console.log("Objeto capturado:", dataObject);

                // === Lógica de envío ===
                const enviarForm = document.getElementById('enviarFormCheck');

                if (enviarForm && enviarForm.checked) {
                    console.log("🟢 Enviando formulario...");
                    form.submit();
                    return;
                }

                // === Copiar al portapapeles ===
                const jsonText = JSON.stringify(dataObject, null, 2);

                if (!navigator.clipboard || !navigator.clipboard.writeText) {
                    alert("❌ Clipboard no soportado por este navegador");
                    return;
                }

                navigator.clipboard.writeText(jsonText)
                    .then(() => {
                        alert("📋 Objeto copiado al portapapeles correctamente.");
                    })
                    .catch(err => {
                        console.error("Error al copiar:", err);
                        alert("❌ No se pudo copiar al portapapeles.");
                    });

            });
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropArea = document.getElementById("drop-area");
        const fileInput = document.getElementById("fileInput");
        const preview = document.getElementById("preview");

        // Abrir input al hacer click
        dropArea.addEventListener("click", () => fileInput.click());

        // Previsualizar imagen
        fileInput.addEventListener("change", function () {
            mostrarPreview(this.files[0]);
        });

        // Arrastrar archivos
        dropArea.addEventListener("dragover", function (e) {
            e.preventDefault();
            dropArea.classList.add("dragover");
        });
        dropArea.addEventListener("dragleave", function () {
            dropArea.classList.remove("dragover");
        });
        dropArea.addEventListener("drop", function (e) {
            e.preventDefault();
            dropArea.classList.remove("dragover");

            let file = e.dataTransfer.files[0];
            if (!file) return;

            fileInput.files = e.dataTransfer.files; // Pasarlo al input real
            mostrarPreview(file);
        });

        function mostrarPreview(file) {
            if (!file.type.startsWith("image/")) return;

            let reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?= $this->endSection() ?>