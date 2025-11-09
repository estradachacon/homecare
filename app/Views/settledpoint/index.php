<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Puntos fijos</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('settledpoint/new') ?>"><i
                        class="fa-solid fa-plus"></i> Nuevo</a>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="col-1">ID</th>
                            <th class="col-7">Puntos</th>
                            <th class="col-2">Ruta</th>
                            <th class="col-2">Acciones</th>
                            <th class="col-2">Configuración</th>
                            <th class="col-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sellers)): ?>
                            <?php foreach ($sellers as $seller): ?>
                                <tr>
                                    <td class="text-center"><?= esc($seller->id) ?></td>
                                    <td><?= esc($seller->seller) ?></td>
                                    <td class="text-center"><?= esc($seller->tel_seller) ?></td>
                                    <td class="text-center"><?= esc($seller->tel_seller) ?></td>
                                    <td>
                                        <a href="<?= base_url('settledpoint/edit/' . $seller->id) ?>"
                                            class="btn btn-sm btn-info"><i class="fa-solid fa-edit"></i></a>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $seller->id ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay puntos fijos registrados</td>
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
                        fetch("<?= base_url('sellers/delete') ?>", {
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