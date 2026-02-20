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
                            <th class="col-3">Cliente</th>
                            <th>Fecha/Hora</th>
                            <th class="col-1">Plazo</th>
                            <th class="col-1">Total</th>
                            <th class="col-1">Saldo</th>
                            <th class="col-1">Estado</th>
                            <th class="col-1">Menú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facturas)): ?>
                            <?php foreach ($facturas as $factura): ?>
                                <tr>
                                    <td class="text-center">
                                        <?= esc(substr($factura->numero_control, -6)) ?>
                                    </td>

                                    <td>
                                        <?php
                                        $siglas = dte_siglas();
                                        $descripciones = dte_descripciones();

                                        $codigo = $factura->tipo_dte;
                                        $sigla = $siglas[$codigo] ?? null;
                                        $descripcion = $sigla ? ($descripciones[$sigla] ?? null) : null;
                                        ?>

                                        <?php if ($sigla && $descripcion): ?>
                                            <span class="badge bg-info text-white">
                                                <?= esc($sigla) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?= esc($descripcion) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Desconocido</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <!-- Si luego haces join con cliente real puedes cambiar esto -->
                                        <?= esc($factura->cliente_nombre ?? 'Sin cliente') ?>
                                    </td>

                                    <td class="text-center">
                                        <?= esc($factura->fecha_emision) ?>
                                        <br>
                                        <small class="text-muted"><?= esc($factura->hora_emision) ?></small>
                                    </td>

                                    <td class="text-center">
                                        <?= esc($factura->condicion_operacion ?? 'Contado') ?>
                                    </td>

                                    <td class="text-end">
                                        $ <?= number_format($factura->total_pagar, 2) ?>
                                    </td>

                                    <td class="text-end">
                                        $ <?= number_format($factura->total_pagar, 2) ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-success">Activa</span>
                                    </td>

                                    <td class="text-center">
                                        <a href="<?= base_url('facturas/ver/' . $factura->id) ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    No hay facturas registradas
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>
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