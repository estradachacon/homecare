<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4><i class="fa-solid fa-money-check-alt"></i> Movimientos de efectivo</h4>
            </div>
            <div class="card-body">
                <div class="mb-3 d-flex justify-content-end">
                    <?php if (tienePermiso('registrar_gasto')): ?>
                        <button type="button" class="btn btn-success" id="btn-nuevo-gasto">
                            <i class="fa-solid fa-plus-circle"></i> Registrar Nuevo Gasto
                        </button>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="transactionsTable">
                        <thead class="thead-primary bg-primary text-white">
                            <tr>
                                <th>ID</th>
                                <th>Cuenta</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Descripción</th>
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

<div class="modal fade" id="modalRegistroGasto" tabindex="-1" aria-labelledby="modalRegistroGastoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRegistroGastoLabel"><i class="fa-solid fa-file-invoice-dollar"></i> Registrar Nuevo Gasto/Salida</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-registro-gasto">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="gastoFecha" class="form-label">Fecha del Movimiento</label>
                        <input type="date" class="form-control" id="gastoFecha" name="gastoFecha" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Buscador de cuenta -->
                    <div class="mt-3 divAccount" id="gastoCuenta<?= esc($q['id'] ?? '') ?>">
                        <label class="form-label">Cuenta</label>
                        <select name="account"
                            id="account"
                            class="form-control select2-account"
                            data-initial-id=""
                            data-initial-text="">
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="gastoMonto" class="form-label">Monto del Gasto ($)</label>
                        <input type="number" step="0.01" class="form-control" id="gastoMonto" name="gastoMonto" placeholder="Ej: 15.50" required>
                    </div>

                    <div class="mb-3">
                        <label for="gastoDescripcion" class="form-label">Concepto / Descripción</label>
                        <textarea class="form-control" id="gastoDescripcion" name="gastoDescripcion" rows="2" required></textarea>
                    </div>

                    <div class="alert alert-info mt-3" role="alert">
                        Al presionar "Guardar Gasto", se registrará una transacción de **SALIDA** de la cuenta seleccionada.
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btn-guardar-gasto">
                        <i class="fa-solid fa-save"></i> Guardar Gasto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    const accountSearchUrl = "<?= base_url('accounts-list') ?>";

    $(document).ready(function() {
        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
        // Interceptar SOLO los forms de agregar destino
        $('#account').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar cuenta...',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: accountSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.name
                        }))
                    };
                }
            }
        }).trigger('change'); // <-- Esta línea hace que Select2 lea el option inicial
    });
</script>
<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
            }
        });

        // 1. Lógica para Abrir el Modal
        $('#btn-nuevo-gasto').on('click', function() {
            // Asumiendo que estás usando Bootstrap 5, usa el método modal('show')
            var myModal = new bootstrap.Modal(document.getElementById('modalRegistroGasto'));
            myModal.show();
        });

        // 2. Lógica para Enviar el Formulario (AJAX/Fetch)
        $("#form-registro-gasto").on("submit", function(e) {
            e.preventDefault();

            let btn = $("#btn-guardar-gasto");
            btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

            $.ajax({
                url: "/transactions/addSalida",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",

                success: function(response) {
                    if (response.success) {

                        Swal.fire({
                            icon: "success",
                            title: "¡Guardado!",
                            text: "El gasto se registró correctamente.",
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 1600);

                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: response.message
                        });
                    }
                },

                error: function() {
                    Swal.fire({
                        icon: "error",
                        title: "Error del servidor",
                        text: "No se pudo registrar el gasto."
                    });
                },

                complete: function() {
                    btn.prop("disabled", false).html('<i class="fa-solid fa-save"></i> Guardar Gasto');
                }
            });
        });

    });
</script>

<?= $this->endSection() ?>