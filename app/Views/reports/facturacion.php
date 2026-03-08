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

    .select2-container .select2-selection--single {
        height: 38px !important;
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
            placeholder: 'Buscar vendedor...',
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
</script>

<?= $this->endSection() ?>