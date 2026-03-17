<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .select2-container {
        z-index: 9999 !important;
    }

    .select2-container {
        z-index: 1050 !important;
    }

    .select2-dropdown {
        z-index: 1050 !important;
    }

    .select2-container .select2-selection--single {
        height: 40px !important;
        /* altura estándar Bootstrap */
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        /* centra texto */
        padding-left: .75rem;
    }
</style>
<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex">
                <h4 class="header-title">Configuración de Comisiones</h4>
            </div>

            <div class="accordion" id="accordionComisiones">

                <!-- 🔹 1. MARGEN -->
                <form method="post" action="<?= base_url('comisiones/guardarMargen') ?>">
                    <div class="card mb-2">
                        <div class="card-header d-flex align-items-center" data-toggle="collapse" data-target="#margen">
                            <h5 class="mb-0">
                                Comisión por Margen de Utilidad
                                <span class="badge badge-warning ml-2">Pendiente módulo de compras</span>
                            </h5>
                            <i class="fa fa-chevron-down ml-auto"></i>
                        </div>

                        <div id="margen" class="collapse" data-parent="#accordionComisiones">
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Margen Min</th>
                                                <th>Margen Max</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($margenes as $m): ?>
                                                <tr>
                                                    <td><input type="number" name="margen_min[]" value="<?= $m->margen_min ?>" class="form-control form-control-sm"></td>
                                                    <td><input type="number" name="margen_max[]" value="<?= $m->margen_max ?>" class="form-control form-control-sm"></td>
                                                    <td><input type="number" name="margen_porcentaje[]" value="<?= $m->porcentaje ?>" class="form-control form-control-sm"></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-right mt-2">
                                    <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>


                <!-- 🔹 2. REGLAS -->
                <form method="post" action="<?= base_url('comisiones/guardarReglas') ?>">

                    <div class="card mb-2">
                        <div class="card-header d-flex align-items-center" data-toggle="collapse" data-target="#reglas">
                            <h5 class="mb-0">Reglas de Comisión por Producto</h5>
                            <i class="fa fa-chevron-down ml-auto"></i>
                        </div>

                        <div id="reglas" class="collapse" data-parent="#accordionComisiones">
                            <div class="card-body">

                                <!-- 🔥 TU BLOQUE ORIGINAL COMPLETO -->
                                <div class="row align-items-end mb-3">

                                    <div class="col-md-4">
                                        <label>Tipo</label>
                                        <select id="tipoRegla" class="form-control">
                                            <option value="producto">Producto</option>
                                            <option value="categoria">Categoría</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label>Valor</label>
                                        <select id="selectProducto" class="form-control"></select>
                                    </div>

                                    <div class="col-md-2">
                                        <label>%</label>
                                        <input type="number" step="0.01" id="porcentajeRegla" class="form-control">
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary w-100" onclick="agregarRegla()">
                                            Agregar
                                        </button>
                                    </div>

                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="tablaReglas">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Valor</th>
                                                <th>%</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reglas as $r): ?>
                                                <tr>
                                                    <td>
                                                        <?= $r->tipo ?>
                                                        <input type="hidden" name="tipo[]" value="<?= $r->tipo ?>">
                                                    </td>
                                                    <td>
                                                        <?= $r->valor_texto ?? $r->valor ?>
                                                        <input type="hidden" name="valor[]" value="<?= $r->valor ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            name="porcentaje[]"
                                                            value="<?= $r->porcentaje ?>"
                                                            step="0.01"
                                                            class="form-control form-control-sm">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm btn-remove-regla">X</button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-right mt-2">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        Guardar
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                </form>


                <!-- 🔹 3. VENDEDORES -->
                <div class="card mb-2">
                    <div class="card-header d-flex"
                        data-toggle="collapse"
                        data-target="#vendedores">
                        <h5 class="mb-0">Comisión por Vendedor</h5>
                        <i class="fa fa-chevron-down ml-auto"></i>
                    </div>

                    <div id="vendedores" class="collapse" data-parent="#accordionComisiones">
                        <div class="card-body">

                            <div class="row align-items-end">

                                <div class="col-md-6">
                                    <label>Vendedor</label>
                                    <select id="selectVendedor" class="form-control"></select>
                                </div>

                                <div class="col-md-3">
                                    <label>% Comisión</label>
                                    <input type="number" step="0.01" id="inputComisionVendedor" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary w-100" onclick="agregarVendedor()">
                                        Agregar
                                    </button>
                                </div>

                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered" id="tablaVendedores">
                                    <thead>
                                        <tr>
                                            <th>Vendedor</th>
                                            <th>%</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vendedores as $v): ?>
                                            <tr>
                                                <td>
                                                    <?= $v->nombre ?>
                                                    <input type="hidden" name="vendedor_ids[]" value="<?= $v->vendedor_id ?>">
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        name="vendedor_porcentaje[]"
                                                        value="<?= $v->porcentaje ?>"
                                                        step="0.01"
                                                        class="form-control form-control-sm vendedor-input">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm btn-remove-vendedor">X</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- 🔹 4. GENERAL -->
                <form method="post" action="<?= base_url('comisiones/guardarGeneral') ?>">
                    <div class="card mb-2">
                        <div class="card-header d-flex align-items-center"
                            data-toggle="collapse"
                            data-target="#general">
                            <h5 class="mb-0">Porcentaje General</h5>
                            <i class="fa fa-chevron-down ml-auto"></i>
                        </div>

                        <div id="general" class="collapse" data-parent="#accordionComisiones">
                            <div class="card-body">

                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label>% por defecto</label>
                                        <input type="number" step="0.01"
                                            class="form-control"
                                            name="porcentaje_default"
                                            value="<?= $config->porcentaje_default ?? 0 ?>">
                                    </div>

                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success w-100">
                                            Guardar
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {

        // 🔹 SELECT2 VENDEDORES
        $('#selectVendedor').select2({
            placeholder: 'Buscar vendedor...',
            ajax: {
                url: '<?= base_url('sellers/searchAjax?select2=1') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: data => ({
                    results: data.results
                })
            }
        });

        // 🔹 SELECT2 PRODUCTOS
        $('#selectProducto').select2({
            width: '100%',
            dropdownParent: $('body'),
            placeholder: 'Buscar producto...',
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('productos/searchAjax?select2=1') ?>',
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: data => ({
                    results: data.results
                })
            }
        });

    });


    /* =========================
       🔴 VENDEDORES (AJAX FULL)
    ========================= */

    function agregarVendedor() {

        let select = $('#selectVendedor');
        let id = select.val();
        let text = select.find('option:selected').text();
        let porcentaje = $('#inputComisionVendedor').val();

        if (!id) {
            Swal.fire('Selecciona un vendedor');
            return;
        }

        if (!porcentaje) {
            Swal.fire('Ingresa un porcentaje');
            return;
        }

        // 🔥 VALIDAR DUPLICADO EN TABLA (CLAVE)
        let existe = false;

        $('#tablaVendedores tbody tr').each(function() {
            let currentId = $(this).find('input[name="vendedor_ids[]"]').val();
            if (currentId == id) {
                existe = true;
            }
        });

        if (existe) {
            Swal.fire('Ese vendedor ya está agregado');
            return;
        }

        // 🔥 AJAX
        $.post('<?= base_url('comisiones/vendedor/add') ?>', {
            vendedor_id: id,
            porcentaje: porcentaje
        }, function(resp) {

            if (resp.status === 'ok') {

                let fila = `
            <tr>
                <td>
                    ${text}
                    <input type="hidden" name="vendedor_ids[]" value="${id}">
                </td>
                <td>
                    <input type="number" name="vendedor_porcentaje[]" value="${porcentaje}" step="0.01" class="form-control form-control-sm vendedor-input">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-vendedor">X</button>
                </td>
            </tr>`;

                $('#tablaVendedores tbody').append(fila);

                // limpiar
                select.val(null).trigger('change');
                $('#inputComisionVendedor').val('');

                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: 'Vendedor agregado',
                    timer: 1500,
                    showConfirmButton: false
                });

            } else if (resp.status === 'exists') {
                Swal.fire('Ya existe en base de datos');
            }

        }, 'json');
    }
    //Actualizar el porcentaje en tiempo real
    $(document).on('blur', '.vendedor-input', function() {

        let input = $(this);
        let fila = input.closest('tr');

        let id = fila.find('input[name="vendedor_ids[]"]').val();
        let porcentaje = input.val();

        if (!porcentaje) return;

        $.post('<?= base_url('comisiones/vendedor/update') ?>', {
            vendedor_id: id,
            porcentaje: porcentaje
        }, function(resp) {

            if (resp.status === 'ok') {

                // 🔥 feedback visual input
                input.addClass('border-success');

                setTimeout(() => {
                    input.removeClass('border-success');
                }, 1000);

                // 🔥 TOAST
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Actualizado',
                    showConfirmButton: false,
                    timer: 1500
                });

            } else if (resp.status === 'nochange') {

                // opcional: no mostrar nada o un aviso leve
                console.log('Sin cambios');

            } else {

                Swal.fire('Error al actualizar');

            }

        }, 'json');

    });
    // 🔹 ELIMINAR VENDEDOR
    $(document).on('click', '.btn-remove-vendedor', function() {

        let fila = $(this).closest('tr');
        let id = fila.find('input[name="vendedor_ids[]"]').val();

        Swal.fire({
            title: '¿Eliminar vendedor?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí'
        }).then(result => {

            if (result.isConfirmed) {

                $.post('<?= base_url('comisiones/vendedor/delete') ?>', {
                    vendedor_id: id
                }, function(resp) {

                    if (resp.status === 'ok') {

                        fila.remove();

                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: 'Eliminado',
                            timer: 1500,
                            showConfirmButton: false
                        });

                    }

                }, 'json');

            }

        });

    });

    // 🔹 AGREGAR REGLA
    function agregarRegla() {

        let tipo = $('#tipoRegla').val();
        let producto = $('#selectProducto').select2('data')[0];
        let porcentaje = $('#porcentajeRegla').val();

        if (!producto) {
            Swal.fire('Selecciona un producto');
            return;
        }

        if (!porcentaje) {
            Swal.fire('Ingresa un porcentaje');
            return;
        }

        let fila = `
    <tr>
        <td>
            ${tipo}
            <input type="hidden" name="tipo[]" value="${tipo}">
        </td>
        <td>
            ${producto.text}
            <input type="hidden" name="valor[]" value="${producto.id}">
        </td>
        <td>
            <input type="number" name="porcentaje[]" value="${porcentaje}" step="0.01" class="form-control form-control-sm">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm btn-remove-regla">X</button>
        </td>
    </tr>`;

        $('#tablaReglas tbody').append(fila);

        $('#selectProducto').val(null).trigger('change');
        $('#porcentajeRegla').val('');
    }


    // 🔹 ELIMINAR REGLA (solo visual)
    $(document).on('click', '.btn-remove-regla', function() {
        $(this).closest('tr').remove();
    });
</script>

<?= $this->endSection() ?>