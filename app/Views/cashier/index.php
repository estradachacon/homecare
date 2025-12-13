<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">Lista de cajas</h4>
                <?php if (
                    tienePermiso('crear_caja')
                ): ?>
                    <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('cashiers/new') ?>">
                        <i class="fa-solid fa-plus"></i> Crear caja
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">

                <!-- Modo móvil -->
                <div class="d-block d-md-none">
                    <?php if (empty($cashiers)): ?>
                        <div class="text-center">No hay cajas registradas.</div>
                    <?php else: ?>
                        <?php foreach ($cashiers as $cashier): ?>
                            <div class="card p-2">
                                <div class="card-body d-flex justify-content-between text-center align-items-center">
                                    <span><strong>Nombre / Usuario:</strong> <?= esc($cashier->name) ?> || <?= esc($cashier->user_name) ?></span>
                                </div>
                                <div>
                                    <span><strong>Monto inicial:</strong> <?= esc($cashier->initial_balance) ?></span>
                                </div>
                                <div>
                                    <p><strong>Sucursal:</strong> <?= esc($cashier->branch_name) ?></p>
                                </div>
                                <div class="text-right">
                                    <button class="btn btn-sm btn-primary toggle-details">Ver</button>
                                </div>
                                <div class="details mt-2 d-none">
                                    <p><strong>ID:</strong> <?= esc($cashier->id) ?></p>

                                    <p><strong>Monto actual:</strong> <?= esc($cashier->current_balance) ?></p>
                                    <p><strong>Estado:</strong>
                                        <?php if ($cashier->is_open): ?>Caja abierta<?php else: ?>Caja cerrada<?php endif; ?>
                                    </p>
                                    <div class="text-center mt-2">
                                        <a href="<?= base_url('cashiers/show/' . $cashier->id) ?>"
                                            class="btn btn-sm btn-primary"><i class="fa-solid fa-eye"></i></a>
                                            
                                        <?php if (tienePermiso('editar_caja')): ?>
                                            <a href="<?= base_url('cashiers/edit/' . $cashier->id) ?>"
                                                class="btn btn-sm btn-info"><i class="fa-solid fa-edit"></i></a>
                                        <?php endif; ?> 

                                        <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $cashier->id ?>"><i
                                                class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Modo escritorio -->
                <div class="d-none d-md-block">
                    <table class="table table-bordered" id="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de caja || Usuario Asignado</th>
                                <th>Sucursal</th>
                                <th>Monto inicial</th>
                                <th>Monto actual</th>
                                <th class="col-1">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cashiers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay cajas registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cashiers as $cashier): ?>
                                    <tr>
                                        <td><?= esc($cashier->id) ?></td>
                                        <td class="text-center"><?= esc($cashier->name) ?> || <?= esc($cashier->user_name) ?>
                                        </td>
                                        <td><?= esc($cashier->branch_name) ?></td>
                                        <td><?= esc($cashier->initial_balance) ?></td>
                                        <td><?= esc($cashier->current_balance) ?></td>
                                        <td class="text-center">
                                            <?php
                                            $status = esc($cashier->is_open);
                                            switch ($status) {
                                                case '1':
                                                    $style['class'] = 'bg-info text-white';
                                                    break;
                                                case '0':
                                                    $style['class'] = 'bg-secondary text-white';
                                                    break;
                                                default:
                                                    $style['class'] = 'bg-light text-danger border border-secondary';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $style['class'] ?> rounded-pill px-3 py-2">
                                                <?php if ($status == 1): ?>Caja abierta<?php else: ?>Caja cerrada<?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('cashiers/show/' . $cashier->id) ?>"
                                                class="btn btn-sm btn-primary"><i class="fa-solid fa-eye"></i></a>
                                            <?php if (tienePermiso('editar_caja')): ?>
                                                <a href="<?= base_url('cashiers/edit/' . $cashier->id) ?>"
                                                    class="btn btn-sm btn-info"><i class="fa-solid fa-edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (tienePermiso('eliminar_caja')): ?>
                                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $cashier->id ?>"><i
                                                        class="fa-solid fa-trash"></i></button>
                                            <?php endif; ?>
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
                            fetch("<?= base_url('cashiers/delete') ?>", {
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