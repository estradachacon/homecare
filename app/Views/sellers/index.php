<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h4 class="mb-3">Mantenimiento de Vendedores</h4>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <a href="<?= base_url('sellers/new') ?>" class="btn btn-primary mb-3">➕ Nuevo Vendedor</a>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Vendedor</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sellers)): ?>
                <?php foreach ($sellers as $seller): ?>
                    <tr>
                        <td><?= esc($seller['id']) ?></td>
                        <td><?= esc($seller['seller']) ?></td>
                        <td><?= esc($seller['tel_seller']) ?></td>
                        <td>
                            <button class="btn btn-danger delete-btn" data-id="<?= $seller['id'] ?>">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No hay vendedores registrados</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
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

                        fetch(`<?= base_url('sellers') ?>/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                [csrfHeader]: csrfToken
                            }
                        })

                            .then(response => {
                                if (response.status === 303) {
                                    // Redirección normal → asumimos éxito
                                    return { status: 'success', message: 'Vendedor eliminado correctamente.' };
                                }
                                return response.json();
                            })
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