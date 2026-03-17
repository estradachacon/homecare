<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex text-center justify-content-center">
                <h4 class="header-title">Menú Reportes</h4>
            </div>
            <div class="card-body">
                <div class="row" style="padding-left: 100px !important;padding-right: 100px !important">
                    <a href="<?= base_url('reports/facturacion') ?>" class="col-md-3 card-options">
                        <div class="card border-success mb-3 card-option-container">
                            <div class="card-body text-info icon-card-options"><i class="fa-solid fa-file-invoice"></i></div>
                            <div class="card-footer bg-transparent border-info card-footer-options">Facturación</div>
                        </div>
                    </a>
                    <a href="<?= base_url('reports/saldos-antiguedad') ?>" class="col-md-3 card-options">
                        <div class="card border-success mb-3 card-option-container">
                            <div class="card-body text-info icon-card-options"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                            <div class="card-footer bg-transparent border-info card-footer-options">Cuentas por cobrar</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>