<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
/* Estilo solo para el select2 del vendedor */
#seller_id + .select2-container--bootstrap4 .select2-selection {
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

/* Placeholder */
#seller_id + .select2-container--bootstrap4 .select2-selection__placeholder {
    color: #6c757d !important;
}

/* Focus igual que input de Bootstrap */
#seller_id + .select2-container--bootstrap4.select2-container--focus .select2-selection {
    border-color: #86b7fe !important;
    outline: 0 !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}
/* Ocultamos el campo inicialmente */
.retiro-paquete-container {
    display: none;
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

/* Cuando se muestra */
.retiro-paquete-container.show {
    display: block;
    opacity: 1;
    transform: translateY(0);
}
.autosize-input {
    overflow: hidden;      /* oculta scrollbar vertical */
    resize: none;          /* evita que el usuario cambie tamaño manualmente */
    min-height: 38px;      /* altura mínima */
    line-height: 1.5;      /* buena legibilidad */
    transition: height 0.2s ease; /* animación suave al crecer */
    max-height: 146px; /* aprox 5 líneas */
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
                            <select id="seller_id" name="seller_id" class="form-select" style="width: 100%;">
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
                        <div class="col-md-6 retiro-paquete-container" id="retiro_paquete_container" style="display: none;">
                            <label class="form-label">Retiro del paquete</label>
                            <textarea id="retiro_paquete" name="retiro_paquete" class="form-control autosize-input" rows="1" placeholder="Lugar de recogida" required></textarea>
                        </div>

                        <!-- Punto fijo -->
                        <div class="col-md-6">
                            <label class="form-label">Punto fijo</label>
                            <select name="id_puntofijo" class="form-select">
                                <option value="">Seleccione un punto fijo</option>
                                <!-- Llenar dinámicamente -->
                            </select>
                        </div>
                        
                        <!-- Destino -->
                        <div class="col-md-6">
                            <label class="form-label">Destino</label>
                            <input type="text" name="destino" class="form-control" placeholder="Ciudad o sucursal destino" required>
                        </div>

                        <!-- Dirección -->
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" class="form-control autosize-input" rows="2"></textarea>
                        </div>

                        <!-- Fechas -->
                        <div class="col-md-6">
                            <label class="form-label">Fecha de ingreso</label>
                            <input type="date" name="fecha_ingreso" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de entrega</label>
                            <input type="date" name="fecha_entrega" class="form-control">
                        </div>

                        <!-- Teléfonos -->
                        <div class="col-md-6">
                            <label class="form-label">Teléfono primario</label>
                            <input type="tel" name="tel_primario" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono opcional</label>
                            <input type="tel" name="tel_opcional" class="form-control">
                        </div>

                        <!-- Fletes -->
                        <div class="col-md-4">
                            <label class="form-label">Flete total ($)</label>
                            <input type="number" step="0.01" name="flete_total" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Flete pagado ($)</label>
                            <input type="number" step="0.01" name="flete_pagado" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Flete pendiente ($)</label>
                            <input type="number" step="0.01" name="flete_pendiente" class="form-control" readonly>
                        </div>

                        <!-- Monto declarado -->
                        <div class="col-md-6">
                            <label class="form-label">Monto declarado ($)</label>
                            <input type="number" step="0.01" name="monto" class="form-control">
                        </div>

                        <!-- Foto -->
                        <div class="col-md-6">
                            <label class="form-label">Foto del paquete</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>

                        <!-- Comentarios -->
                        <div class="col-12">
                            <label class="form-label">Comentarios</label>
                            <textarea name="comentarios" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Fragil -->
                        <div class="col-md-6">
                            <label class="form-label">¿Es frágil?</label>
                            <select name="fragil" class="form-select" required>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>

                        <!-- Estatus -->
                        <div class="col-md-6">
                            <label class="form-label">Estatus</label>
                            <select name="estatus" class="form-select" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En tránsito">En tránsito</option>
                                <option value="Entregado">Entregado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>

                        <!-- Usuario -->
                        <div class="col-md-12">
                            <input type="hidden" name="user_id" value="<?= session('user_id') ?>">
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

<div class="modal fade" id="modalCreateSeller" tabindex="-1" role="dialog" aria-labelledby="modalCreateSellerLabel" aria-hidden="true">
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
    $('#formCreateSeller').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '<?= base_url('sellers/create-ajax') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
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
            error: function() {
                Swal.fire('Error', 'Ocurrió un error en la petición.', 'error');
            }
        });
    });
</script>
<script>
    window.addEventListener('load', function() {
        
        // Inicializar Select2
        $('#seller_id').select2({
            theme: 'bootstrap4',
            placeholder: '🔍 Buscar vendedor...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '<?= base_url('sellers/search') ?>', // <-- corregido
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data, params) {
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
        $('#seller_id').on('select2:select', function(e) {
            const selected = e.params.data;
            if (selected.id === 'create_new') {
                $('#seller_id').val(null).trigger('change');
                $('#modalCreateSeller').modal('show');
            }
        });

        // Guardar nuevo vendedor vía AJAX
        $('#formCreateSeller').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= base_url('sellers/create-ajax') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#modalCreateSeller').modal('hide');

                        const newOption = new Option(response.data.text, response.data.id, true, true);
                        $('#seller_id').append(newOption).trigger('change');

                        Swal.fire('Éxito', 'Vendedor creado y seleccionado.', 'success');
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo crear el vendedor.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Ocurrió un error en la petición.', 'error');
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>