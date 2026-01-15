<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="header-title"><i class="fa-solid fa-money-check-alt"></i> Movimientos historicos de caja</h4>
            </div>
            <div class="card-body">
                <!-- MODO MÓVIL -->
                <div class="d-block d-md-none">

                    <?php if (empty($transactions)): ?>
                        <div class="alert alert-light text-center border">
                            <p class="mb-0 text-muted">No hay movimientos registrados</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush shadow-sm rounded">

                            <?php foreach ($transactions as $t): ?>
                                <?php
                                $isIn  = $t['type'] === 'in';
                                $icon  = $isIn ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                                $color = $isIn ? 'text-success' : 'text-danger';
                                ?>

                                <div class="list-group-item py-3 px-3 border-start border-4 <?= $isIn ? 'border-success' : 'border-danger' ?>">


                                    <!-- FILA PRINCIPAL -->
                                    <div class="d-flex align-items-start justify-content-between">

                                        <!-- IZQUIERDA -->
                                        <div class="d-flex gap-3 flex-grow-1">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                style="width: 42px; height: 42px;">
                                                <i class="fa-solid <?= $icon ?> <?= $color ?>"></i>
                                            </div>

                                            <div>
                                                <div class="fw-semibold small">
                                                    <?= esc($t['concept']) ?>
                                                </div>
                                                <span class="badge text-white <?= $isIn ? 'bg-success' : 'bg-danger' ?> rounded-pill small">
                                                    <?= $isIn ? 'INGRESO' : 'SALIDA' ?>
                                                </span>
                                                <div class="text-muted small mt-1">
                                                    <?= date('d/m/Y · H:i', strtotime($t['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- MONTO -->
                                        <div class="text-end ms-2">
                                            <div class="<?= $color ?> fw-bold">
                                                <?= $isIn ? '+' : '-' ?> $<?= number_format($t['amount'], 2) ?>
                                            </div>
                                            <div class="text-muted small">
                                                Saldo $<?= number_format($t['balance_after'], 2) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- INFO SECUNDARIA -->
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small text-muted">
                                        <span>
                                            <i class="fa-solid fa-user"></i>
                                            <?= explode(' ', esc($t['user_name']))[0] ?>
                                        </span>

                                        <span>
                                            Caja #<?= esc($t['cashier_id']) ?>
                                            · Sesión #<?= esc($t['cashier_session_id']) ?>
                                        </span>
                                    </div>

                                </div>
                            <?php endforeach; ?>

                        </div>
                    <?php endif; ?>

                    <!-- PAGINACIÓN -->
                    <div class="mt-3">
                        <?= $pager->links('default', 'bitacora_pagination') ?>
                    </div>
                </div>


                <!-- Modo de escritorio -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="transactionsTable">
                            <thead class="thead-primary bg-primary text-white">
                                <tr>
                                    <th>ID</th>
                                    <th>ID de Caja || Usuario</th>
                                    <th>Sesión</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Restante</th>
                                    <th>Concepto</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><?= esc($t['id']) ?></td>
                                        <td class="text-center"><?= esc($t['cashier_id']) ?> || <?= esc($t['user_name']) ?></td>
                                        <td class="text-center"><?= esc($t['cashier_session_id']) ?></td>
                                        <td>
                                            <?php if ($t['type'] === 'in'): ?>
                                                <span class="badge bg-success text-white">INGRESO</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger text-white">SALIDA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>$ <?= number_format(esc($t['amount']), 2) ?></td>
                                        <td>$ <?= number_format(esc($t['balance_after']), 2) ?></td>
                                        <td><?= esc($t['concept']) ?></td>
                                        <td><?= esc($t['reference_type']) ?></td>
                                        <td><?= esc($t['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            pageLength: 25,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [
                [0, "desc"]
            ],
            ordering: true,
            searching: true,
            paging: true,
            info: true,
            responsive: true,
            language: {
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros por página",
                info: "Mostrando _START_ a _END_ de _TOTAL_ movimientos",
                infoEmpty: "Mostrando 0 a 0 de 0 movimientos",
                infoFiltered: "(filtrado de _MAX_ movimientos en total)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron resultados",
                emptyTable: "No hay movimientos disponibles",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                },
                aria: {
                    sortAscending: ": activar para ordenar la columna de manera ascendente",
                    sortDescending: ": activar para ordenar la columna de manera descendente"
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>