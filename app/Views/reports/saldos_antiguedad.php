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
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Saldos por Antigüedad - Maestro</h4>
                <small class="text-muted">
                    Genera el reporte de cuentas por cobrar clasificadas hasta el día seleccionado.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Fecha Corte -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha de Corte</label>
                                    <input type="date"
                                        name="fecha_corte"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted">Cliente</label>
                                    <select name="cliente_id" class="form-control cliente-select"></select>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="col-md-5 btn-container-adjust">
                                <div class="row">

                                    <div class="col-md-6">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/saldos-antiguedad-pdf') ?>"
                                            class="btn btn-primary btn-block btn-equal">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Resumido
                                        </button>
                                    </div>

                                    <div class="col-md-6">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/saldos-antiguedad-detalle-pdf') ?>"
                                            class="btn btn-success btn-block btn-equal">
                                            <i class="fas fa-list mr-2"></i>
                                            Con Detalle
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
    <div class="col-md-12 mt-2">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Saldos por Antigüedad - Vendedor</h4>
                <small class="text-muted">
                    Genera el reporte de cuentas por cobrar clasificadas por rango de días.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="get" target="_blank">
                        <div class="row">

                            <!-- Fecha Corte -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha de Corte</label>
                                    <input type="date"
                                        name="fecha_corte"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Cliente -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted">Cliente</label>
                                    <select name="cliente_id" class="form-control cliente-select"></select>
                                </div>
                            </div>

                            <!-- Vendedor -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted">Vendedor</label>
                                    <select id="sellerSelect"
                                        name="seller_id"
                                        class="form-control seller-select">
                                    </select>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="col-md-5 btn-container-adjust">
                                <div class="row">

                                    <div class="col-md-6">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/saldos-antiguedad-vendedor-pdf') ?>"
                                            class="btn btn-primary btn-block btn-equal">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Resumido
                                        </button>
                                    </div>

                                    <div class="col-md-6">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/saldos-antiguedad-vendedor-detalle-pdf') ?>"
                                            class="btn btn-success btn-block btn-equal">
                                            <i class="fas fa-list mr-2"></i>
                                            Con Detalle
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
    <div class="col-md-12 mt-2">
        <div class="card">
            <div class="card-header">
                <h4>Estado de Cuenta de Cliente</h4>
                <small class="text-muted">
                    Muestra todos los documentos y pagos aplicados a un cliente con su saldo acumulado.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Cliente -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="text-muted">Cliente</label>
                                    <select name="cliente_id" class="form-control cliente-select" required></select>
                                </div>
                            </div>

                            <!-- Fecha inicio -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date"
                                        name="fecha_inicio"
                                        class="form-control">
                                </div>
                            </div>

                            <!-- Fecha fin -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date"
                                        name="fecha_fin"
                                        class="form-control"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- Botón -->
                            <div class="col-md-2 btn-container-adjust">
                                <button type="submit"
                                    formaction="<?= base_url('reports/estado-cuenta-cliente-pdf') ?>"
                                    class="btn btn-dark btn-block btn-equal">
                                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                                    Generar
                                </button>
                            </div>

                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
    <div class="col-md-12 mt-2">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Quedans</h4>
                <small class="text-muted">
                    Consulta de quedans con su estado, fechas y montos.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Desde -->
                            <div class="col-md-2">
                                <label>Desde</label>
                                <input
                                    type="date"
                                    name="desde"
                                    class="form-control"
                                    value="<?= esc($desde ?? date('Y-m-01')) ?>">
                            </div>

                            <!-- Hasta -->
                            <div class="col-md-2">
                                <label>Hasta</label>
                                <input
                                    type="date"
                                    name="hasta"
                                    class="form-control"
                                    value="<?= esc($hasta ?? date('Y-m-d')) ?>">
                            </div>

                            <!-- Cliente -->
                            <div class="col-md-4">
                                <label>Cliente</label>
                                <select name="cliente_id" class="form-control cliente-select"></select>
                            </div>

                            <!-- Botones -->
                            <div class="col-md-2 mt-4">
                                <button type="submit"
                                    formaction="<?= base_url('reports/quedans-pdf') ?>"
                                    class="btn btn-success btn-block">

                                    <i class="fas fa-file-pdf mr-2"></i>
                                    Detalle PDF
                                </button>
                            </div>
                            <div class="col-md-2 mt-4">
                                <button type="submit"
                                    formaction="<?= base_url('reports/quedans-excel') ?>"
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