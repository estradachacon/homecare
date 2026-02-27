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
        margin-top: 30px; /* ajusta este valor a tu gusto */
    }
</style>
<div class="row align-items-end">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Reporte de Saldos por Antigüedad</h4>
                <small class="text-muted">
                    Genera el reporte de cuentas por cobrar clasificadas por rango de días.
                </small>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">

                    <form method="get" target="_blank">

                        <div class="row">

                            <!-- Fecha Corte -->
                            <div class="col-md-3">
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
                                    <label>Cliente (Opcional)</label>
                                    <input type="text"
                                        name="cliente"
                                        class="form-control"
                                        placeholder="Nombre del cliente">
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="col-md-5 btn-container-adjust">
                                <div class="row">

                                    <div class="col-md-6">
                                        <button type="submit"
                                            formaction="<?= base_url('reportes/saldos-antiguedad-pdf') ?>"
                                            class="btn btn-primary btn-block btn-equal">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Resumido
                                        </button>
                                    </div>

                                    <div class="col-md-6">
                                        <button type="submit"
                                            formaction="<?= base_url('reportes/saldos-antiguedad-detalle-pdf') ?>"
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
</div>

<?= $this->endSection() ?>