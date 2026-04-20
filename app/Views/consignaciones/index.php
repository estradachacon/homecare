<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">Notas de Envío</h4>
                <?php if (tienePermiso('crear_consignaciones')): ?>
                    <a href="<?= base_url('consignaciones/crear') ?>" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-plus"></i> Nueva Nota
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" action="<?= base_url('consignaciones') ?>" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select name="vendedor_id" class="form-control form-control-sm">
                                <option value="">Todos los vendedores</option>
                                <?php foreach ($vendedores as $v): ?>
                                    <option value="<?= $v->id ?>" <?= ($filtros['vendedor_id'] == $v->id) ? 'selected' : '' ?>>
                                        <?= esc($v->seller) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="abierta"  <?= ($filtros['estado'] === 'abierta')  ? 'selected' : '' ?>>Abierta</option>
                                <option value="cerrada"  <?= ($filtros['estado'] === 'cerrada')  ? 'selected' : '' ?>>Cerrada</option>
                                <option value="anulada"  <?= ($filtros['estado'] === 'anulada')  ? 'selected' : '' ?>>Anulada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="fecha_inicio" value="<?= esc($filtros['fecha_inicio'] ?? '') ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="fecha_fin" value="<?= esc($filtros['fecha_fin'] ?? '') ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm mr-2">
                                <i class="fa-solid fa-filter"></i> Filtrar
                            </button>
                            <a href="<?= base_url('consignaciones') ?>" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Vendedor</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($consignaciones)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hay notas de envío registradas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($consignaciones as $c): ?>
                                    <tr>
                                        <td><?= $c->id ?></td>
                                        <td>
                                            <a href="<?= base_url('consignaciones/' . $c->id) ?>">
                                                <strong><?= esc($c->numero) ?></strong>
                                            </a>
                                        </td>
                                        <td><?= esc($c->vendedor_nombre) ?></td>
                                        <td><?= esc($c->nombre) ?></td>
                                        <td><?= date('d/m/Y', strtotime($c->fecha)) ?></td>
                                        <td class="text-end">$<?= number_format($c->subtotal, 2) ?></td>
                                        <td class="text-center">
                                            <?php if ($c->estado === 'abierta'): ?>
                                                <span class="badge bg-success">Abierta</span>
                                            <?php elseif ($c->estado === 'cerrada'): ?>
                                                <span class="badge bg-secondary">Cerrada</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Anulada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('consignaciones/' . $c->id) ?>" class="btn btn-info btn-xs" title="Ver">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <?php if ($c->estado === 'abierta' && tienePermiso('cerrar_consignaciones')): ?>
                                                <a href="<?= base_url('consignaciones/' . $c->id . '/cerrar') ?>" class="btn btn-warning btn-xs" title="Cerrar">
                                                    <i class="fa-solid fa-lock"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($c->estado === 'abierta' && tienePermiso('anular_consignaciones')): ?>
                                                <button class="btn btn-danger btn-xs btn-anular" data-id="<?= $c->id ?>" data-numero="<?= esc($c->numero) ?>" title="Anular">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?= $pager->links('default', 'bootstrap_full') ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $(document).on('click', '.btn-anular', function () {
        const id     = $(this).data('id');
        const numero = $(this).data('numero');

        Swal.fire({
            title: '¿Anular nota?',
            html: `¿Está seguro de anular la nota <strong>${numero}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(`<?= base_url('consignaciones') ?>/${id}/anular`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Anulada', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
    });
});
</script>

<?= $this->endSection() ?>
