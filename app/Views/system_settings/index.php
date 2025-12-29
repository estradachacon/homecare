<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">⚙️ Configuración del Sistema</h2>
            <p class="text-muted">Ajusta los parámetros globales y operativos de la aplicación.</p>
        </div>
    </div>

    <hr>

    <ul class="nav nav-tabs" id="settingsTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab"
                aria-controls="general" aria-selected="true">
                General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="branches-tab" data-toggle="tab" href="#branches" role="tab" aria-controls="branches"
                aria-selected="false">
                Sucursales y Operación
            </a>
        </li>
    </ul>

    <div class="tab-content mt-4" id="settingsTabContent">

        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">

            <h4 class="mb-3">Información y Apariencia</h4>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Datos de la Empresa</h5>
                        <div class="mb-2">
                            <h5 class="fw-bold text-primary">FC Encomiendas</h5>
                        </div>

                        <div class="mb-2">
                            <h5 class="fw-bold text-primary">Casa Matriz, Metrogalerias Local 1-2C</h5>
                        </div>
                </div>
            </div>


            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Almacenamiento en uso</h5>
                    <p class="card-text text-muted">
                        Espacio utilizado actualmente
                    </p>

                    <h4 class="fw-bold text-primary">
                        <?= $storageUsed ?> MB
                    </h4>
                </div>
            </div>

        </div>

        <div class="tab-pane fade" id="branches" role="tabpanel" aria-labelledby="branches-tab">

            <h4 class="mb-3">Parámetros Operativos</h4>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Administración de Sucursales</h5>
                    <p class="card-text text-muted">Gestiona la creación, edición y estado (Activa/Inactiva) de las
                        sucursales.</p>
                    <a href="<?= base_url('branches') ?>" class="btn btn-primary">Ir a Listado de Sucursales</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>