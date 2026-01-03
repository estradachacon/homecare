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
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold text-primary">
                                <?= esc($settings->company_name) ?>
                            </h5>

                            <p class="mb-0">
                                <?= esc($settings->company_address) ?>
                            </p>
                        </div>

                        <a href="<?= base_url('settings/edit') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-edit"></i> Editar
                        </a>
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
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Limpiar Cookies de navegador</h5>
                    <button class="btn btn-outline-danger btn-sm" onclick="confirmClearBrowser()">
                        <i class="fa-solid fa-broom"></i> Limpiar datos del navegador
                    </button>

                    <?php if (session()->get('role_id') == 1): ?>
                        <button class="btn btn-danger btn-sm" onclick="confirmLogoutAll()">
                            <i class="fa-solid fa-users-slash"></i> Cerrar sesión a todos
                        </button>
                    <?php endif; ?>

                    <script>
                        function confirmLogoutAll() {
                            Swal.fire({
                                title: '¿Cerrar sesión a todos?',
                                text: 'Todos los usuarios activos serán desconectados',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, cerrar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "<?= base_url('system/logout-all') ?>";
                                }
                            });
                        }
                    </script>
                    <script>
                        function confirmClearBrowser() {
                            Swal.fire({
                                title: '¿Limpiar datos del navegador?',
                                text: 'Esto limpiará cookies y caché del sistema en este navegador.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Sí, limpiar',
                                cancelButtonText: 'Cancelar'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "<?= base_url('tools/clear-browser') ?>";
                                }
                            });
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>