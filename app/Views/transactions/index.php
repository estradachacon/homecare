<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4><i class="fa-solid fa-money-check-alt"></i> Movimientos de efectivo</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="transactionsTable">
                        <thead class="thead-primary bg-primary text-white">
                            <tr>
                                <th>ID</th>
                                <th>Cuenta</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Origen</th>
                                <th>Referencia</th>
                                <th>Tracking</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?= esc($t->id) ?></td>
                                    <td><?= esc($t->account_name) ?></td>

                                    <td>
                                        <?php if ($t->tipo == 'entrada'): ?>
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-arrow-down"></i> Entrada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fa-solid fa-arrow-up"></i> Salida
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td><strong>$<?= number_format($t->monto, 2) ?></strong></td>
                                    <td><?= esc($t->origen ?? '—') ?></td>
                                    <td><?= esc($t->referencia ?? '—') ?></td>
                                    <td><?= esc($t->tracking_id ?? '—') ?></td>

                                    <td><?= date('d/m/Y H:i', strtotime($t->created_at)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DATA TABLES SI DESEAS -->
<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            order: [
                [7, 'desc']
            ],
            pageLength: 25,
            
            // ⭐ TRADUCCIÓN AL ESPAÑOL ⭐
            language: {
                "sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    });
</script>

<?= $this->endSection() ?>