<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .btn-equal {
        height: 38px;
        padding: .375rem .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-container-adjust { margin-top: 30px; }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: .75rem;
    }
</style>

<div class="row align-items-end">

    <!-- Reporte 1: Compras Percibidas -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Compras Percibidas</h4>
                <small class="text-muted">
                    Listado de compras ordenado por fecha y correlativo. Exportable en PDF y Excel.
                </small>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="get" target="_blank">
                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" name="desde" class="form-control" value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date" name="hasta" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Proveedor (opcional)</label>
                                    <select name="proveedor_id" class="form-control proveedor-select"></select>
                                </div>
                            </div>

                            <div class="col-md-3 btn-container-adjust">
                                <div class="row">
                                    <div class="col-6 pr-1">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/compras-pdf') ?>"
                                            class="btn btn-primary btn-block btn-equal">
                                            <i class="fas fa-file-pdf mr-1"></i> PDF
                                        </button>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <button type="submit"
                                            formaction="<?= base_url('reports/compras-excel') ?>"
                                            class="btn btn-success btn-block btn-equal">
                                            <i class="fas fa-file-excel mr-1"></i> Excel
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

    <!-- Reporte 2: Compras Agrupadas por Producto -->
    <div class="col-md-12 mt-3">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Compras por Producto</h4>
                <small class="text-muted">
                    Totales de cantidades y montos agrupados por producto en el período seleccionado.
                </small>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="get" target="_blank">
                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" name="desde" class="form-control" value="<?= date('Y-m-01') ?>">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hasta</label>
                                    <input type="date" name="hasta" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Proveedor (opcional)</label>
                                    <select name="proveedor_id" class="form-control proveedor-select"></select>
                                </div>
                            </div>

                            <div class="col-md-2 btn-container-adjust">
                                <button type="submit"
                                    formaction="<?= base_url('reports/compras-por-producto-pdf') ?>"
                                    class="btn btn-primary btn-block btn-equal">
                                    <i class="fas fa-file-pdf mr-1"></i> Generar PDF
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
    $('.proveedor-select').each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) return;
        $(this).select2({
            placeholder: 'Todos los proveedores',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '<?= base_url('proveedores/searchAjax') ?>',
                dataType: 'json',
                delay: 250,
                data: p => ({ q: p.term, select2: 1 }),
                processResults: d => d
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
