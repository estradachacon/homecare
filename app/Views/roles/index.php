<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de Roles</h4>
                <?php if (tienePermiso('crear_roles')): ?>
                    <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('roles/new') ?>"><i
                            class="fa-solid fa-plus"></i> Nuevo Rol</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="col-1">ID</th>
                            <th class="col-4">Nombre del Rol</th>
                            <th class="col-5">Descripción</th>
                            <th class="col-2 justify-content-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($roles)): ?>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td class="text-center"><?= esc($role['id']) ?></td>
                                    <td><strong><?= esc($role['nombre']) ?></strong></td>
                                    <td><?= esc($role['descripcion']) ?></td>
                                    <td class="text-center">
                                        <?php if (tienePermiso('editar_roles')): ?>
                                            <a href="<?= base_url('roles/edit/' . $role['id']) ?>"
                                                class="btn btn-sm btn-info"><i class="fa-solid fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('eliminar_roles')): ?>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $role['id'] ?>">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (tienePermiso('asignar_permisos')): ?>
                                            <a href="<?= site_url('access/' . $role['id']) ?>"
                                            class="btn btn-sm btn-warning"
                                            title="Asignar permisos">
                                                <i class="fa-solid fa-key"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay roles registrados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle detalles en móviles
        document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const details = this.closest('.card').querySelector('.details');
                details.classList.toggle('d-none');
                this.textContent = details.classList.contains('d-none') ? 'Ver' : 'Ocultar';
            });
        });

        // Botones eliminar
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfHeader = document.querySelector('meta[name="csrf-header"]').getAttribute('content');
                        fetch("<?= base_url('roles/delete') ?>", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    [csrfHeader]: csrfToken
                                },
                                body: new URLSearchParams({
                                    id: id
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                Swal.fire({
                                    title: data.status === 'success' ? 'Éxito' : 'Error',
                                    text: data.message,
                                    icon: data.status,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                if (data.status === 'success') {
                                    const row = button.closest('tr');
                                    if (row) row.remove();
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                }
                            })
                            .catch(err => {
                                Swal.fire('Error', 'Ocurrió un problema en la petición.', 'error');
                            });
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>