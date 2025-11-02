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
        <li class="nav-item">
            <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-controls="security"
                aria-selected="false">
                Seguridad y Logs
            </a>
        </li>
    </ul>

    <div class="tab-content mt-4" id="settingsTabContent">

        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">

            <h4 class="mb-3">Información y Apariencia</h4>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Nombre y Título</h5>
                    <p class="card-text text-muted">Define el nombre de la aplicación y el prefijo de los títulos.</p>
                    <form>...</form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Zona Horaria</h5>
                    <p class="card-text text-muted">Asegúrate de que la hora del servidor sea correcta.</p>
                    <form>...</form>
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

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Inventario y Stock</h5>
                    <p class="card-text text-muted">Define umbrales de alerta de stock.</p>
                    <form>...</form>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">

            <h4 class="mb-3">Registro y Mantenimiento</h4>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Registro de Actividad (Logs)</h5>
                    <p class="card-text text-muted">Consulta el historial de acciones y accesos.</p>
                    <a href="<?= base_url('logs') ?>" class="btn btn-warning">Ver Logs del Sistema</a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Mantenimiento de Datos</h5>
                    <p class="card-text text-muted">Opciones para limpiar cachés o realizar copias de seguridad.</p>
                    <button class="btn btn-danger">Limpiar Caché de Vistas</button>
                </div>
            </div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>