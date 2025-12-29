<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">Lista de sucursales</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('branches/new') ?>"><i
                        class="fa-solid fa-plus"></i> Crear sucursal</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="users-table">
                    <thead>
                        <tr>
                            <th class="col-1">ID</th>
                            <th class="col-5">Nombre de Sucursal</th>
                            <th class="col-5">Dirección</th>
                            <th class="text-center col-1">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($branches)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay sucursales registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($branches as $branch): ?>
                                <tr>
                                    <td class="text-center"><?= esc($branch->id) ?></td>
                                    <td><?= esc($branch->branch_name) ?></td>
                                    <td><?= esc($branch->branch_direction) ?></td>
                                    
                                    <td class="text-center">
                                        <a href="<?= base_url('branches/edit/' . $branch->id) ?>" class="btn btn-sm btn-info"
                                            title="Editar"><i class="fa-solid fa-edit"></i></a>
                                        <button
                                            class="btn btn-danger btn-sm delete-btn"
                                            data-id="<?= $branch->id ?>"
                                            type="button">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
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

                        fetch("<?= base_url('branches/delete/') ?>" + id, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    [csrfHeader]: csrfToken // 👈 se envía el token CSRF
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
                                if (data.csrf) {
                                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrf.token);
                                    document.querySelector('meta[name="csrf-header"]').setAttribute('content', data.csrf.header);
                                }
                                if (data.status === 'success') {
                                    // Opcional: eliminar la fila visualmente sin recargar
                                    const row = button.closest('tr');
                                    if (row) row.remove();
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