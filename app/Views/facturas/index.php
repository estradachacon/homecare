<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de facturas</h4>
                <?php if (tienePermiso('cargar_facturas')): ?>
                    <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('facturas/carga') ?>"><i
                            class="fa-solid fa-plus"></i> Cargar..</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Correlativo</th>
                            <th class="col-2">Tipo DOC</th>
                            <th class="col-2">Cliente</th>
                            <th class="col-3">Fecha | Hora</th>
                            <th class="col-2">Plazo</th>
                            <th class="col-2">Total</th>
                            <th class="col-2">Saldo</th>
                            <th class="col-2">Estado</th>
                            <th class="col-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($settledPoints)): ?>
                            <?php foreach ($settledPoints as $settledPoint): ?>
                                <tr>
                                    <td class="text-center"><?= esc($settledPoint->id) ?></td>
                                    <td><?= esc($settledPoint->point_name) ?></td>
                                    <td class="text-center"><?= esc($settledPoint->route_name) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $dias = [
                                            'mon' => 'Lunes',
                                            'tus' => 'Martes',
                                            'wen' => 'Miércoles',
                                            'thu' => 'Jueves',
                                            'fri' => 'Viernes',
                                            'sat' => 'Sábado',
                                            'sun' => 'Domingo'
                                        ];

                                        $dias_visita = [];

                                        foreach ($dias as $col => $nombre) {
                                            if (!empty($settledPoint->$col) && $settledPoint->$col == 1) {
                                                $dias_visita[] = $nombre;
                                            }
                                        }

                                        echo !empty($dias_visita)
                                            ? implode(', ', $dias_visita)
                                            : '<span class="text-muted">Sin días asignados</span>';
                                        ?>
                                    </td>
                                    <td class="text-center"><?= esc($settledPoint->hora_inicio . ' - ' . $settledPoint->hora_fin) ?></td>
                                    <td>
                                        <?php if (tienePermiso('editar_puntofijo')): ?>
                                            <a href="<?= base_url('settledpoint/edit/' . $settledPoint->id) ?>"
                                                class="btn btn-sm btn-info"><i class="fa-solid fa-edit"></i></a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('eliminar_puntofijo')): ?>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $settledPoint->id ?>">
                                                <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No hay facturas registradas</td>
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
                        fetch("<?= base_url('settledpoint/delete') ?>", {
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