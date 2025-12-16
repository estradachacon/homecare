<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4><i class="fa-solid fa-money-check-alt"></i> Movimientos historicos de caja</h4>
            </div>
            <div class="card-body">
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
                                    <td><?= esc($t->id) ?></td>
                                    <td class="text-center"><?= esc($t->cashier_id) ?> || <?= esc($t->user_name) ?></td>
                                    <td class="text-center"><?= esc($t->cashier_session_id) ?></td>
                                    <td>
                                        <?php if ($t->type === 'in'): ?>
                                            <span class="badge bg-success text-white">INGRESO</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white">SALIDA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>$ <?= number_format(esc($t->amount), 2) ?></td>
                                    <td>$ <?= number_format(esc($t->balance_after), 2) ?></td>
                                    <td><?= esc($t->concept) ?></td>
                                    <td><?= esc($t->reference_type) ?></td>
                                    <td><?= esc($t->created_at) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    $('#transactionsTable').DataTable({
        pageLength: 25, // cantidad por defecto
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[0, "desc"]], // ordenar por fecha descendente
        ordering: true,
        searching: true,
        paging: true,
        info: true,
        responsive: true,
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros por página",
            info: "Mostrando _START_ a _END_ de _TOTAL_ movimientos",
            infoEmpty: "No hay movimientos disponibles",
            zeroRecords: "No se encontraron resultados",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });
});
</script>

<?= $this->endSection() ?>