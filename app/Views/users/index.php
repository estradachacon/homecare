<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">Lista de usuarios</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('users/new') ?>"><i
                        class="fa-solid fa-plus"></i> Crear usuario</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 mb-2">
                        <label>Tipo de usuario</label>
                        <select class="form-control select2 select-filter" data-placeholder="Seleccione una opción"
                            name="status" multiple="true">
                            <option value="Gerente">Gerente</option>
                            <option value="Pagador">Pagador</option>
                            <option value="Digitador">Digitador</option>
                            <option value="Motorista">Motorista</option>
                        </select>
                    </div>
                </div>
                <table class="table table-bordered" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Usuario</th>
                            <th>Email</th>
                            <th class="col-1">Rol</th>
                            <th class="col-1">Sucursal</th>
                            <th class="col-1 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay usuarios registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= esc($user['id']) ?></td>
                                    <td><?= esc($user['user_name']) ?></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $roleName = esc($user['role_name']);
                                        $style = []; // Array para definir las clases
                                
                                        // Definimos el estilo basado en el rol
                                        switch ($roleName) {
                                            case 'Gerente':
                                                $style['class'] = 'bg-dark text-white'; // Oscuro y potente
                                                break;
                                            case 'Pagador':
                                                $style['class'] = 'bg-success text-white'; // Éxito y confirmación
                                                break;
                                            case 'Digitador':
                                                $style['class'] = 'bg-info text-white'; // Informativo, texto oscuro para contraste
                                                break;
                                            case 'Motorista':
                                                $style['class'] = 'bg-secondary text-white'; // Advertencia/Atención, texto oscuro para contraste
                                                break;
                                            default:
                                                $style['class'] = 'bg-light text-warning border border-secondary'; // Para cualquier cosa que se escape
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $style['class'] ?> rounded-pill px-3 py-2">
                                            <?= $roleName ?>
                                        </span>
                                    </td>
                                    <td><?= esc($user['branch_name']) ?></td>
                                    <td class="text-center">
                                        <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-sm btn-info"
                                            title="Editar"><i class="fa-solid fa-edit"></i></a>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $user['id'] ?>"><i
                                                class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
        document.addEventListener('DOMContentLoaded', function() {
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
                            fetch("<?= base_url('users/delete') ?>", {
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