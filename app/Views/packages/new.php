<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    /* Contenedor general de toasts */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    min-height: 50px;    /* para reservar espacio visual */
    z-index: 1050;       /* suficiente para estar arriba de contenido */
    pointer-events: none; /* permite que clics pasen a elementos debajo */
}

/* Cada toast individual */
.toast {
    pointer-events: auto; /* solo el toast intercepta clic si se hace sobre él */
    min-width: 250px;
}

/* Opcional: animación de aparición de toast */
.toast.show {
    opacity: 1;
    transition: opacity 0.5s ease-in-out;
}

</style>
<link rel="stylesheet" href="<?= base_url('backend/assets/css/newpackage.css') ?>">
<script>
    $.ajaxSetup({
        data: {
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }
    });
</script>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class=" header-title mb-0">Registrar nuevo paquete</h5>
                <a href="<?= base_url('packages') ?>" class="btn btn-light btn-sm">Volver</a>
            </div>
            <div class="card-body">
                <form id="formPaquete" enctype="multipart/form-data">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
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
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="flete_pagado"
                                    id="flete_pagado" placeholder="Ingrese monto">
                                <div class="input-group-append">
                                    <button class="btn btn-secondary" type="button" id="btnSetZero">
                                        Pago parcial
                                    </button>
                                </div>
                            </div>
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
                                <p class="drop-text">Toca aquí para tomar foto</p>
                                <small class="text-muted">También puedes arrastrar y soltar una imagen</small>

                                <!-- Vista previa -->
                                <img id="preview" class="preview-img" style="display:none;">
                            </div>

                            <!-- Input real -->
                            <input type="file" id="fileInput" name="foto" accept="image/*" capture="environment"
                                class="d-none" enctype="multipart/form-data">
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
<!-- Contenedor de toast -->
<div class="toast-container" aria-live="polite" aria-atomic="true">
    <div id="successToast" class="toast text-white bg-success" data-delay="2800">
        <div class="toast-header bg-success text-white">
            <strong class="mr-auto">Éxito</strong>
            <small>Ahora</small>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
        </div>
        <div class="toast-body">
            Paquete creado correctamente
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
<script src="/backend/assets/js/scripts_packaging.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
// Evitar que scroll cambie los números
document.querySelectorAll('input[type=number]').forEach(input => {
    input.addEventListener('wheel', function(e){
        e.preventDefault();
    });
});
        /* -----------------------------------------------------------
         * SELECT2 – Vendedores
         * ----------------------------------------------------------- */
        $('#seller_id').select2({
            theme: 'bootstrap4',
            placeholder: '🔍 Buscar vendedor...',
            allowClear: true,
            minimumInputLength: 2,
            width: '100%',
            language: {
                inputTooShort: function (args) {
                    let remaining = args.minimum - args.input.length;
                    return `Por favor ingrese ${remaining} caracter${remaining === 1 ? '' : 'es'} o más`;
                },
                searching: function () {
                    return "Buscando...";
                },
                noResults: function () {
                    return "No se encontraron resultados";
                }
            },
            ajax: {
                url: '<?= base_url('sellers/search') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: function (data, params) {
                    let results = data || [];
                    if (results.length === 0 && params.term?.trim() !== '') {
                        results.push({ id: 'create_new', text: '➕ Crear nuevo vendedor' });
                    }
                    return { results };
                },
                cache: true
            }
        });

        $('#seller_id').on('select2:select', function (e) {
            const selected = e.params.data;
            if (selected.id === 'create_new') {
                $('#seller_id').val(null).trigger('change');
                $('#modalCreateSeller').modal('show');
            }
        });

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
                        const option = new Option(response.data.text, response.data.id, true, true);
                        $('#seller_id').append(option).trigger('change');
                        Swal.fire('Éxito', 'Vendedor creado correctamente.', 'success');
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo crear.', 'error');
                    }
                },
                error: () => Swal.fire('Error', 'Error de petición.', 'error')
            });
        });

        /* -----------------------------------------------------------
         * SELECT2 – Punto fijo
         * ----------------------------------------------------------- */
        $('#puntofijo_select').select2({
            theme: 'bootstrap4',
            placeholder: '🔍 Buscar punto fijo...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '<?= base_url('settledPoints/getList') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(item => ({ id: item.id, text: item.point_name }))
                })
            }
        });

        /* -----------------------------------------------------------
         * DATERANGEPICKER – Punto fijo
         * ----------------------------------------------------------- */
        $('#puntofijo_select').on('change', function () {
            const puntoId = $(this).val();
            const dateInput = $('#fecha_entrega_puntofijo');

            if (!puntoId) return dateInput.val('');

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

                    let nextValid = moment();
                    for (let i = 0; i < 14; i++) {
                        if (allowedDays.includes(nextValid.day())) break;
                        nextValid.add(1, 'days');
                    }

                    dateInput.data('daterangepicker')?.remove();

                    dateInput.daterangepicker({
                        singleDatePicker: true,
                        showDropdowns: true,
                        autoApply: true,
                        startDate: nextValid,
                        autoUpdateInput: true,
                        isInvalidDate: date => !allowedDays.includes(date.day()),
                        locale: { format: 'YYYY-MM-DD', firstDay: 1 }
                    });

                    dateInput.val(nextValid.format('YYYY-MM-DD'));
                }
            });
        });

        /* -----------------------------------------------------------
         * DROP AREA – Foto
         * ----------------------------------------------------------- */
        const dropArea = document.getElementById("drop-area");
        const fileInput = document.getElementById("fileInput");
        const preview = document.getElementById("preview");

        dropArea.addEventListener("click", () => fileInput.click());
        fileInput.addEventListener("change", () => mostrarPreview(fileInput.files[0]));

        dropArea.addEventListener("dragover", e => {
            e.preventDefault();
            dropArea.classList.add("dragover");
        });

        dropArea.addEventListener("dragleave", () =>
            dropArea.classList.remove("dragover")
        );

        dropArea.addEventListener("drop", e => {
            e.preventDefault();
            dropArea.classList.remove("dragover");

            let file = e.dataTransfer.files[0];
            if (file) {
                fileInput.files = e.dataTransfer.files;
                mostrarPreview(file);
            }
        });

        function mostrarPreview(file) {
            if (!file?.type.startsWith("image/")) return;
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(file);
        }

        /* -----------------------------------------------------------
         * BOTÓN – Pago parcial / Descontar en Remu
         * ----------------------------------------------------------- */
        const btnSetZero = document.getElementById('btnSetZero');
        const fletePagado = document.getElementById('flete_pagado');
        const fleteTotal = document.getElementById('flete_total');
        const fletePendiente = document.getElementById('flete_pendiente');

        btnSetZero.addEventListener('click', function () {
            if (!fletePagado.disabled) {
                fletePagado.value = "0.00";
                fletePagado.disabled = true;

                const total = parseFloat(fleteTotal.value) || 0;
                fletePendiente.value = total.toFixed(2);

                btnSetZero.textContent = "Descontar en Remu";
                btnSetZero.classList.replace("btn-secondary", "btn-warning");

            } else {
                fletePagado.disabled = false;
                fletePagado.value = "";
                fletePendiente.value = "";

                btnSetZero.textContent = "Pago parcial";
                btnSetZero.classList.replace("btn-warning", "btn-secondary");
            }
        });

        /* -----------------------------------------------------------
         * AJAX – Envío del formulario con barra de progreso
         * ----------------------------------------------------------- */

        const form = document.getElementById("formPaquete");

        form.addEventListener("submit", function (e) {
            e.preventDefault();

            let formData = new FormData(form);

            // SweetAlert de progreso
            Swal.fire({
                title: "Subiendo paquete...",
                html: `
            <div class="progress" style="height: 22px;">
                <div id="uploadProgress" class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: 0%">0%</div>
            </div>
        `,
                allowOutsideClick: false,
                showConfirmButton: false
            });

            let xhr = new XMLHttpRequest();

            // Progreso de subida
            xhr.upload.addEventListener("progress", function (e) {
                if (e.lengthComputable) {
                    let percent = Math.round((e.loaded / e.total) * 100);

                    let bar = document.getElementById("uploadProgress");
                    bar.style.width = percent + "%";
                    bar.textContent = percent + "%";
                }
            });

            // Respuesta del servidor
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {

                    Swal.close();

                    if (xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);

                        if (response.status === "success") {
                            Swal.close();
                            // Redirige al mismo view pero con query param
                            window.location.href = "<?= base_url('/packages/new') ?>?created=1";

                        } else {
                            Swal.fire("Error", response.message, "error");
                        }

                    } else {
                        Swal.fire("Error", "Hubo un problema en el servidor.", "error");
                    }
                }
            };

            xhr.open("POST", "<?= base_url('packages/store') ?>");
            xhr.send(formData);
        });
    });
    $(document).ready(function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('created') === '1') {
            $('#successToast').toast('show');
        }
    });

</script>

<?php if (session()->getFlashdata('success')): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Paquete creado!',
            text: '<?= session()->getFlashdata('success'); ?>',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>