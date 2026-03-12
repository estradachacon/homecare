<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .btn-equal {
        height: 38px;
        /* misma altura que form-control */
        padding: .375rem .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-container-adjust {
        margin-top: 30px;
        /* ajusta este valor a tu gusto */
    }

    .select2-container .select2-selection--single,
    .select2-container .select2-selection--multiple {
        min-height: 38px !important;
        /* altura Bootstrap */
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }

    /* Texto del single */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: .75rem;
    }

    /* Texto del multiple */
    .select2-container--default .select2-selection--multiple {
        padding: 2px 6px;
    }

    /* Tags del multiple */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: 2px 6px;
        font-size: 15px;
    }

    /* Campo de búsqueda interno */
    .select2-container--default .select2-selection--multiple .select2-search__field {
        margin-top: 3px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
        padding-left: 18px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #ccc;
        transition: .3s;
        border-radius: 24px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background: white;
        transition: .3s;
        border-radius: 50%;
    }

    .switch input:checked+.slider {
        background: #28a745;
    }

    .switch input:checked+.slider:before {
        transform: translateX(22px);
    }

    .switch-label {
        display: block;
        font-size: 13px;
        color: #6c757d;
        margin-top: 6px;
    }
</style>
<div class="row align-items-end">
    <div class="col-md-12 mt-2">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Facturación</h4>
                <small class="text-muted">
                    Genera la facturación por rango de fecha agrupada por tipo de documento.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Fecha Desde -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date"
                                        name="desde"
                                        class="form-control"
                                        value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>

                            <!-- Fecha Hasta -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date"
                                        name="hasta"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Cliente</label>

                                    <select name="cliente_id"
                                        id="clienteSelect"
                                        class="form-control cliente-select">

                                        <option value="">Todos los clientes</option>

                                    </select>
                                </div>
                            </div>

                            <!-- Tipo Documento -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo Documento</label>
                                    <select name="tipo_documento" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="01">Factura</option>
                                        <option value="03">Crédito Fiscal</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Botones -->
                            <div class="col-md-6 btn-container-adjust">
                                <div class="row">

                                    <div class="col-md-4">
                                        <button type="submit"
                                            name="modo"
                                            value="resumen"
                                            formaction="<?= base_url('reports/facturacion-pdf') ?>"
                                            class="btn btn-primary btn-block btn-equal">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Resumen
                                        </button>
                                    </div>

                                    <div class="col-md-4">
                                        <button type="submit"
                                            name="modo"
                                            value="detalle"
                                            formaction="<?= base_url('reports/facturacion-pdf') ?>"
                                            class="btn btn-success btn-block btn-equal">
                                            <i class="fas fa-list mr-2"></i>
                                            Detalle PDF
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/facturacion-excel') ?>"
                                            class="btn btn-success btn-block btn-equal">

                                            <i class="fas fa-file-excel mr-2"></i>
                                            Detalle Excel

                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
    <div class="col-md-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Ventas por Cliente</h4>
                <small class="text-muted">
                    Genera el detalle de ventas agrupado por cliente y tipo de documento.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Fecha Desde -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date"
                                        name="desde"
                                        class="form-control"
                                        value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>

                            <!-- Fecha Hasta -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date"
                                        name="hasta"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Cliente</label>

                                    <select name="cliente_id"
                                        class="form-control cliente-select">

                                        <option value="">Todos los clientes</option>

                                    </select>

                                </div>
                            </div>

                            <!-- Tipo Documento -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo Documento</label>

                                    <select name="tipo_documento" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="01">Factura</option>
                                        <option value="03">Crédito Fiscal</option>
                                    </select>

                                </div>
                            </div>

                            <!-- Botón -->
                            <div class="col-md-2 mt-2">

                                <button type="submit"
                                    formaction="<?= base_url('reports/ventas-cliente-pdf') ?>"
                                    class="btn btn-success btn-block">

                                    <i class="fas fa-file-pdf mr-2"></i>
                                    Detalle PDF

                                </button>

                            </div>
                            <div class="col-md-2 mt-2">
                                <button type="submit"
                                    formaction="<?= base_url('reports/ventas-cliente-excel') ?>"
                                    class="btn btn-success btn-block btn-equal">

                                    <i class="fas fa-file-excel mr-2"></i>
                                    Detalle Excel

                                </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mt-3">

        <div class="card">

            <div class="card-header">
                <h4>Reporte de Ventas por Tipo de Venta</h4>
                <small class="text-muted">
                    Genera el reporte de ventas filtrado por tipo de venta, vendedor o cliente.
                </small>
            </div>

            <div class="card shadow-sm">

                <div class="card-body">

                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Fecha Desde -->
                            <div class="col-md-2">
                                <label>Desde</label>
                                <input type="date"
                                    name="desde"
                                    class="form-control"
                                    value="<?= date('Y-m-01') ?>">
                            </div>

                            <!-- Fecha Hasta -->
                            <div class="col-md-2">
                                <label>Hasta</label>
                                <input type="date"
                                    name="hasta"
                                    class="form-control"
                                    value="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- Tipo de Venta -->
                            <div class="col-md-3">
                                <label>Tipo de Venta</label>

                                <select name="tipo_venta[]"
                                    class="form-control tipo-venta-select"
                                    multiple>

                                </select>

                            </div>

                            <!-- Clasificado por -->
                            <div class="col-md-2">
                                <label>Clasificado por</label>

                                <select name="clasificado"
                                    class="form-control">

                                    <option value="vendedor">
                                        Vendedor
                                    </option>

                                    <option value="cliente">
                                        Cliente
                                    </option>

                                </select>

                            </div>

                            <!-- Nivel -->
                            <div class="col-md-3">
                                <label>Nivel de Reporte</label>

                                <select name="nivel" id="nivelReporte" class="form-control">

                                    <option value="detalle">
                                        Detalle por factura
                                    </option>

                                    <option value="resumen">
                                        Resumen
                                    </option>

                                </select>

                            </div>

                            <!-- Vendedor -->
                            <div class="col-md-3 mt-2">

                                <label>Vendedor</label>

                                <select name="vendedores[]"
                                    class="form-control vendedor-select"
                                    multiple>

                                    <!-- cargado por ajax -->

                                </select>

                            </div>

                            <!-- Mostrar items -->
                            <div class="col-md-2 mt-4 text-center">

                                <label class="switch">
                                    <input type="checkbox" id="mostrarItemsSwitch" name="mostrar_items" value="1">
                                    <span class="slider"></span>
                                </label>

                                <span class="switch-label">
                                    Mostrar detalle de items
                                </span>

                            </div>

                            <!-- Salto de página -->
                            <div class="col-md-2 mt-4 text-center">

                                <label class="switch">
                                    <input type="checkbox" name="salto_tipo" value="1">
                                    <span class="slider"></span>
                                </label>

                                <span class="switch-label">
                                    Salto de página por tipo
                                </span>

                            </div>

                            <!-- Botón PDF -->
                            <div class="col-md-2 mt-4">
                                <button type="submit"
                                    formaction="<?= base_url('reports/ventas-tipo-pdf') ?>"
                                    class="btn btn-success btn-block">

                                    <i class="fas fa-file-pdf mr-2"></i>
                                    Detalle PDF

                                </button>
                            </div>

                            <!-- Botón Excel -->
                            <div class="col-md-2 mt-4">
                                <button type="submit"
                                    formaction="<?= base_url('reports/ventas-tipo-excel') ?>"
                                    class="btn btn-success btn-block btn-equal">

                                    <i class="fas fa-file-excel mr-2"></i>
                                    Detalle Excel

                                </button>
                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>
    <div class="col-md-12 mt-3">
        <div class="card">

            <div class="card-header">
                <h4>Reporte de Ventas por Vendedor</h4>
                <small class="text-muted">
                    Genera ventas por vendedor con opción de agrupar y mostrar detalle de productos.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Fecha Desde -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date"
                                        name="desde"
                                        class="form-control"
                                        value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>

                            <!-- Fecha Hasta -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date"
                                        name="hasta"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Vendedores -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Vendedores</label>

                                    <select name="vendedores[]"
                                        id="sellerSelect"
                                        class="form-control"
                                        multiple>

                                    </select>

                                </div>
                            </div>

                            <!-- Agrupación -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Agrupar</label>

                                    <select name="agrupar" class="form-control">

                                        <option value="ninguno">Sin agrupar</option>
                                        <option value="vendedor">Por vendedor</option>

                                    </select>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Nivel del reporte</label>
                                <select name="nivel" class="form-control">

                                    <option value="dia">Resumen por día</option>

                                    <option value="factura">Detalle de facturas</option>

                                    <option value="productos">Detalle con productos</option>

                                </select>
                            </div>
                            <!-- Nivel de detalle -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Detalle</label>

                                    <select name="detalle" class="form-control">

                                        <option value="resumen">Resumen</option>
                                        <option value="productos">Incluir productos</option>

                                    </select>

                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="col-md-4 btn-container-adjust">

                                <div class="row">

                                    <div class="col-md-6">
                                        <button
                                            type="submit"
                                            formaction="<?= base_url('reports/facturacion-vendedores-pdf') ?>"
                                            class="btn btn-primary btn-block btn-equal">

                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Resumen PDF

                                        </button>
                                    </div>

                                    <div class="col-md-6">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/ventas-vendedores-excel') ?>"
                                            class="btn btn-success btn-block btn-equal">

                                            <i class="fas fa-file-excel mr-2"></i>
                                            Excel

                                        </button>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        const cliente = $('#clienteSelect').val();
        // ================= CLIENTE SELECT2 =================

        $('.cliente-select').each(function() {

            if ($(this).hasClass("select2-hidden-accessible")) return;

            $(this).select2({
                placeholder: 'Buscar cliente...',
                ajax: {
                    url: '<?= base_url("clientes/buscar") ?>',
                    dataType: 'json',
                    delay: 250,
                    data: p => ({
                        q: p.term
                    }),
                    processResults: function(data) {
                        if (!Array.isArray(data)) {
                            return {
                                results: []
                            };
                        }
                        return {
                            results: data
                        };
                    }
                }
            });

        });

        $('#sellerSelect').select2({
            placeholder: 'Buscar vendedores...',
            multiple: true,
            minimumInputLength: 2,
            ajax: {
                url: "<?= base_url('sellers/searchAjax') ?>",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        select2: 1
                    };
                },
                processResults: function(data) {
                    return data;
                }
            }
        });

        $('.vendedor-select').each(function() {

            if ($(this).hasClass("select2-hidden-accessible")) return;

            $(this).select2({
                placeholder: 'Buscar vendedores...',
                multiple: true,
                minimumInputLength: 2,
                ajax: {
                    url: "<?= base_url('sellers/searchAjax') ?>",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            select2: 1
                        };
                    },
                    processResults: function(data) {
                        return data;
                    }
                }
            });

        });

        $('.tipo-venta-select').each(function() {

            if ($(this).hasClass("select2-hidden-accessible")) return;

            $(this).select2({
                placeholder: 'Buscar tipo de venta...',
                multiple: true,
                minimumInputLength: 1,
                ajax: {
                    url: "<?= base_url('tipo_venta/searchAjax') ?>",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            select2: 1
                        };
                    },
                    processResults: function(data) {
                        return data;
                    }
                }
            });

        });

        function controlarItems() {

            let nivel = $('#nivelReporte').val();
            let sw = $('#mostrarItemsSwitch');

            if (nivel === 'resumen') {

                sw.prop('checked', false);
                sw.prop('disabled', true);

                sw.closest('.switch').css({
                    'opacity': '0.4',
                    'cursor': 'not-allowed'
                });

            } else {

                sw.prop('disabled', false);

                sw.closest('.switch').css({
                    'opacity': '1',
                    'cursor': 'pointer'
                });
            }
        }

        $('#nivelReporte').on('change', controlarItems);

        $(document).ready(function() {
            controlarItems();
        });
    });
</script>

<?= $this->endSection() ?>