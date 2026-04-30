<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between mb-4">
    <div>
        <h4 class="mb-1">Reportes de Consignaciones</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/consignaciones">Consignaciones</a></li>
                <li class="breadcrumb-item active">Reportes</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-3">

    <div class="col-md-4">
        <a href="/consignaciones/reportes/notas" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-reporte">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="icon-box bg-primary text-white rounded p-3">
                        <i class="fa-solid fa-file-lines fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 text-dark">Notas de Envío</h5>
                        <p class="card-text text-muted small mb-0">
                            Listado de NE con estados, aprobaciones, vendedores y montos totales.
                        </p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="/consignaciones/reportes/productos" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-reporte">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="icon-box bg-success text-white rounded p-3">
                        <i class="fa-solid fa-boxes-stacked fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 text-dark">Productos</h5>
                        <p class="card-text text-muted small mb-0">
                            Unidades enviadas, facturadas, devueltas y en stock por producto.
                        </p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="/consignaciones/reportes/pacientes" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-reporte">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="icon-box bg-info text-white rounded p-3">
                        <i class="fa-solid fa-user-injured fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 text-dark">Pacientes</h5>
                        <p class="card-text text-muted small mb-0">
                            Pacientes atendidos, doctor vinculado y resumen de notas recibidas.
                        </p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="/consignaciones/reportes/doctores" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-reporte">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="icon-box bg-warning text-white rounded p-3">
                        <i class="fa-solid fa-user-doctor fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 text-dark">Doctores</h5>
                        <p class="card-text text-muted small mb-0">
                            Doctores con cantidad de pacientes, notas y valor total gestionado.
                        </p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="/consignaciones/reportes/clientes" class="text-decoration-none">
            <div class="card h-100 border-0 shadow-sm card-reporte">
                <div class="card-body d-flex align-items-start gap-3 p-4">
                    <div class="icon-box bg-danger text-white rounded p-3">
                        <i class="fa-solid fa-building fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-1 text-dark">Clientes Facturados</h5>
                        <p class="card-text text-muted small mb-0">
                            Clientes a quienes se les facturó, con notas y monto total facturado.
                        </p>
                    </div>
                </div>
            </div>
        </a>
    </div>

</div>

<style>
    .card-reporte { transition: transform .15s, box-shadow .15s; }
    .card-reporte:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12) !important; }
    .icon-box { min-width: 48px; min-height: 48px; display: flex; align-items: center; justify-content: center; }
</style>

<?= $this->endSection() ?>
