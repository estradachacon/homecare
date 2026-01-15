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
                <!-- Modo movil -->
                <div class="d-block d-md-none">
                    <?php if (empty($cashiers)): ?>
                        <div class="alert alert-light text-center shadow-sm">
                            <i class="fa-solid fa-inbox mb-2 fa-2x text-muted"></i>
                            <p class="mb-0">No hay cajas registradas actualmente.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cashiers as $cashier): ?>
                            <?php
                            $status = esc($cashier->is_open);
                            $badgeClass = $status == '1' ? 'bg-info' : 'bg-warning'; // Colores más semánticos
                            ?>
                            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                                <div style="height: 4px;" class="<?= $badgeClass ?>"></div>

                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="fw-bold mb-0 text-primary">
                                                <i class="fa-solid fa-cash-register me-2"></i><?= esc($cashier->name) ?>
                                            </h5>
                                            <small class="text-muted">ID: #<?= esc($cashier->id) ?></small>
                                        </div>
                                        <span class="badge <?= $badgeClass ?> rounded-pill">
                                            <?= $status == 1 ? 'Abierta' : 'Cerrada' ?>
                                        </span>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="text-muted small d-block">Usuario</label>
                                            <span class="fw-medium"><i class="fa-solid fa-user-tie me-1 text-secondary"></i> <?= esc($cashier->user_name) ?></span>
                                        </div>
                                        <div class="col-6">
                                            <label class="text-muted small d-block">Sucursal</label>
                                            <span class="fw-medium"><i class="fa-solid fa-building me-1 text-secondary"></i> <?= esc($cashier->branch_name) ?></span>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="text-muted small d-block">Monto Inicial</label>
                                            <span class="text-dark">$<?= number_format($cashier->initial_balance, 2) ?></span>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="text-muted small d-block">Monto Actual</label>
                                            <span class="fw-bold text-success">$<?= number_format($cashier->current_balance, 2) ?></span>
                                        </div>
                                    </div>

                                    <hr class="my-3 opacity-25">

                                    <div class="d-flex justify-content-between gap-2">
                                        <?php if ($cashier->is_open == '1' && tienePermiso('hacer_corte')): ?>
                                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 py-2 btn-cerrar-caja" data-cashier-id="<?= esc($cashier->id) ?>">
                                                <i class="fa-solid fa-scissors me-1"></i> Cerrar Caja
                                            </button>
                                        <?php endif; ?>

                                        <div class="d-flex gap-2">
                                            <?php if (tienePermiso('editar_caja')): ?>
                                                <a href="<?= base_url('cashiers/edit/' . $cashier->id) ?>" class="btn btn-light btn-sm px-3 py-2 border">
                                                    <i class="fa-solid fa-edit text-info"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if (tienePermiso('eliminar_caja')): ?>
                                                <button class="btn btn-light btn-sm px-3 py-2 border delete-btn" data-id="<?= $cashier->id ?>">
                                                    <i class="fa-solid fa-trash text-danger"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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
                                            <?php if ($cashier->is_open == '1' && tienePermiso('hacer_corte')): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary btn-cerrar-caja"
                                                    data-cashier-id="<?= esc($cashier->id) ?>">
                                                    <i class="fa-solid fa-scissors"></i>
                                                </button>
                                            <?php endif; ?>


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
                <!-- 🔽 PAGER COMPARTIDO -->
                <div class="d-flex justify-content-center mt-3">
                    <?= $pager->links('default', 'bitacora_pagination') ?>
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
    <script>
        document.querySelectorAll('.btn-cerrar-caja').forEach(btn => {

            btn.addEventListener('click', async function() {

                const cashierId = this.dataset.cashierId;

                try {
                    const res = await fetch(`<?= base_url('cashiers/summary') ?>/${cashierId}`);
                    const data = await res.json();

                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    const html = `
                <p><b>Monto inicial:</b> $${data.initial_amount}</p>
                <p><b>Salidas:</b> $${data.total_out}</p>
                <hr>
                <h4>Total esperado: $${data.expected}</h4>
            `;

                    Swal.fire({
                        title: 'Cierre de caja',
                        html: html,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Cerrar caja',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#198754'
                    }).then(async (result) => {

                        if (result.isConfirmed) {

                            const closeRes = await fetch(`<?= base_url('cashiers/close') ?>`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `session_id=${data.session_id}`
                            });

                            const closeData = await closeRes.json();

                            if (closeData.success) {
                                Swal.fire('Cerrada', 'La caja fue cerrada correctamente', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', closeData.error, 'error');
                            }
                        }
                    });

                } catch (e) {
                    Swal.fire('Error', 'No se pudo obtener el resumen', 'error');
                }

            });

        });
    </script>
    <?= $this->endSection() ?>