<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Grupos de Contenido</h4>
                <button class="btn btn-primary" onclick="crearGrupo()">Crear nuevo grupo</button>
            </div>
            <hr>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th class="col-md-4">Nombre del Grupo</th>
                            <th class="col-md-3">Descripción</th>
                            <th class="col-md-1">Tipo</th>
                            <th>Activo</th>
                            <th class="col-md-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $group): ?>
                            <tr>
                                <td><?= $group->id ?></td>
                                <td><?= esc($group->title) ?></td>
                                <td><?= esc($group->description) ?></td>
                                <td><?= esc($group->type) ?></td>
                                <td class="text-center"><?= $group->is_active ? 'Sí' : 'No' ?></td>
                                <td class="text-center">
                                    <!-- Gestionar imágenes -->
                                    <a href="<?= base_url('content/manage/' . $group->id) ?>" class="btn btn-sm btn-primary">Gestionar</a>
                                    <!-- Editar grupo -->
                                    <a href="<?= base_url('content/edit/' . $group->id) ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <!-- Borrar grupo -->
                                    <?php if ($group->id != 1): ?>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $group->id ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
                        fetch("<?= base_url('content/group/delete') ?> ", {
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
<script>
    function crearGrupo() {
        Swal.fire({
            title: 'Nuevo Grupo de Fotos',
            input: 'text',
            inputLabel: 'Nombre del grupo',
            inputPlaceholder: 'Escribe el nombre...',
            showCancelButton: true,
            confirmButtonText: 'Crear',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes escribir un nombre para el grupo';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear un formulario dinámico para enviar al backend
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/content/create';
                form.style.display = 'none';

                // Campo title
                const titleInput = document.createElement('input');
                titleInput.name = 'title';
                titleInput.value = result.value;
                form.appendChild(titleInput);

                // Campo slug opcional: lo puedes dejar vacío y el backend lo generará
                const slugInput = document.createElement('input');
                slugInput.name = 'slug';
                slugInput.value = '';
                form.appendChild(slugInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>